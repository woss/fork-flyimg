<?php

namespace Tests\Core\RateLimiter;

use Core\RateLimiter\FileRateLimiter;
use PHPUnit\Framework\TestCase;

/**
 * Test file-based rate limiter
 *
 * Class FileRateLimiterTest
 * @package Tests\Core\RateLimiter
 */
class FileRateLimiterTest extends TestCase
{
    /**
     * @var FileRateLimiter
     */
    private $rateLimiter;

    /**
     * @var string
     */
    private $testStorageDir;

    /**
     * Setup test environment
     */
    protected function setUp(): void
    {
        $this->testStorageDir = sys_get_temp_dir() . '/flyimg_ratelimit_test_' . uniqid() . '/';
        $this->rateLimiter = new FileRateLimiter($this->testStorageDir);
    }

    /**
     * Cleanup test files
     */
    protected function tearDown(): void
    {
        if (is_dir($this->testStorageDir)) {
            $files = glob($this->testStorageDir . '*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            rmdir($this->testStorageDir);
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

