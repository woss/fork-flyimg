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
        // Use environment variable for Redis host (for CI), fallback to localhost
        $redisHost = getenv('REDIS_HOST') ?: 'localhost';
        $redisPort = getenv('REDIS_PORT') ?: 6379;

        // Use a test Redis database (db 15) to avoid conflicts
        $this->redis = new Client([
            'scheme' => 'tcp',
            'host' => $redisHost,
            'port' => (int)$redisPort,
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

    public function testIncrementUsesInstanceScopedKeyFormatWhenInstanceIdProvided(): void
    {
        $identifier = '127.0.0.1';
        $window = 60;
        $hash = md5($identifier);
        $instanceId = 'deployment-a';

        // Use a dedicated rate limiter instance with instance id on the same Redis connection
        $rateLimiter = new RedisRateLimiter($this->redis, $instanceId);
        $rateLimiter->increment($identifier, $window);

        $keys = $this->redis->keys('rate_limit:*');
        $this->assertNotEmpty($keys);

        $key = $keys[0];
        $this->assertMatchesRegularExpression(
            sprintf('#^rate_limit:%s:%s:%d:\d+$#', $instanceId, $hash, $window),
            $key
        );
    }

    public function testResetUsesLegacyPatternWhenNoInstanceId(): void
    {
        $identifier = '127.0.0.1';
        $window = 60;
        $limit = 5;

        // Create some legacy-format keys using the default rate limiter (no instance id)
        $this->rateLimiter->increment($identifier, $window);
        $this->rateLimiter->increment($identifier, $window);

        $keysBefore = $this->redis->keys('rate_limit:*');
        $this->assertNotEmpty($keysBefore);

        // Reset and ensure all matching keys are removed
        $this->rateLimiter->reset($identifier);

        $keysAfter = $this->redis->keys('rate_limit:*');
        $this->assertEmpty($keysAfter);
    }

    public function testResetUsesInstanceScopedPatternWhenInstanceIdProvided(): void
    {
        $identifier = '127.0.0.1';
        $hash = md5($identifier);
        $instanceId = 'deployment-a';

        $rateLimiter = new RedisRateLimiter($this->redis, $instanceId);

        // Create some instance-scoped keys
        $rateLimiter->increment($identifier, 60);

        $keysBefore = $this->redis->keys('rate_limit:*');
        $this->assertNotEmpty($keysBefore);
        $this->assertMatchesRegularExpression(
            sprintf('#^rate_limit:%s:%s:%d:\d+$#', $instanceId, $hash, 60),
            $keysBefore[0]
        );

        // Reset and ensure keys for this identifier/instance are removed
        $rateLimiter->reset($identifier);

        $keysAfter = $this->redis->keys('rate_limit:*');
        $this->assertEmpty($keysAfter);
    }
}
