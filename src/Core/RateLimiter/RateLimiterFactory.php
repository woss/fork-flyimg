<?php

namespace Core\RateLimiter;

use Core\Entity\AppParameters;
use Predis\Client;

/**
 * Factory to create rate limiter instances
 *
 * Class RateLimiterFactory
 * @package Core\RateLimiter
 */
class RateLimiterFactory
{
    /**
     * Create a rate limiter based on configuration
     *
     * @param AppParameters $params Application parameters
     * @return RateLimiterInterface
     */
    public static function create(AppParameters $params): RateLimiterInterface
    {
        $storageType = $params->parameterByKey('rate_limit_storage', 'memory');

        switch ($storageType) {
            case 'redis':
                $redisConfig = $params->parameterByKey('rate_limit_redis', []);
                $redis = self::createRedisClient($redisConfig);
                return new RedisRateLimiter($redis);
            case 'memory':
            default:
                return new MemoryRateLimiter();
        }
    }

    /**
     * Create Redis client from configuration
     *
     * @param array $config Redis configuration
     * @return Client
     * @throws \Exception If Redis connection fails and connection testing is enabled
     */
    private static function createRedisClient(array $config): Client
    {
        $defaults = [
            'scheme' => 'tcp',
            'host' => '127.0.0.1',
            'port' => 6379,
        ];

        $connection = array_merge($defaults, $config);

        // Support for Redis connection string (e.g., redis://localhost:6379)
        if (isset($config['url'])) {
            $client = new Client($config['url']);
        } else {
            $client = new Client($connection);
        }

        // Test connection and log if it fails
        try {
            $client->ping();
        } catch (\Exception $e) {
            error_log('Redis rate limiter: WARNING - Connection test failed: ' .
                $e->getMessage() . ' (host: ' . ($connection['host'] ?? 'default') .
                ', port: ' . ($connection['port'] ?? 'default') . ')'
            );
            // Continue anyway - connection will be tested on first use
        }

        return $client;
    }
}
