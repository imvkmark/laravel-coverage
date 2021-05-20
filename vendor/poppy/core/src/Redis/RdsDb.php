<?php

namespace Poppy\Core\Redis;

use Throwable;

/**
 * @mixin RdsNative
 */
class RdsDb
{

    /**
     * @var RdsNative $handler
     */
    private $handler;


    private static $handleRepo;

    /**
     * Handle constructor.
     * @param string $database
     */
    public function __construct($database = '')
    {
        $database      = $database ?: 'default';
        $config        = config('database.redis.' . $database);
        $this->handler = new RdsNative($config);
    }

    /**
     * 数据库单例
     * @param string $db
     * @return mixed|\Poppy\Core\Redis\RdsDb
     */
    public static function instance(string $db = 'default')
    {
        if (!isset(self::$handleRepo[$db])) {
            self::$handleRepo[$db] = new self($db);
        }
        return self::$handleRepo[$db];
    }

    /**
     * @param $method
     * @param $arguments
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        return $this->handler->$method(...$arguments);
    }

    public function __destruct()
    {
        try {
            $this->handler->disconnect();
        } catch (Throwable $e) {

        }
    }

    public static function __callStatic($method, $arguments)
    {
        return (new self)->$method(...$arguments);
    }
}