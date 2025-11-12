<?php

namespace Tests\Core\RateLimiter;

use Core\RateLimiter\RedisRateLimiter;
use PHPUnit\Framework\TestCase;
use Predis\Client;

/**
 * Test Redis-based rate limiter
 *
 * Class RedisRateLimiterTest
 * @package Tests\Core\RateLimiter
 */
class RedisRateLimiterTest extends TestCase
{
    /**
     * @var RedisRateLimiter
     */
    private $rateLimiter;

    /**
     * @var Client
     */
    private $redis;

    /**
     * Setup test environment
     */
    protected function setUp(): void
    {
        // Use a test Redis database (db 15) to avoid conflicts
        $this->redis = new Client([
            'scheme' => 'tcp',
            'host' => '172.17.0.3',
            'port' => 6379,
            'database' => 15,
        ]);

        // Check if Redis is available
        try {
            $this->redis->ping();
        } catch (\Exception $e) {
            $this->markTestSkipped('Redis server is not available');
        }

        $this->rateLimiter = new RedisRateLimiter($this->redis);
    }

    /**
     * Cleanup test data
     */
    protected function tearDown(): void
    {
        if ($this->redis) {
            try {
                // Clean up test keys
                $keys = $this->redis->keys('rate_limit:*');
                if (!empty($keys)) {
                    $this->redis->del($keys);
                }
            } catch (\Exception $e) {
                // Ignore cleanup errors
            }
        }
    }

    /**
     * Test rate limit check when no previous requests
     */
    public function testCheckLimitNoPreviousRequests(): void
    {
        $identifier = '127.0.0.1';
        $limit = 10;
        $window = 60;

        $result = $this->rateLimiter->checkLimit($identifier, $limit, $window);

        $this->assertTrue($result['allowed']);
        $this->assertEquals($limit, $result['remaining']);
        $this->assertGreaterThan(time(), $result['reset']);
    }

    /**
     * Test rate limit after incrementing
     */
    public function testCheckLimitAfterIncrement(): void
    {
        $identifier = '127.0.0.1';
        $limit = 5;
        $window = 60;

        // Increment 3 times
        $this->rateLimiter->increment($identifier, $window);
        $this->rateLimiter->increment($identifier, $window);
        $this->rateLimiter->increment($identifier, $window);

        $result = $this->rateLimiter->checkLimit($identifier, $limit, $window);

        $this->assertTrue($result['allowed']);
        $this->assertEquals(2, $result['remaining']); // 5 - 3 = 2
    }

    /**
     * Test rate limit exceeded
     */
    public function testRateLimitExceeded(): void
    {
        $identifier = '127.0.0.1';
        $limit = 3;
        $window = 60;

        // Increment to the limit
        for ($i = 0; $i < $limit; $i++) {
            $this->rateLimiter->increment($identifier, $window);
        }

        $result = $this->rateLimiter->checkLimit($identifier, $limit, $window);

        $this->assertFalse($result['allowed']);
        $this->assertEquals(0, $result['remaining']);
    }

    /**
     * Test reset functionality
     */
    public function testReset(): void
    {
        $identifier = '127.0.0.1';
        $limit = 5;
        $window = 60;

        // Increment a few times
        $this->rateLimiter->increment($identifier, $window);
        $this->rateLimiter->increment($identifier, $window);

        $result = $this->rateLimiter->checkLimit($identifier, $limit, $window);
        $this->assertEquals(3, $result['remaining']);

        // Reset
        $this->rateLimiter->reset($identifier);

        // Should be back to full limit
        $result = $this->rateLimiter->checkLimit($identifier, $limit, $window);
        $this->assertTrue($result['allowed']);
        $this->assertEquals($limit, $result['remaining']);
    }

    /**
     * Test different identifiers are tracked separately
     */
    public function testDifferentIdentifiers(): void
    {
        $identifier1 = '127.0.0.1';
        $identifier2 = '192.168.1.1';
        $limit = 5;
        $window = 60;

        // Increment for identifier1
        $this->rateLimiter->increment($identifier1, $window);
        $this->rateLimiter->increment($identifier1, $window);

        // Increment for identifier2
        $this->rateLimiter->increment($identifier2, $window);

        $result1 = $this->rateLimiter->checkLimit($identifier1, $limit, $window);
        $result2 = $this->rateLimiter->checkLimit($identifier2, $limit, $window);

        $this->assertEquals(3, $result1['remaining']); // 5 - 2 = 3
        $this->assertEquals(4, $result2['remaining']); // 5 - 1 = 4
    }
}

