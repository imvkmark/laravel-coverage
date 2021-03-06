<?php

namespace Poppy\Core\Redis;

use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Poppy\Core\Exceptions\RedisException;
use Poppy\Framework\Helper\UtilHelper;
use Predis\Client;
use Predis\Pipeline\Pipeline;
use Predis\Response\Status;
use stdClass;
use Throwable;

/**
 * 缓存处理
 */
class RdsNative
{
    /**
     * @var Client $redis
     */
    private $redis;

    /**
     * 缓存标签
     * @var string $cacheTag
     */
    private $cacheTag;

    /**
     * CacheHandler constructor.
     * @param array  $config   配置
     * @param string $cacheTag 缓存标签
     */
    public function __construct(array $config, $cacheTag = '')
    {
        $this->redis = new Client($config);

        $this->cacheTag = $cacheTag;
    }

    /**
     * 将字符串值 value 关联到 key, 如果 key 已经持有其他值， SET 就覆写旧值， 无视类型
     * 当 SET 命令对一个带有生存时间（TTL）的键进行设置之后， 该键原有的 TTL 将被清除
     * @param string       $key
     * @param string|array $value
     * @param null|string  $expireResolution 过期策略 EX : 过期时间(秒), PX : 过期时间(毫秒) <br>
     *                                       EX seconds : 秒 <br>
     *                                       PX milliseconds  毫秒 <br>
     *                                       NX -- 不存在则设置 <br>
     *                                       XX -- 存在则设置
     * @param null|int     $expireTTL        过期时间
     * @param null         $flag             设置类型 NX : 不存在则设置, XX : 存在则设置, 这个参数在没有时间设定情况下可以进行前置
     * @return bool
     */
    public function set(string $key, $value, $expireResolution = null, $expireTTL = null, $flag = null): bool
    {
        $arguments    = array_values(func_get_args());
        $arguments[0] = $this->tagged($key);
        $arguments[1] = $this->toString($value);

        $result = $this->redis->set(...$arguments);
        if ($result instanceof Status) {
            return true;
        }
        return false;
    }

    /**
     *
     * @param string $key    默认的KEY
     * @param bool   $decode 解码, 因为解码存在性能问题, 建议字符串/Json 不进行解码, 直接拿到外边去进行解码处理
     * @return mixed
     */
    public function get(string $key, bool $decode = true)
    {
        $result = $this->redis->get($this->tagged($key));
        return $decode ? $this->decode($result) : $result;
    }

    /**
     * 将键 key 的值设为 value ， 并返回键 key 在被设置之前的旧值。
     * @param string $key
     * @param mixed  $value
     * @param bool   $decode 是否进行解码
     * @return mixed|string|null
     */
    public function getSet(string $key, $value, bool $decode = true)
    {
        $result = $this->redis->getset($this->tagged($key), $this->toString($value));
        return $decode ? $this->decode($result) : $result;
    }


    /**
     * 返回键 key 储存的字符串值的长度, 如果key 不存在则返回 0
     * 这里返回的字符串长度是字符数, 如果一个汉字, 这里返回 3
     * @param string $key
     * @return int
     */
    public function strLen(string $key): int
    {
        return $this->redis->strlen($this->tagged($key));
    }


    /**
     * 如果键 key 已经存在并且它的值是一个字符串， APPEND 命令将把 value 追加到键 key 现有值的末尾。
     * 如果 key 不存在， APPEND 就简单地将键 key 的值设为 value ， 就像执行 SET key value 一样。
     * 追加 value 之后， 键 key 的值的长度
     * @param string $key
     * @param string $value
     * @return int
     */
    public function append(string $key, string $value): int
    {
        return $this->redis->append($this->tagged($key), $value);
    }


    /**
     * 从偏移量 offset 开始， 用 value 参数覆写(overwrite)键 key 储存的字符串值。
     * 不存在的键 key 当作空白字符串处理。
     * SETRANGE 命令会确保字符串足够长以便将 value 设置到指定的偏移量上， 如果键 key 原来储存的字符串长度比偏移量小(比如字符串只有 5 个字符长，但你设置的 offset 是 10 )， 那么原字符和偏移量之间的空白将用零字节(zerobytes, "\x00" )进行填充。
     * 设置空的数据返回的内容是       , 不能正确显示, 当然也不是空格,可以当做无意义的数据进行截取处理, 尽量避免使用这个字串
     * @param string $key 设置的键值
     * @param int    $offset
     * @param string $value
     * @return int
     */
    public function setRange(string $key, int $offset, string $value): int
    {
        return $this->redis->setrange($this->tagged($key), $offset, $value);
    }

    /**
     * 为键 key 储存的数字值加上一。
     *
     * 如果键 key 不存在， 那么它的值会先被初始化为 0 ， 然后再执行 INCR 命令。
     *
     * 如果键 key 储存的值不能被解释为数字， 那么 INCR 命令将返回一个错误。
     * @param string $key
     * @param int    $value
     * @return int
     */
    public function incr(string $key, $value = 1): int
    {
        if ($value === 1) {
            return $this->redis->incr($this->tagged($key));
        }
        else {
            return $this->redis->incrby($this->tagged($key), $value);
        }
    }


    /**
     * 因为浮点数计算可能会产生问题, 这里强制返回 String
     * @param string       $key
     * @param float|string $value
     * @return string
     */
    public function incrByFloat(string $key, float $value): string
    {
        return $this->redis->incrbyfloat($this->tagged($key), $value);
    }


    /**
     * 为键 key 储存的数字值减去一。
     * 如果键 key 不存在， 那么键 key 的值会先被初始化为 0 ， 然后再执行 DECR 操作。
     * 如果键 key 储存的值不能被解释为数字， 那么 DECR 命令将返回一个错误。
     * @param string $key
     * @param int    $value
     * @return int
     */
    public function decr(string $key, $value = 1): int
    {
        if ($value === 1) {
            return $this->redis->decr($this->tagged($key));
        }
        else {
            return $this->redis->decrby($this->tagged($key), $value);
        }
    }

    /**
     * 返回键 key 储存的字符串值的指定部分， 字符串的截取范围由 start 和 end 两个偏移量决定 (包括 start 和 end 在内)。
     * 负数偏移量表示从字符串的末尾开始计数， -1 表示最后一个字符， -2 表示倒数第二个字符， 以此类推。
     * @param string $key
     * @param int    $start
     * @param int    $end
     * @return string
     */
    public function getRange(string $key, int $start, int $end = -1): string
    {
        return $this->redis->getrange($key, $start, $end);
    }

    /**
     * 是否进行默认解码
     * @param      $keys
     * @param bool $decode
     * @return array
     */
    public function mGet($keys, bool $decode = true): array
    {
        $keys   = $this->tagged($keys);
        $result = $this->redis->mget($keys);
        return $decode ? $this->decode($result) : $result;
    }

    /**
     * 设置值并设置过期时间
     * @param string       $key
     * @param int          $seconds
     * @param array|string $value
     * @return bool
     */
    public function setEx(string $key, int $seconds, $value): bool
    {
        return (bool) $this->redis->setex($this->tagged($key), $seconds, $this->toString($value));
    }


    /**
     * 设置值并设置过期时间(毫秒)
     * @param string       $key
     * @param int          $milliseconds
     * @param array|string $value
     * @return bool
     */
    public function pSetEx(string $key, int $milliseconds, $value): bool
    {
        $res = $this->redis->psetex($this->tagged($key), $milliseconds, $this->toString($value));
        if ($res instanceof Status) {
            return true;
        }
        return false;
    }

    /**
     * @param string       $key
     * @param array|string $value
     * @return bool
     */
    public function setNx(string $key, $value): bool
    {
        $res = $this->redis->setnx($this->tagged($key), $this->toString($value));
        return (bool) $res;
    }


    /**
     * 同时为多个键设置值(原子性操作)
     * @param array $array 需要设置的 key/value
     * @return bool
     */
    public function mSet(array $array): bool
    {
        $new = [];
        foreach ($array as $key => $arr) {
            $new[$this->tagged($key)] = $this->toString($arr);
        }
        return (bool) $this->redis->mset($new);
    }

    /**
     * 当且仅当所有给定键都不存在时， 为所有给定键设置值。
     * 即使只有一个给定键已经存在， MSETNX 命令也会拒绝执行对所有键的设置操作。
     * MSETNX 是一个原子性(atomic)操作， 所有给定键要么就全部都被设置， 要么就全部都不设置， 不可能出现第三种状态。
     * @param array $array
     * @return bool
     */
    public function mSetNx(array $array): bool
    {
        $new = [];
        foreach ($array as $key => $arr) {
            $new[$this->tagged($key)] = $this->toString($arr);
        }
        return (bool) $this->redis->msetnx($new);
    }

    /**
     * 删除哈希表 key 中的一个或多个指定域，不存在的域将被忽略。
     * @param string       $key
     * @param string|array $fields
     * @return int 被成功移除的域的数量，不包括被忽略的域
     */
    public function hDel(string $key, $fields): int
    {
        return $this->redis->hdel($this->tagged($key), (array) $fields);
    }

    /**
     * @param $key
     * @param $field
     * @return bool
     */
    public function hExists($key, $field): bool
    {
        return (bool) $this->redis->hexists($this->tagged($key), $field);
    }

    /**
     * 返回哈希表中给定域的值, 支持数组的返回
     * @param string|int $key
     * @param string|int $field
     * @param bool       $decode
     * @return mixed
     */
    public function hGet($key, $field, $decode = true)
    {
        $value = $this->redis->hget($this->tagged($key), $field);
        return $decode ? $this->decode($value) : $value;
    }

    /**
     * @param      $key
     * @param bool $decode
     * @return array
     */
    public function hGetAll($key, $decode = true): array
    {
        $infos = $this->redis->hgetall($this->tagged($key));
        return $decode ? $this->decode($infos) : $infos;
    }

    /**
     * 设置哈希表
     * @param string $key
     * @param string $field
     * @param mixed  $value
     * @return int 当 HSET 命令在哈希表中新创建 field 域并成功为它设置值时， 命令返回 1 ； 如果域 field 已经存在于哈希表， 并且 HSET 命令成功使用新值覆盖了它的旧值， 那么命令返回 0
     */
    public function hSet(string $key, string $field, $value): int
    {
        return $this->redis->hset($this->tagged($key), $field, $this->toString($value));
    }

    /**
     * 当且仅当域 field 尚未存在于哈希表的情况下， 将它的值设置为 value 。
     * 如果给定域已经存在于哈希表当中， 那么命令将放弃执行设置操作。
     * 如果哈希表 hash 不存在， 那么一个新的哈希表将被创建并执行 HSETNX 命令。
     * @param string $key
     * @param string $field
     * @param mixed  $value
     * @return int HSETNX 命令在设置成功时返回 1 ， 在给定域已经存在而放弃执行设置操作时返回 0
     */
    public function hSetNx(string $key, string $field, $value): int
    {
        return $this->redis->hsetnx($this->tagged($key), $field, $this->toString($value));
    }

    /**
     * 返回哈希表 key 中域的数量
     * @param string $key
     * @return int
     */
    public function hLen(string $key): int
    {
        return $this->redis->hlen($this->tagged($key));
    }

    /**
     * 返回哈希表 key 中， 与给定域 field 相关联的值的字符串长度（string length）。
     * 如果给定的键或者域不存在， 那么命令返回 0
     * @param string $key
     * @param string $field
     * @return int
     */
    public function hStrLen(string $key, string $field): int
    {
        return $this->redis->hstrlen($this->tagged($key), $field);
    }

    /**
     * 为哈希表 key 中的域 field 的值加上增量 increment 。
     * 增量也可以为负数，相当于对给定域进行减法操作。
     * 如果 key 不存在，一个新的哈希表被创建并执行 HINCRBY 命令。
     * 如果域 field 不存在，那么在执行命令前，域的值被初始化为 0 。
     * 对一个储存字符串值的域 field 执行 HINCRBY 命令将造成一个错误。
     * @param string $key
     * @param string $field
     * @param int    $value 支持科学计数法
     * @return int
     */
    public function hIncrBy(string $key, string $field, int $value = 1): int
    {
        $val = (string) $this->redis->hincrby($this->tagged($key), $field, $value);
        return (int) $val;
    }


    /**
     * 浮点数增加
     * @param string       $key
     * @param string       $field
     * @param float|string $value 需要增加的数据, 支持科学计数法
     * @return string 因为这个地方计算的时候会出现 0.01 + 0.01 = 0.1999999999 情况出现, 所有不建议使用这个方法
     */
    public function hIncrByFloat(string $key, string $field, $value): string
    {
        return $this->redis->hincrbyfloat($this->tagged($key), $field, $value);
    }

    /**
     * 批量获取数据
     * @param string       $key
     * @param string|array $fields
     * @param bool         $decode 是否进行解码
     * @return array
     */
    public function hMGet(string $key, $fields, bool $decode = true): array
    {
        $result = $this->redis->hmget($this->tagged($key), (array) $fields);
        return array_combine($fields, $decode ? $this->decode($result) : $result);
    }

    /**
     * @param                 $key
     * @param array|Arrayable $dictionary
     * @return mixed
     */
    public function hMSet($key, $dictionary)
    {
        if ($dictionary instanceof Arrayable) {
            $dictionary = $dictionary->toArray();
        }

        if (!$dictionary) {
            return true;
        }

        $new = [];
        foreach ($dictionary as $k => $value) {
            $new[$k] = $this->toString($value);
        }

        return $this->redis->hmset($this->tagged($key), $new);
    }

    /**
     * 对于 Hscan 存在的 count 失效的问题可以查看
     * @param string $key
     * @param int    $cursor
     * @param array  $options
     * @return array [cursor, list]
     * @url https://my.oschina.net/throwable/blog/4518025
     */
    public function hScan(string $key, $cursor = 0, $options = []): array
    {
        return $this->redis->hscan($this->tagged($key), $cursor, $options);
    }

    /**
     * 返回哈希表 key 中的所有域
     * @param string $key
     * @return array
     */
    public function hKeys(string $key): array
    {
        return $this->redis->hkeys($this->tagged($key));
    }


    /**
     * 获取所有值, 支持数组的传入和获取
     * @param string $key
     * @return array
     */
    public function hVals(string $key): array
    {
        $result = $this->redis->hvals($this->tagged($key));
        return $this->decode($result);
    }

    /**
     * 列表的长度
     * @param $key
     * @return int
     */
    public function lLen($key): int
    {
        return $this->redis->llen($this->tagged($key));
    }


    /**
     * 返回列表 key 中指定区间内的元素，区间以偏移量 start 和 stop 指定
     * @param string $key
     * @param int    $start
     * @param int    $stop
     * @return array
     */
    public function lRange(string $key, int $start = 0, $stop = -1): array
    {
        return $this->redis->lrange($this->tagged($key), $start, $stop);
    }


    /**
     * 保留指定区间内的数据
     * @param string $key
     * @param int    $start
     * @param int    $stop
     * @return bool
     */
    public function lTrim(string $key, int $start, int $stop): bool
    {
        return (bool) $this->redis->ltrim($this->tagged($key), $start, $stop);
    }

    /**
     * @param string       $key
     * @param int          $count
     * @param array|string $value
     * @return int
     */
    public function lRem(string $key, int $count, $value): int
    {
        return $this->redis->lrem($this->tagged($key), $count, $this->toString($value));
    }

    /**
     * 左侧移除
     * @param $key
     * @return mixed
     */
    public function lPop($key)
    {
        $value = $this->redis->lpop($this->tagged($key));
        return $this->decode($value);
    }


    /**
     * 返回列表 key 中，下标为 index 的元素
     * @param string $key
     * @param int    $index
     * @return mixed
     */
    public function lIndex(string $key, int $index)
    {
        $value = $this->redis->lindex($this->tagged($key), $index);
        return $this->decode($value);
    }

    /**
     * 将值 value 插入到列表 key 当中，位于值 pivot 之前或之后。
     * 当 pivot 不存在于列表 key 时，不执行任何操作。
     * 当 key 不存在时， key 被视为空列表，不执行任何操作。
     * @param string $key
     * @param string $whence
     * @param mixed  $pivot
     * @param mixed  $value
     * @return bool
     */
    public function lInsert(string $key, string $whence, $pivot, $value): bool
    {
        $pivot = $this->toString($pivot);
        $value = $this->toString($value);
        $num   = $this->redis->linsert($this->tagged($key), $whence, $pivot, $value);
        if ($num <= 0) {
            return false;
        }
        return true;
    }


    /**
     * 设置值
     * @param string $key
     * @param int    $index
     * @param mixed  $value
     * @return bool
     */
    public function lSet(string $key, int $index, $value): bool
    {
        try {
            $this->redis->lset($this->tagged($key), $index, $this->toString($value));
            return true;
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * @param string       $key
     * @param string|array $elements
     * @return int
     */
    public function pfAdd(string $key, $elements): int
    {
        $elements = (array) $elements;
        return $this->redis->pfadd($this->tagged($key), $elements);
    }

    /**
     * HyperLogLog 数据统计
     * 命令返回的可见集合（observed set）基数并不是精确值， 而是一个带有 0.81% 标准错误（standard error）的近似值
     * @param string|array $key
     * @return int
     */
    public function pfCount($key): int
    {
        $keys = (array) $key;
        return $this->redis->pfcount(array_map(function ($key) {
            return $this->tagged($key);
        }, $keys));
    }

    /**
     * HyperLogLog 数据合并
     * @param string|array $key
     * @return bool
     */
    public function pfMerge($key): bool
    {
        return $this->redis->pfcount(array_map(function ($key) {
            return $this->tagged($key);
        }, (array) $key));
    }

    /**
     * 阻塞式获取
     * @param string $key
     * @param int    $timeout
     * @return array|null
     */
    public function bLPop(string $key, $timeout = 2): ?array
    {
        return $this->redis->blpop($this->tagged($key), $timeout);
    }


    /**
     * 阻塞式获取
     * @param string $key
     * @param int    $timeout
     * @return array|null
     */
    public function bRPop(string $key, $timeout = 2): ?array
    {
        return $this->redis->brpop($this->tagged($key), $timeout);
    }

    /**
     * 阻塞式获取
     * @param string $source
     * @param string $dist
     * @param int    $timeout
     * @return string|null
     */
    public function bRPopLPush(string $source, string $dist, $timeout = 2): ?string
    {
        return $this->redis->brpoplpush($this->tagged($source), $this->tagged($dist), $timeout);
    }


    /**
     * 将一个或多个值 value 插入到列表 key 的表头
     * 如果有多个 value 值，那么各个 value 值按从左到右的顺序依次插入到表头：
     * 比如说，对空列表 mylist 执行命令 LPUSH mylist a b c ，列表的值将是 c b a
     * 这等同于原子性地执行 LPUSH mylist a 、 LPUSH mylist b 和 LPUSH mylist c 三个命令。
     * @param string       $key
     * @param string|array $values
     * @return int
     */
    public function lPush(string $key, $values): int
    {
        $values = array_map([$this, 'toString'], (array) $values);
        return $this->redis->lpush($this->tagged($key), $values);
    }


    /**
     * 将值 value 插入到列表 key 的表头，当且仅当 key 存在并且是一个列表
     * 测试发现参数仅仅支持一个, 因为仅仅支持一个, 如果为数组, 则进行序列化
     * @param string       $key
     * @param array|string $values
     * @return int
     */
    public function lPushX(string $key, $values): int
    {
        return $this->redis->lpushx($this->tagged($key), $this->toString($values));
    }

    /**
     * 将一个或多个值 value 插入到列表 key 的表尾(最右边)。
     * 如果有多个 value 值，那么各个 value 值按从左到右的顺序依次插入到表尾
     * 比如对一个空列表 mylist 执行 RPUSH mylist a b c ，得出的结果列表为 a b c
     * 等同于执行命令 RPUSH mylist a 、 RPUSH mylist b 、 RPUSH mylist c
     * @param string $key
     * @param        $values
     * @return int
     */
    public function rPush(string $key, $values): int
    {
        $values = array_map([$this, 'toString'], (array) $values);
        return $this->redis->rpush($this->tagged($key), $values);
    }

    /**
     * 右侧插入, 如果值存在, 则进行赋值, 否则不进行操作
     * 经测试, 这里仅仅支持单值, 不支持数组
     * @param string       $key
     * @param string|array $values
     * @return int
     */
    public function rPushX(string $key, $values): int
    {
        return $this->redis->rpushx($this->tagged($key), $this->toString($values));
    }

    /**
     * 右侧移除元素
     * @param string $key
     * @return mixed
     */
    public function rPop(string $key)
    {
        $value = $this->redis->rpop($this->tagged($key));
        return $this->decode($value);
    }


    /**
     * 右侧移除元素
     * @return mixed
     */
    public function rPopLPush(string $source, string $dist)
    {
        $value = $this->redis->rpoplpush($this->tagged($source), $this->tagged($dist));
        return $this->decode($value);
    }

    /**
     * 新增集合元素, 先转为数组, 如果数组内有可序列化元素再进行序列化
     * @param string       $key     key
     * @param string|array $members 成员
     * @return int
     */
    public function sAdd(string $key, $members): int
    {
        $values = array_map([$this, 'toString'], (array) $members);
        return $this->redis->sadd($this->tagged($key), $values);
    }


    /**
     * 对比多个 key
     * @params array $keys
     * @return array
     */
    public function sDiff($keys)
    {
        $values = $this->redis->sdiff($this->tagged($keys));
        return $this->decode($values);
    }


    /**
     * 这个命令的作用和 SDIFF key [key …] 类似，但它将结果保存到 destination 集合，而不是简单地返回结果集。
     * @param string $destination
     * @param array  $keys
     * @return int
     */
    public function sDiffStore(string $destination, array $keys): int
    {
        return $this->redis->sdiffstore($this->tagged($destination), $this->tagged($keys));
    }

    /**
     * 对比多个 key, 取相同的值
     * @params array $keys
     * @param $keys
     * @return array
     */
    public function sInter($keys): array
    {
        $values = $this->redis->sinter($this->tagged($keys));
        return $this->decode($values);
    }

    /**
     * 这个命令类似于 SINTER key [key …] 命令，但它将结果保存到 destination 集合，而不是简单地返回结果集。
     * 如果 destination 集合已经存在，则将其覆盖。
     * destination 可以是 key 本身
     * @param string $destination
     * @param array  $keys
     * @return int
     */
    public function sInterStore(string $destination, array $keys): int
    {
        return $this->redis->sinterstore($this->tagged($destination), $this->tagged($keys));
    }


    /**
     * 合并两个数据
     * @param array $keys
     * @return array|string|null
     */
    public function sUnion(array $keys): array
    {
        $values = $this->redis->sunion($this->tagged($keys));
        return $this->decode($values);
    }

    /**
     * 将对接的数据保存到指定目的地
     * @param string $destination
     * @param array  $keys
     * @return int
     */
    public function sUnionStore(string $destination, array $keys): int
    {
        return $this->redis->sunionstore($this->tagged($destination), $this->tagged($keys));
    }

    /**
     * 返回集合的数量
     * @param string $key key
     * @return int
     */
    public function sCard(string $key): int
    {
        return $this->redis->scard($this->tagged($key));
    }

    /**
     * 随机返回 count个元素
     * @param string $key   key
     * @param int    $count 数量
     * @return array
     */
    public function sRandMember(string $key, int $count): array
    {
        return (array) $this->redis->srandmember($this->tagged($key), $count);
    }

    /**
     * 移除一个或多个元素, 如果是多个元素, 需要用数组进行包裹
     * @param string $key    key
     * @param mixed  $member 成员
     * @return int
     */
    public function sRem(string $key, $member): int
    {
        if (is_array($member)) {
            $values = array_map([$this, 'toString'], (array) $member);
        }
        else {
            $values = $this->toString($member);
        }
        return $this->redis->srem($this->tagged($key), $values);
    }

    /**
     * 检测是否是集合内的元素
     * @param string $key
     * @param mixed  $member 单个元素
     * @return bool
     */
    public function sIsMember(string $key, $member): bool
    {
        $member = $this->toString($member);
        return (bool) $this->redis->sismember($this->tagged($key), $member);
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function sPop(string $key)
    {
        $value = $this->redis->spop($this->tagged($key));
        return $this->decode($value);
    }

    /**
     * 将元素从一个列表移动到另外一个列表
     * SMOVE 是原子性操作
     * @param string $source
     * @param string $dist
     * @param mixed  $member
     * @return int
     */
    public function sMove(string $source, string $dist, $member): int
    {
        $values = $this->toString($member);
        return $this->redis->smove($this->tagged($source), $this->tagged($dist), $values);
    }

    /**
     * @param string $key
     * @param int    $cursor
     * @param array  $options
     * @return array
     */
    public function sScan(string $key, $cursor = 0, $options = []): array
    {
        return $this->redis->sscan($this->tagged($key), $cursor, $options);
    }

    /**
     * 获取集合的所有成员
     * @param string $key
     * @return array
     */
    public function sMembers(string $key): array
    {
        $values = $this->redis->smembers($this->tagged($key));
        return $this->decode($values);
    }

    /**
     * 有序集合
     * @param       $key
     * @param array $membersAndScoresDictionary 参数 ['value' => 'score']
     * @return int
     */
    public function zAdd($key, array $membersAndScoresDictionary): int
    {
        return $this->redis->zadd($this->tagged($key), $membersAndScoresDictionary);
    }

    /**
     * @param string $key
     * @param mixed  $increment
     * @param string $member
     * @return string
     */
    public function zIncrBy(string $key, $increment, string $member): string
    {
        return $this->redis->zincrby($this->tagged($key), $increment, $member);
    }

    /**
     * @param $key
     * @return int
     */
    public function zCard($key): int
    {
        return $this->redis->zcard($this->tagged($key));
    }

    /**
     * 返回有序集 key 中， score 值在 min 和 max 之间(默认包括 score 值等于 min 或 max )的成员的数量
     * @param string $key
     * @param mixed  $min
     * @param mixed  $max
     * @return int
     */
    public function zCount(string $key, $min, $max): int
    {
        return $this->redis->zcount($this->tagged($key), $min, $max);
    }

    /**
     * 对于一个所有成员的分值都相同的有序集合键 key 来说， 这个命令会返回该集合中， 成员介于 min 和 max 范围内的元素数量
     * @param string $key
     * @param string $min
     * @param string $max
     * @return int
     */
    public function zLexCount(string $key, string $min, string $max): int
    {
        return $this->redis->zlexcount($this->tagged($key), $min, $max);
    }

    /**
     * @param string $key
     * @param mixed  $member
     * @return int
     */
    public function zRem(string $key, $member): int
    {
        return $this->redis->zrem($this->tagged($key), (array) $member);
    }

    /**
     * 返回成员的排名, 按照score从小到大的排名, 最小为0
     * @param string $key
     * @param string $member
     * @return int|null
     */
    public function zRank(string $key, string $member): ?int
    {
        return $this->redis->zrank($this->tagged($key), $member);
    }

    /**
     * 返回有序集 key 中，指定区间内的成员。
     * 其中成员的位置按 score 值递增(从小到大)来排序。这里取的是索引值,就是排序的第几个值作为顺序
     * @param string $key
     * @param mixed  $start
     * @param mixed  $stop
     * @param array  $options
     * @return array
     */
    public function zRange(string $key, $start, $stop, $options = []): array
    {
        return $this->redis->zrange($this->tagged($key), $start, $stop, $options);
    }


    /**
     * 返回有序集 key 中，所有 score 值介于 min 和 max 之间(包括等于 min 或 max )的成员。有序集成员按 score 值递增(从小到大)次序排列
     * @param string $key
     * @param int    $min
     * @param int    $max
     * @param array  $options
     * @return array
     */
    public function zRangeByScore(string $key, int $min, int $max, $options = []): array
    {
        return $this->redis->zrangebyscore($this->tagged($key), $min, $max, $options);
    }

    /**
     * 返回有序集 key 中， score 值介于 max 和 min 之间(默认包括等于 max 或 min )的所有的成员。有序集成员按 score 值递减(从大到小)的次序排列
     * @param string $key
     * @param int    $min
     * @param int    $max
     * @param array  $options
     * @return array
     */
    public function zRevRangeByScore(string $key, int $min, int $max, $options = []): array
    {
        return $this->redis->zrevrangebyscore($this->tagged($key), $min, $max, $options);
    }

    /**
     * 移除有序集 key 中，所有 score 值介于 min 和 max 之间(包括等于 min 或 max )的成员。
     * @param string $key
     * @param mixed  $start
     * @param mixed  $stop
     * @return int
     */
    public function zRemRangeByScore(string $key, $start, $stop): int
    {
        return $this->redis->zremrangebyscore($this->tagged($key), $start, $stop);
    }


    /**
     * 根据字符信息移除相关的数据
     * @param string $key
     * @param string $start
     * @param string $stop
     * @return int
     */
    public function zRemRangeByLex(string $key, string $start, string $stop): int
    {
        return $this->redis->zremrangebylex($this->tagged($key), $start, $stop);
    }

    /**
     * 移除有序集 key 中，指定排名(rank)区间内的所有成员
     * @param string $key
     * @param int    $start
     * @param int    $stop
     * @return int
     */
    public function zRemRangeByRank(string $key, int $start, int $stop): int
    {
        return $this->redis->zremrangebyrank($this->tagged($key), $start, $stop);
    }

    /**
     * @param string $key
     * @param mixed  $start
     * @param mixed  $stop
     * @param array  $options
     * @return array
     */
    public function zRevRange(string $key, $start, $stop, $options = []): array
    {
        return $this->redis->zrevrange($this->tagged($key), $start, $stop, $options);
    }


    /**
     * 有序集合的元素会根据成员的字典序（lexicographical ordering）来进行排序
     * 合法的 min 和 max 参数必须包含 ( 或者 [ ， 其中 ( 表示开区间（指定的值不会被包含在范围之内）， 而 [ 则表示闭区间（指定的值会被包含在范围之内）
     * @param string $key
     * @param string $start
     * @param string $stop
     * @param array  $options
     * @return array
     */
    public function zRangeByLex(string $key, string $start, string $stop, $options = []): array
    {
        return $this->redis->zrangebylex($this->tagged($key), $start, $stop, $options);
    }

    /**
     * @param string $key
     * @param int    $cursor
     * @param array  $options
     * @return array
     */
    public function zScan(string $key, $cursor = 0, $options = []): array
    {
        return $this->redis->zscan($this->tagged($key), $cursor, $options);
    }

    /**
     * @param string $key
     * @param string $member
     * @return string|null
     */
    public function zScore(string $key, string $member): ?string
    {
        return $this->redis->zscore($this->tagged($key), $member);
    }

    /**
     * 计算给定的一个或多个有序集的并集，其中给定 key 的数量必须以 numkeys 参数指定，并将该并集(结果集)储存到 destination
     * @param string       $destination
     * @param array|string $keys
     * @param array|null   $options
     * @return string|null
     */
    public function zUnionStore(string $destination, $keys, array $options = []): ?string
    {
        $keys = $this->tagged($keys);
        return $this->redis->zunionstore($this->tagged($destination), $keys, $options);
    }

    /**
     * 计算给定的一个或多个有序集的交集，其中给定 key 的数量必须以 numkeys 参数指定，并将该交集(结果集)储存到 destination 。
     * @param string       $destination
     * @param array|string $keys
     * @param array        $options
     * @return string|null
     */
    public function zInterStore(string $destination, $keys, array $options = []): ?string
    {
        $keys = $this->tagged($keys);
        return $this->redis->zinterstore($this->tagged($destination), $keys, $options);
    }

    /**
     * 反向的进行验证Rank
     * @param string $key
     * @param string $member
     * @return int|null
     */
    public function zRevRank(string $key, string $member): ?int
    {
        return $this->redis->zrevrank($this->tagged($key), $member);
    }

    /**
     * 添加用户地址位置
     * @param string $key       key
     * @param string $longitude 经度
     * @param string $latitude  纬度
     * @param string $member    成员
     * @return bool
     */
    public function geoAdd(string $key, string $longitude, string $latitude, string $member): bool
    {
        return (bool) $this->redis->geoadd($this->tagged($key), $longitude, $latitude, $member);
    }

    /**
     * 获取用户地理位置
     * @param string       $key     key
     * @param array|string $members 用户列表
     * @return array|null
     */
    public function geoPos(string $key, $members): ?array
    {
        return $this->redis->geopos($this->tagged($key), (array) $members);
    }

    /**
     * 计算两个位置距离
     * @param string    $key     key
     * @param int|mixed $member1 成员1
     * @param int|mixed $member2 成员2
     * @param string    $unit    单位[m:米; km:千米; mi:英里; ft:英尺]
     * @return string|null
     */
    public function geoDist(string $key, $member1, $member2, $unit = 'm'): ?string
    {
        return $this->redis->geodist($this->tagged($key), $member1, $member2, $unit);
    }

    /**
     * 以给定的经纬度为中心， 返回键包含的位置元素当中， 与中心的距离不超过给定最大距离的所有位置元素。
     * @param string       $key       key
     * @param string       $longitude 经度
     * @param string       $latitude  纬度
     * @param string|float $radius    距离/半径
     * @param string       $unit      单位 [m;km;mi;ft]
     * @param array|null   $options   附加选项[WITHDIST: 返回距离; WITHCOORD: 返回经纬度; WITHHASH:返回hash值; count: 返回数量]
     * @return mixed
     */
    public function geoRadius(string $key, string $longitude, string $latitude, $radius, string $unit, array $options = [])
    {
        if (isset($options['storedist'])) {
            $options['storedist'] = $this->tagged($options['storedist']);
        }
        return $this->redis->georadius($this->tagged($key), $longitude, $latitude, $radius, $unit, $options);
    }

    /**
     * 以给定的元素为中心，与中心的距离不超过给定最大距离的所有位置元素。
     * @param string       $key     key
     * @param string       $member  成员
     * @param string|float $radius  距离/半径
     * @param string       $unit    单位 [m;km;mi;ft]
     * @param array|null   $options 附加选项[WITHDIST: 返回距离; WITHCOORD: 返回经纬度; WITHHASH:返回hash值; count: 返回数量]
     * @return mixed
     */
    public function geoRadiusByMember(string $key, string $member, $radius, string $unit, array $options = [])
    {
        if (isset($options['storedist'])) {
            $options['storedist'] = $this->tagged($options['storedist']);
        }
        return $this->redis->georadiusbymember($this->tagged($key), $member, $radius, $unit, $options);
    }

    /**
     * @param string $key
     * @param mixed  $members
     * @return array
     */
    public function geoHash(string $key, $members): array
    {
        return $this->redis->geohash($this->tagged($key), (array) $members);
    }

    /**
     * 选择数据库
     * @param string $database 数据库
     * @return bool
     * @throws RedisException
     */
    public function select(string $database): bool
    {
        $database = config("database.redis.$database.database");

        if ($database === null) {
            throw new RedisException("database [$database] not found");
        }

        try {
            $this->redis->select((int) $database);
        } catch (Throwable $e) {
            throw new RedisException('系统内部错误');
        }

        return true;
    }

    /**
     * 断开连接
     * @return bool
     */
    public function disconnect(): bool
    {
        try {
            $this->redis->disconnect();
        } catch (Throwable $e) {

        }

        return true;
    }

    /**
     * 删除标签key
     * @param string|array $keys key
     * @return int
     * @deprecated 3.1
     * @removed    4.0
     * @see        del
     */
    public function delTaggedKeys($keys): int
    {
        return $this->redis->del((array) $keys);
    }

    /**
     * 扫描所有key
     * @param       $cursor
     * @param array $options
     * @return array
     */
    public function scan($cursor, array $options = []): array
    {
        return $this->redis->scan($cursor, $options);
    }

    /*
    |--------------------------------------------------------------------------
    | Db
    |--------------------------------------------------------------------------
    |
    */

    /**
     * @param Closure $closure
     * @return array|Pipeline
     */
    public function pipeline(Closure $closure)
    {
        return $this->redis->pipeline($closure);
    }


    /**
     * @param $key
     * @return bool
     */
    public function exists($key): bool
    {
        return (bool) $this->redis->exists($this->tagged($key));
    }

    /**
     * 返回或保存给定列表、集合、有序集合 key 中经过排序的元素
     * @param string $key
     * @param array  $options
     * @return array
     */
    public function sort(string $key, $options = []): array
    {
        return $this->redis->sort($this->tagged($key), $options);
    }

    /**
     * 返回 key 所储存的值的类型
     * @param $key
     * @return string
     */
    public function type($key): string
    {
        return $this->redis->type($this->tagged($key));
    }

    /**
     * 清空当前数据库的所有KEY
     * @return bool
     */
    public function flushDb(): bool
    {
        return $this->redis->flushdb();
    }

    /**
     * 清空整个 Redis 服务器的数据
     * @return bool
     */
    public function flushAll(): bool
    {
        return $this->redis->flushall();
    }


    /**
     * 重命名
     * @param string $key    目标KEY
     * @param string $target 目标地址
     * @return bool
     */
    public function rename(string $key, string $target): bool
    {
        return (bool) $this->redis->rename($this->tagged($key), $this->tagged($target));
    }


    /**
     * 将当前数据库的 key 移动到给定的数据库 db 当中
     * @param string $key
     * @param mixed  $db
     * @return bool
     * @throws RedisException
     */
    public function move(string $key, $db): bool
    {
        $database = config("database.redis.$db.database");

        if ($database === null) {
            throw new RedisException("database [$db] not found");
        }

        try {
            return (bool) $this->redis->move($this->tagged($key), $database['database']);
        } catch (Throwable $e) {
            throw new RedisException('系统内部错误');
        }
    }

    /**
     * 不存在时候命名
     * @param string $key    目标KEY
     * @param string $target 目标地址
     * @return bool
     */
    public function renameNx(string $key, string $target): bool
    {
        return (bool) $this->redis->renamenx($this->tagged($key), $this->tagged($target));
    }


    /**
     * 当 key 不存在时，返回 -2 .当 key 存在但没有设置剩余生存时间时，返回 -1 。 否则，以秒为单位，返回 key 的剩余生存时间
     * @param string $key
     * @return int
     */
    public function ttl(string $key): int
    {
        return $this->redis->ttl($this->tagged($key));
    }

    /**
     * 这个命令类似于 TTL 命令，但它以毫秒为单位返回 key 的剩余生存时间，而不是像 TTL 命令那样，以秒为单位
     * @param string $key
     * @return int
     */
    public function pTtl(string $key): int
    {
        return $this->redis->pttl($this->tagged($key));
    }


    /**
     * 持久化当前KEY
     * @param string $key
     * @return int
     */
    public function persist(string $key): int
    {
        return $this->redis->persist($this->tagged($key));
    }

    /**
     * 设置有效期
     * @param string $key     key
     * @param int    $seconds 秒数
     * @return bool
     */
    public function expire(string $key, int $seconds): bool
    {
        return (bool) $this->redis->expire($this->tagged($key), $seconds);
    }

    /**
     * 这个命令和 EXPIRE 命令的作用类似，但是它以毫秒为单位设置 key 的生存时间，而不像 EXPIRE 命令那样，以秒为单位
     * @param string $key
     * @param int    $milliseconds
     * @return bool
     */
    public function pExpire(string $key, int $milliseconds): bool
    {
        return (bool) $this->redis->expire($this->tagged($key), $milliseconds);
    }

    /**
     * 返回一个随机KEY
     * @return string
     */
    public function randomKey(): string
    {
        return $this->redis->randomkey();
    }


    /**
     * 返回当前数据库的 key 的数量
     * @return int
     */
    public function dbSize(): int
    {
        return $this->redis->dbsize();
    }

    /**
     * 设置有效期
     * @param string $key       key
     * @param int    $timestamp 失效时间
     * @return bool
     */
    public function expireAt(string $key, int $timestamp): bool
    {
        return (bool) $this->redis->expireat($this->tagged($key), $timestamp);
    }

    /**
     * 以毫秒为单位设置过期时间戳
     * @param string $key
     * @param int    $mill_timestamp
     * @return bool
     */
    public function pExpireAt(string $key, int $mill_timestamp): bool
    {
        return (bool) $this->redis->expireat($this->tagged($key), $mill_timestamp);
    }

    /**
     * @return mixed
     */
    public function multi()
    {
        return $this->redis->multi();
    }

    /**
     * 执行所有事务块内的命令
     * @return array
     */
    public function exec(): ?array
    {
        return $this->redis->exec();
    }

    /**
     * 取消事务, 放弃执行事务内的所有命令
     * @return mixed
     */
    public function discard()
    {
        return $this->redis->discard();
    }

    /**
     * 取消事务, 放弃执行事务内的所有命令
     * @return mixed
     */
    public function watch($key)
    {
        return $this->redis->watch((array) $key);
    }

    /**
     * 取消 WATCH 命令对所有 key 的监视
     * @return bool
     */
    public function unwatch(): bool
    {
        return $this->redis->unwatch();
    }

    /**
     * 获取key下面的所有key
     * KEYS * 匹配数据库中所有 key 。
     * KEYS h?llo 匹配 hello ， hallo 和 hxllo 等。
     * KEYS h*llo 匹配 hllo 和 heeeeello 等。
     * KEYS h[ae]llo 匹配 hello 和 hallo ，但不匹配 hillo 。
     * @param string $key 给定的key
     * @return array
     */
    public function keys(string $key): array
    {
        return $this->redis->keys($this->tagged($key));
    }

    /**
     * 删除key
     * @param string|array $key 缓存key
     * @return int
     */
    public function del($key): int
    {
        return $this->redis->del(array_map(function ($key) {
            return $this->tagged($key);
        }, (array) $key));
    }

    /*
    |--------------------------------------------------------------------------
    | BitMap
    |--------------------------------------------------------------------------
    |
    */

    /**
     * 设置位图
     * @param string $key    key
     * @param int    $offset 偏移量
     * @param int    $value  值
     * @return int
     */
    public function setBit(string $key, int $offset, int $value): int
    {
        return $this->redis->setbit($this->tagged($key), $offset, $value);
    }

    /**
     * 获取位图上偏移量的位
     * @param string $key
     * @param int    $offset
     * @return int
     */
    public function getBit(string $key, int $offset): int
    {
        return $this->redis->getbit($this->tagged($key), $offset);
    }

    /**
     * 计算给定字符串中，被设置为 1 的比特位的数量
     * @param string $key
     * @param int    $start
     * @param int    $end
     * @return int
     */
    public function bitCount(string $key, $start = 0, $end = -1): int
    {
        return $this->redis->bitcount($this->tagged($key), $start, $end);
    }

    /**
     * 返回位图中第一个值为 bit 的二进制位的位置。
     * 在默认情况下， 命令将检测整个位图， 但用户也可以通过可选的 start 参数和 end 参数指定要检测的范围。
     * @param string $key
     * @param string $bit
     * @param int    $start
     * @param int    $end
     * @return int
     */
    public function bitPos(string $key, string $bit, $start = 0, $end = -1): int
    {
        return $this->redis->bitpos($this->tagged($key), $bit, $start, $end);
    }

    /**
     * @param $key
     * @param $subcommand
     * @param ...$subcommandArg
     * @return array
     * @url http://redisdoc.com/bitmap/bitfield.html
     */
    public function bitField($key, $subcommand, ...$subcommandArg): array
    {
        return $this->redis->bitfield($this->tagged($key), $subcommand, ...$subcommandArg);
    }

    /**
     * 对一个或多个保存二进制位的字符串 key 进行位元操作，并将结果保存到 destkey 上
     * @param string $operation
     * @param string $distKey
     * @param string $key
     * @return int
     */
    public function bitOp(string $operation, string $distKey, string $key): int
    {
        $distKey = $this->tagged($distKey);
        $key     = $this->tagged($key);
        return $this->redis->bitop($operation, $distKey, $key);
    }


    /**
     * Get an item from the cache, or execute the given Closure and store the result.
     *
     * @param string                                    $key
     * @param \DateTimeInterface|\DateInterval|int|null $ttl
     * @param \Closure                                  $callback
     * @return mixed
     */
    public function remember(string $key, $ttl, Closure $callback)
    {
        $taggedKey = $this->tagged($key);

        $value = $this->get($taggedKey);

        // If the item exists in the cache we will just return this immediately and if
        // not we will execute the given Closure and cache the result of that for a
        // given number of seconds so it's available for all subsequent requests.
        if (!is_null($value)) {
            return $value;
        }

        $this->setEx($key, $ttl, $value = $callback());

        return $value;
    }

    /**
     * @param string|array|null $result
     * @return mixed
     */
    public function decode($result)
    {
        /**
         * @param mixed $val
         * @return mixed|string|null
         */
        $decode = function ($val) {
            if (!$val) {
                return $val;
            }
            try {
                return unserialize($val);
            } catch (Throwable $e) {
                if (UtilHelper::isJson($val)) {
                    return json_decode($val);
                }
                return $val;
            }
        };
        if (is_array($result)) {
            return array_map($decode, $result);
        }
        return $decode($result);
    }

    /**
     * 将数据转为字符串
     * @param array|string|Arrayable|stdClass $value
     * @return string
     */
    private function toString($value): string
    {
        if (is_string($value)) {
            return $value;
        }
        if (is_numeric($value)) {
            return $value;
        }
        return serialize($value);
    }

    /**
     * 返回Key/一系列KEY
     * @param string|array $key
     * @return string|array 可以返回数组或者字串
     */
    private function tagged($key)
    {

        if (is_array($key)) {
            $keys = [];
            foreach ($key as $_key) {
                $keys[] = $this->tagged($_key);
            }
            return $keys;
        }

        $prefix = config('cache.prefix');
        $return = $prefix;

        if ($this->cacheTag) {
            $return .= ":{$this->cacheTag}";
        }

        $return .= ":{$key}";

        return $return;
    }
}