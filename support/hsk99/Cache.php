<?php

namespace support\hsk99;

use support\Redis;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Psr16Cache;

/**
 * Class Cache
 * @package support\hsk99
 *
 * Strings methods
 * @method static mixed get($key, $default = null)
 * @method static bool set($key, $value, $ttl = null)
 * @method static bool delete($key)
 * @method static bool clear()
 * @method static iterable getMultiple($keys, $default = null)
 * @method static bool setMultiple($values, $ttl = null)
 * @method static bool deleteMultiple($keys)
 * @method static bool has($key)
 */
class Cache extends \support\Cache
{
    /**
     * @var Psr16Cache
     */
    public static $_instance = null;

    /**
     * @return Psr16Cache
     */
    public static final function instance()
    {
        if (!static::$_instance) {
            $adapter = new RedisAdapter(Redis::connection(config('cache.redis_client', 'default'))->client(), config('cache.namespace', 'cache'), config('cache.lifetime', 0));
            self::$_instance = new Psr16Cache($adapter);
        }
        return static::$_instance;
    }
}
