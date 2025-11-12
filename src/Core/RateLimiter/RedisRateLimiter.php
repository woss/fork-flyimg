<?php

namespace Core\RateLimiter;

use Predis\Client;
use Predis\Connection\ConnectionException;

/**
 * Redis-based rate limiter implementation
 *
 * Class RedisRateLimiter
 * @package Core\RateLimiter
 */
class RedisRateLimiter implements RateLimiterInterface
{
    /**
     * @var Client Redis client
     */
    private $redis;

    /**
     * @var bool Whether Redis connection is available
     */
    private $connectionAvailable = true;

    /**
     * @param Client|null $redis Redis client instance (optional, will create default if not provided)
     */
    public function __construct(Client $redis = null)
    {
        if ($redis === null) {
            // Default Redis connection: localhost:6379
            $this->redis = new Client([
                'scheme' => 'tcp',
                'host' => '127.0.0.1',
                'port' => 6379,
            ]);
        } else {
            $this->redis = $redis;
        }

        // Test connection on construction
        $this->testConnection();
    }

    /**
     * Test Redis connection
     *
     * @return bool
     */
    private function testConnection(): bool
    {
        try {
            $this->redis->ping();
            $this->connectionAvailable = true;
            return true;
        } catch (\Exception $e) {
            $this->connectionAvailable = false;
            error_log('Redis rate limiter connection failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get the Redis key for a given identifier and window
     *
     * @param string $identifier
     * @param int $window
     * @return string
     */
    private function getKey(string $identifier, int $window): string
    {
        $windowStart = $this->getWindowStart(time(), $window);
        return sprintf('rate_limit:%s:%d:%d', md5($identifier), $window, $windowStart);
    }

    /**
     * Calculate the start of the current window
     *
     * @param int $currentTime
     * @param int $window
     * @return int
     */
    private function getWindowStart(int $currentTime, int $window): int
    {
        return (int)floor($currentTime / $window) * $window;
    }

    /**
     * {@inheritdoc}
     */
    public function checkLimit(string $identifier, int $limit, int $window): array
    {
        $currentTime = time();
        $windowStart = $this->getWindowStart($currentTime, $window);
        $key = $this->getKey($identifier, $window);

        // If connection was previously unavailable, try to reconnect
        if (!$this->connectionAvailable) {
            $this->testConnection();
        }

        if (!$this->connectionAvailable) {
            // If Redis is unavailable, allow the request (fail open) but log warning
            error_log('Redis rate limiter: Connection unavailable, allowing request (fail open)');
            return [
                'allowed' => true,
                'remaining' => $limit,
                'reset' => $windowStart + $window
            ];
        }

        try {
            $count = (int)$this->redis->get($key);
            $ttl = $this->redis->ttl($key);

            // If key doesn't exist or expired, count is 0
            if ($count === 0 || $ttl < 0) {
                return [
                    'allowed' => true,
                    'remaining' => $limit,
                    'reset' => $windowStart + $window
                ];
            }

            $remaining = max(0, $limit - $count);
            $allowed = $count < $limit;
            $reset = $windowStart + $window;

            return [
                'allowed' => $allowed,
                'remaining' => $remaining,
                'reset' => $reset
            ];
        } catch (ConnectionException $e) {
            $this->connectionAvailable = false;
            error_log('Redis rate limiter connection error: ' . $e->getMessage());
            // If Redis is unavailable, allow the request (fail open)
            return [
                'allowed' => true,
                'remaining' => $limit,
                'reset' => $windowStart + $window
            ];
        } catch (\Exception $e) {
            error_log('Redis rate limiter error: ' . $e->getMessage());
            // If Redis is unavailable, allow the request (fail open)
            return [
                'allowed' => true,
                'remaining' => $limit,
                'reset' => $windowStart + $window
            ];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function increment(string $identifier, int $window): void
    {
        if (!$this->connectionAvailable) {
            return;
        }

        $currentTime = time();
        $windowStart = $this->getWindowStart($currentTime, $window);
        $key = $this->getKey($identifier, $window);

        try {
            // Use INCR to atomically increment
            $count = $this->redis->incr($key);
            
            // Set expiration if this is the first increment in the window
            if ($count === 1) {
                $this->redis->expire($key, $window);
            }
        } catch (ConnectionException $e) {
            $this->connectionAvailable = false;
            error_log('Redis rate limiter increment connection error: ' . $e->getMessage());
        } catch (\Exception $e) {
            error_log('Redis rate limiter increment error: ' . $e->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function reset(string $identifier): void
    {
        if (!$this->connectionAvailable) {
            return;
        }

        // Redis keys expire automatically, but we can delete matching keys
        try {
            $pattern = 'rate_limit:' . md5($identifier) . ':*';
            $keys = $this->redis->keys($pattern);
            if (!empty($keys)) {
                $this->redis->del($keys);
            }
        } catch (ConnectionException $e) {
            $this->connectionAvailable = false;
            error_log('Redis rate limiter reset connection error: ' . $e->getMessage());
        } catch (\Exception $e) {
            error_log('Redis rate limiter reset error: ' . $e->getMessage());
        }
    }

    /**
     * Set Redis client (useful for testing or custom configuration)
     *
     * @param Client $redis
     * @return void
     */
    public function setRedisClient(Client $redis): void
    {
        $this->redis = $redis;
        $this->testConnection();
    }

    /**
     * Check if Redis connection is available
     *
     * @return bool
     */
    public function isConnectionAvailable(): bool
    {
        return $this->connectionAvailable;
    }
}

