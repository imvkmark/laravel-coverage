<?php

namespace Poppy\Core\Redis;

use Illuminate\Support\Str;
use Poppy\Core\Classes\PyCoreDef;
use Predis\Client;
use Throwable;

/**
 * field过期处理
 */
class RdsFieldExpired
{

    public const TYPE_HASH = 'hash';
    public const TYPE_SET  = 'set';
    public const TYPE_ZSET = 'zset';

    /**
     * @var RdsDb
     */
    private $cache;

    /**
     * @var RdsDb $expireHandler
     */
    private static $expireHandler;

    /**
     * 分隔符号
     * @var string $stripTag
     */
    private static $stripTag = '@@';

    /**
     * 清理过期的field
     * @return bool
     */
    public function clearExpiredField(): bool
    {
        self::initHandler();

        // 需要清理的field
        $fields = self::$expireHandler->zrangebyscore(PyCoreDef::ckRdsKeyFieldExpired(), 0, time());

        $this->convertClearFields($fields);
        if ($fields) {
            self::$expireHandler->zrem(PyCoreDef::ckRdsKeyFieldExpired(), $fields);
        }

        return true;
    }

    public function __destruct()
    {
        try {
            if ($this->cache) {
                $this->cache->disconnect();
                $this->cache = null;
            }
            self::$expireHandler->disconnect();
        } catch (Throwable $e) {
        }
    }

    /**
     * 设置过期时间
     * @param string       $database   数据库
     * @param string       $key        缓存key
     * @param mixed|string $field      field
     * @param string       $type       缓存类型
     * @param float|int    $expireTime 有效期
     * @return bool
     */
    public static function setFieldExpireTime(string $key, string $field, string $type, $database = 'default', $expireTime = 3600 * 24): bool
    {
        self::initHandler();

        // "{$database}@@{$cacheKey}@@{$field}@@{$type}"
        $index = implode(self::$stripTag, [$database, $key, $field, $type]);

        $expiredAt = time() + $expireTime;
        self::$expireHandler->zadd(PyCoreDef::ckRdsKeyFieldExpired(), [
            $index => $expiredAt,
        ]);

        self::$expireHandler->disconnect();

        return true;
    }

    /**
     * 清理hash
     * @param string $key    key
     * @param array  $fields 要清理的field
     * @return bool
     */
    protected function clearHash($key, $fields): bool
    {
        $this->cache->hdel($key, $fields);

        return true;
    }

    /**
     * 清理集合
     * @param string $key    key
     * @param array  $fields 要清理的field
     * @return bool
     */
    protected function clearSet($key, $fields): bool
    {
        $this->cache->srem($key, $fields);

        return true;
    }

    /**
     * 清理有序集合
     * @param string $key    key
     * @param array  $fields 要清理的field
     * @return bool
     */
    protected function clearZset($key, $fields): bool
    {
        $this->cache->zrem($key, $fields);

        return true;
    }

    /**
     * @param $fields
     * @return bool
     */
    private function convertClearFields($fields): bool
    {
        $clearFields = [];

        foreach ($fields as $field) {
            try {
                [$database, $key, $field, $type] = explode(self::$stripTag, $field);
            } catch (Throwable $e) {
                continue;
            }

            $clearFields[] = compact('database', 'key', 'field', 'type');
        }

        if (!$clearFields) {
            return true;
        }

        $this->groupClearFields($clearFields);

        return true;
    }

    /**
     * 清理过期字段
     * @param $clearFields
     * @return bool
     */
    private function groupClearFields($clearFields): bool
    {
        $this->cache = new RdsDb();

        collect($clearFields)->groupBy('database')
            ->map(function ($fields, $database) {
                $this->cache->select($database);

                $fields->groupBy('key')->each(function ($field, $key) {
                    $fieldIndex = $field->pluck('field')->toArray();

                    $item = $field->first();
                    $type = $item['type'] ?? '';
                    if (!$item || !$fieldIndex || !$type || !$key) {
                        return true;
                    }

                    $method = Str::camel('clear_' . $type);
                    if (method_exists($this, $method)) {
                        try {
                            $this->$method($key, $fieldIndex);
                        } catch (Throwable $e) {

                        }
                    }
                });
            });

        return true;
    }

    /**
     * @return bool
     */
    private static function initHandler(): bool
    {
        if (!self::$expireHandler instanceof Client) {
            self::$expireHandler = new RdsDb();
        }

        return true;
    }
}