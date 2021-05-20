<?php

namespace Poppy\Core\Redis;

use DB;
use Illuminate\Support\Str;
use Poppy\Framework\Classes\Number;
use Poppy\Framework\Classes\Traits\AppTrait;
use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\Framework\Exceptions\TransactionException;
use Poppy\Framework\Helper\ArrayHelper;
use Throwable;

/**
 * Redis 持久化数据
 */
class RdsPersist
{

    use AppTrait;

    /**
     * @var string 新增
     */
    public const TYPE_INSERT = 'insert';

    /**
     * @var string 修改
     */
    public const TYPE_UPDATE = 'update';

    /**
     * Redis Key
     * @var string
     */
    static $key = 'system:persist';

    /**
     * @var RdsDb $redis
     */
    private $redis;

    /**
     * 初始化redis连接
     */
    public function __construct()
    {
        $this->redis = new RdsDb();
    }

    /**
     * 往队列中插入一条数据
     * @param string $table  数据表名称
     * @param array  $values 需要插入的数据
     * @return bool
     */
    public function insert($table = '', $values = []): bool
    {

        if (!is_array(reset($values))) {
            $values = [$values];
        }

        // Here, we will sort the insert keys for every record so that each insert is
        // in the same order for the record. We need to make sure this is the case
        // so there are not any errors or problems when inserting these records.
        else {
            foreach ($values as $key => $value) {
                ksort($value);

                $values[$key] = $value;
            }
        }

        $rdsKey    = $this->insertKey($table);
        $arrValues = [];
        foreach ($values as $value) {
            $arrValues[] = json_encode($value);
        }
        $this->redis->rpush($rdsKey, $arrValues);
        return true;
    }

    /**
     * 修改队列中的数据，根据条件没有找到的话就创建一条
     * @param string $table   数据表名称
     * @param array  $where   查询条件(一维数组)
     * @param array  $update  修改条件(一维数组) <br>
     *                        此 update 条件支持 [+] 数据 + , [.] 数据组合, [>] 数据保留之前, [<] 将之前的数据覆盖
     * @return bool
     * @throws ApplicationException
     */
    public function update($table = '', $where = [], $update = []): bool
    {
        $rdsKey = $this->updateKey($table);

        if (empty($where)) {
            return false;
        }

        ksort($where);
        array_walk($where, function (&$value) {
            $value = (string) $value;
        });

        $whereJson = json_encode($where, JSON_UNESCAPED_UNICODE);

        // 当前key的所有list数据
        $exists = $this->redis->hexists($rdsKey, $whereJson);

        $DB = DB::table($table)->where($where);
        if ($exists) {
            // 对之前的数据进行计算
            $former = $this->redis->hget($rdsKey, $whereJson);
            $former = json_decode($former, true);
            // diff fields
            $formerKey = $this->pureKeys(array_keys($former));
            $updateKey = $this->pureKeys(array_keys($update));
            $diffKeys  = array_diff($updateKey, $formerKey);
            if (count($diffKeys)) {
                $formerDiff = (array) (clone $DB)->select($diffKeys)->first();
                if (!$formerDiff) {
                    throw new ApplicationException('数据持久化失败, 数据库中不存在相应数据');
                }
                $former = array_merge($former, (array) $formerDiff);
            }
            $values = $this->calcUpdate($former, $update);
            $this->redis->hset($rdsKey, $whereJson, json_encode($values));
        }
        else {
            $updateKeys = array_keys($update);
            $exists     = (clone $DB)->exists();
            if (!$exists) {
                DB::table($table)->insert($where);
            }
            $former = (clone $DB)->select($this->pureKeys($updateKeys))->first();
            $values = $this->calcUpdate((array) $former, $update);
            $this->redis->hset($rdsKey, $whereJson, json_encode($values));
        }
        return true;
    }

    /**
     * 获取当前缓存的 where 条件的数据
     * @param       $table
     * @param array $where
     * @return array
     */
    public function where($table, $where = [])
    {
        $rdsKey    = $this->updateKey($table);
        $whereJson = $this->whereCondition($where);
        // 当前key的所有list数据
        $exists = $this->redis->hexists($rdsKey, $whereJson);

        if ($exists) {
            // 获取存储的数据
            $former = $this->redis->hget($rdsKey, $whereJson);
            return json_decode($former, true);
        }
        else {
            return [];
        }
    }

    /**
     * 将redis中的所有数据持久化到数据库
     * 执行将所有表的数据都写入数据库中可使用该方法
     * @return bool
     */
    public function exec(): bool
    {
        // 所有新增数据的key
        $insertKeys = [];
        // 所有修改数据的key
        $updateKeys = [];
        $keys       = $this->redis->keys($this->key('*'));

        foreach ($keys as $_key) {
            $keyName = substr($_key, strrpos($_key, ':') + 1);
            if (Str::endsWith($keyName, '_' . self::TYPE_INSERT)) {
                $insertKeys[] = $keyName;
            }
            if (Str::endsWith($keyName, '_' . self::TYPE_UPDATE)) {
                $updateKeys[] = $keyName;
            }
        }

        // 将类型为新增的数据持久化数据库
        $this->execInsert($insertKeys);
        // 将类型为修改的数据持久化数据库
        $this->execUpdate($updateKeys);

        return true;
    }

    /**
     * 将redis中的指定表的数据持久化到数据库
     * 单独持久化某个表的时候可以使用该方法
     * @param string $table
     * @return bool
     */
    public function execTable($table = ''): bool
    {
        try {
            // 当前数据库的写入key
            $insert_key = $table . '_' . self::TYPE_INSERT;
            // 当前数据库的更新key
            $update_key = $table . '_' . self::TYPE_UPDATE;

            // 将类型为新增的数据持久化数据库
            $this->execInsert([$insert_key]);
            // 将类型为修改的数据持久化数据库
            $this->execUpdate([$update_key]);

            return true;
        } catch (Throwable $e) {
            return $this->setError($e);
        }
    }

    /**
     * 进行库的更新计算
     * @param array $former
     * @param array $update
     * @return array
     */
    public function calcUpdate($former = [], $update = [])
    {
        if (empty($update)) {
            return $former;
        }
        foreach ($update as $k => $v) {
            preg_match('/(?<column>[a-zA-Z0-9_]+)(\[(?<operator>\+|-|>|<|\.)])?/i', $k, $match);
            $column   = $match['column'];
            $operator = $match['operator'] ?? '';
            if (isset($former[$column])) {
                $ori = $former[$column];
                switch ($operator) {
                    case '+':
                        $value = (new Number($ori, 2))->add($v)->getValue();
                        break;
                    case '.':
                        $value = $ori . $v;
                        break;
                    case '-':
                        $value = (new Number($ori, 2))->subtract($v)->getValue();
                        break;
                    // preserve former
                    case '>':
                        $value = $ori;
                        break;
                    // preserve current
                    case '<':
                    default:
                        $value = $v;
                        break;
                }
                $former[$column] = $value;
            }
            else {
                $former[$column] = $v;
            }
        }
        return $former;
    }

    /**
     * 返回Column
     * @param $keys
     * @return array
     */
    private function pureKeys($keys)
    {
        $columns = [];
        foreach ($keys as $key) {
            preg_match('/(?<column>[a-zA-Z0-9_]+)(\[(?<operator>\+|-|>|<|\.)])?/i', $key, $match);
            $columns[] = $match['column'];
        }
        return $columns;
    }

    /**
     * 将类型为新增的数据持久化到数据库
     * @param array $insert_keys 类型为新增的数据的keys,二维数组
     * @return bool
     */
    private function execInsert($insert_keys = []): bool
    {
        try {
            foreach ($insert_keys as $_key) {
                $rdsKey = $this->key($_key);
                // 当前key的所有list数据
                $_keyData = $this->redis->lrange($rdsKey, 0, -1);
                $_arrData = [];

                foreach ($_keyData as $_item) {
                    $_arrData[] = json_decode($_item, true);
                }

                // 从key中去取出表名
                $_tableName = Str::before($_key, '_' . self::TYPE_INSERT);

                // 插入成功
                if (!DB::table($_tableName)->insert($_arrData)) {
                    throw new TransactionException('Insert 数据持久化失败, ' . $_tableName . '' . ArrayHelper::toKvStr($_arrData));
                }

                // 从缓冲中删除key
                $this->redis->del([$rdsKey]);

                return true;

            }
        } catch (Throwable $e) {
            return $this->setError($e);
        }

        return true;
    }

    /**
     * 将类型为修改的数据持久化到数据库
     * @param array $update_keys 类型为修改的数据的keys,二维数组
     * @return bool
     */
    private function execUpdate($update_keys = []): bool
    {
        try {
            foreach ($update_keys as $_key) {
                $rdsKey = $this->key($_key);
                // 当前key的所有list数据
                $keys = $this->redis->hkeys($rdsKey);

                // 从key中去取出表名
                $tableName = Str::before($_key, '_' . self::TYPE_UPDATE);

                foreach ($keys as $where) {
                    $arrWhere = json_decode($where, true);
                    $arrValue = json_decode($this->redis->hget($rdsKey, $where), true);

                    // 修改成功
                    if (!DB::table($tableName)->where($arrWhere)->update($arrValue)) {
                        throw new TransactionException('Update 数据持久化失败, ' . $tableName . '' . ArrayHelper::toKvStr($arrWhere));
                    }

                    // 从缓冲中删除key
                    $this->redis->hdel($rdsKey, [$where]);
                }

            }
            return true;
        } catch (Throwable $e) {
            return $this->setError($e);
        }
    }

    /**
     * 返回 Where 条件
     * @param $where
     * @return false|string|null
     */
    private function whereCondition($where)
    {

        if (empty($where)) {
            return null;
        }

        ksort($where);
        array_walk($where, function (&$value) {
            $value = (string) $value;
        });

        return json_encode($where, JSON_UNESCAPED_UNICODE);
    }

    private function updateKey($table)
    {
        return $this->key($table . '_' . self::TYPE_UPDATE);
    }

    private function insertKey($table)
    {
        return $this->key($table . '_' . self::TYPE_INSERT);
    }

    /**
     * 获取 KEY
     * @param $key
     * @return string
     */
    private function key($key)
    {
        return self::$key . ':' . $key;
    }
}