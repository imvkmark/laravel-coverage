<?php

namespace Poppy\Core\Redis;

use Carbon\Carbon;
use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Poppy\Core\Classes\PyCoreDef;

/**
 * 缓存模拟器
 */
class RdsStore
{

    /**
     * 缓存器, 随机秒数缓存器, 不在同一时刻读取值
     * @param string $key    键
     * @param mixed  $value  值
     * @param int    $second 秒数
     * @return mixed
     */
    public static function seconds(string $key, $value, $second = 30)
    {
        $cacheData = [
            'expired' => Carbon::now()->addSeconds($second)->timestamp,
        ];
        $fetchData = sys_cache('py-core')->get(PyCoreDef::ckCacher($key));
        // 无数据 / 已过期
        if (!$fetchData || $fetchData['expired'] <= Carbon::now()->timestamp) {
            if ($value instanceof Closure) {
                $value = $value();
            }
            $cacheData['value'] = $value;
            sys_cache('py-core')->forever(PyCoreDef::ckCacher($key), $cacheData);

            return $cacheData['value'];
        }

        return $fetchData['value'];
    }

    /**
     * Redis Type Key
     * @param string $type Type
     * @param string $key  Key
     * @return string
     */
    public static function redisKey($type, $key): string
    {
        return 'redis:' . $type . ':' . $key;
    }

    /**
     * 单KEY 存储多条数据
     * @param string     $key   指定的KEY
     * @param string|int $index 索引值
     * @return mixed|null
     */
    public static function at(string $key, $index)
    {
        $tag       = Str::before($key, '.');
        $fetchData = (array) sys_cache($tag)->get($key);
        if (!isset($fetchData[$index])) {
            return null;
        }
        return $fetchData[$index];
    }

    /**
     * @param string     $key   指定的KEY
     * @param string|int $index 索引值
     * @param mixed|null $value 设置值
     * @return bool
     */
    public static function set($key, $index, $value = null): bool
    {
        $tag       = Str::before($key, '.');
        $fetchData = (array) sys_cache($tag)->get($key);

        if (!isset($fetchData[$index]) || $fetchData[$index] !== $value) {
            $fetchData[$index] = $value;
            sys_cache($tag)->forever($key, $fetchData);
            return true;
        }
        return true;
    }

    /**
     * 移除项目
     * @param string           $key   指定的KEY
     * @param string|int|array $index 索引值
     * @return bool
     */
    public static function unset($key, $index): bool
    {
        $tag       = Str::before($key, '.');
        $fetchData = (array) sys_cache($tag)->get($key);
        if (is_array($index) && count($index)) {
            foreach ($index as $idx) {
                if (isset($fetchData[$idx])) {
                    unset($fetchData[$idx]);
                }
            }
            sys_cache($tag)->forever($key, $fetchData);
            return true;
        }
        if (isset($fetchData[$index])) {
            unset($fetchData[$index]);
            sys_cache($tag)->forever($key, $fetchData);
            return true;
        }
        return true;
    }

    /**
     * 清除
     * @param string $key 清理这个KEY
     * @return bool
     */
    public static function clear($key): bool
    {
        $tag = Str::before($key, '.');
        sys_cache($tag)->get($key);
        return true;
    }

    /**
     * 原子性鉴定, 这个必须是 Redis 缓存才可生效
     * @param string $key     key
     * @param int    $seconds 秒数
     * @return bool
     */
    public static function inLock(string $key, int $seconds): bool
    {
        /* 非 Redis, 使用文件来进行缓存
         * ---------------------------------------- */
        if (strtolower(config('cache.default')) !== 'redis') {
            $now = Carbon::now()->timestamp;
            if (Cache::has($key)) {
                $content = Cache::get($key);
                if ($content < $now) {
                    Cache::forget($key);
                    return true;
                }
                return false;
            }
            Cache::forever($key, $now + $seconds);
            return true;
        }
        $key = 'py-core:rds-lock:' . $key;
        if (strtolower(config('cache.default')) === 'redis') {
            $client = RdsDb::instance();
            $res    = $client->set($key, 'atomic-' . Carbon::now()->timestamp, 'EX', $seconds, 'NX');
            if ($res === false) {
                return true;
            }
            return false;
        }
        return true;
    }
}
