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
     * @var string Test storage directory
     */
    private $testStorageDir;

    /**
     * Setup test environment
     */
    protected function setUp(): void
    {
        // Create a unique temporary directory for each test
        $this->testStorageDir = sys_get_temp_dir() . '/flyimg_rate_limit_test_' . uniqid();
        $this->rateLimiter = new FileRateLimiter($this->testStorageDir);
    }

    /**
     * Cleanup test data
     */
    protected function tearDown(): void
    {
        if ($this->testStorageDir && is_dir($this->testStorageDir)) {
            // Remove all test files
            $files = glob($this->testStorageDir . '/*');
            if ($files !== false) {
                foreach ($files as $file) {
                    if (is_file($file)) {
                        @unlink($file);
                    }
                }
            }
            @rmdir($this->testStorageDir);
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

    /**
     * Test window expiration
     */
    public function testWindowExpiration(): void
    {
        $identifier = '127.0.0.1';
        $limit = 5;
        $window = 2; // Very short window for testing

        // Increment a few times
        $this->rateLimiter->increment($identifier, $window);
        $this->rateLimiter->increment($identifier, $window);

        $result = $this->rateLimiter->checkLimit($identifier, $limit, $window);
        $this->assertEquals(3, $result['remaining']);

        // Wait for window to expire
        sleep($window + 1);

        // Should be reset after window expiration
        $result = $this->rateLimiter->checkLimit($identifier, $limit, $window);
        $this->assertTrue($result['allowed']);
        $this->assertEquals($limit, $result['remaining']);
    }

    /**
     * Test default storage directory creation
     */
    public function testDefaultStorageDirectory(): void
    {
        // Create rate limiter without specifying directory
        $rateLimiter = new FileRateLimiter();

        $identifier = '127.0.0.1';
        $limit = 10;
        $window = 60;

        // Should work without errors
        $result = $rateLimiter->checkLimit($identifier, $limit, $window);
        $this->assertTrue($result['allowed']);

        // Cleanup default directory if it was created in temp
        $defaultDir = '/tmp/flyimg/ratelimit';
        if (is_dir($defaultDir)) {
            $files = glob($defaultDir . '/*');
            if ($files !== false) {
                foreach ($files as $file) {
                    if (is_file($file)) {
                        @unlink($file);
                    }
                }
            }
        }
    }

    /**
     * Test multiple windows for same identifier
     */
    public function testMultipleWindows(): void
    {
        $identifier = '127.0.0.1';
        $limit = 5;
        $window1 = 60;  // 1 minute
        $window2 = 3600; // 1 hour

        // Increment for minute window
        $this->rateLimiter->increment($identifier, $window1);
        $this->rateLimiter->increment($identifier, $window1);

        // Increment for hour window
        $this->rateLimiter->increment($identifier, $window2);

        $result1 = $this->rateLimiter->checkLimit($identifier, $limit, $window1);
        $result2 = $this->rateLimiter->checkLimit($identifier, $limit, $window2);

        $this->assertEquals(3, $result1['remaining']); // 5 - 2 = 3
        $this->assertEquals(4, $result2['remaining']); // 5 - 1 = 4
    }

    /**
     * Test rate limiting per hour
     */
    public function testRateLimitPerHour(): void
    {
        $identifier = '127.0.0.1_hour';
        $limit = 100;
        $window = 3600; // 1 hour in seconds

        // Check initial state
        $result = $this->rateLimiter->checkLimit($identifier, $limit, $window);
        $this->assertTrue($result['allowed']);
        $this->assertEquals($limit, $result['remaining']);

        // Increment 50 times
        for ($i = 0; $i < 50; $i++) {
            $this->rateLimiter->increment($identifier, $window);
        }

        $result = $this->rateLimiter->checkLimit($identifier, $limit, $window);
        $this->assertTrue($result['allowed']);
        $this->assertEquals(50, $result['remaining']); // 100 - 50 = 50

        // Increment to the limit
        for ($i = 0; $i < 50; $i++) {
            $this->rateLimiter->increment($identifier, $window);
        }

        $result = $this->rateLimiter->checkLimit($identifier, $limit, $window);
        $this->assertFalse($result['allowed']);
        $this->assertEquals(0, $result['remaining']);
    }

    /**
     * Test rate limiting per day
     */
    public function testRateLimitPerDay(): void
    {
        $identifier = '127.0.0.1_day';
        $limit = 1000;
        $window = 86400; // 1 day in seconds (24 * 60 * 60)

        // Check initial state
        $result = $this->rateLimiter->checkLimit($identifier, $limit, $window);
        $this->assertTrue($result['allowed']);
        $this->assertEquals($limit, $result['remaining']);

        // Increment 500 times
        for ($i = 0; $i < 500; $i++) {
            $this->rateLimiter->increment($identifier, $window);
        }

        $result = $this->rateLimiter->checkLimit($identifier, $limit, $window);
        $this->assertTrue($result['allowed']);
        $this->assertEquals(500, $result['remaining']); // 1000 - 500 = 500

        // Increment to the limit
        for ($i = 0; $i < 500; $i++) {
            $this->rateLimiter->increment($identifier, $window);
        }

        $result = $this->rateLimiter->checkLimit($identifier, $limit, $window);
        $this->assertFalse($result['allowed']);
        $this->assertEquals(0, $result['remaining']);
    }

    /**
     * Test rate limiting per month (30 days)
     */
    public function testRateLimitPerMonth(): void
    {
        $identifier = '127.0.0.1_month';
        $limit = 10000;
        $window = 2592000; // 30 days in seconds (30 * 24 * 60 * 60)

        // Check initial state
        $result = $this->rateLimiter->checkLimit($identifier, $limit, $window);
        $this->assertTrue($result['allowed']);
        $this->assertEquals($limit, $result['remaining']);

        // Increment 5000 times
        for ($i = 0; $i < 5000; $i++) {
            $this->rateLimiter->increment($identifier, $window);
        }

        $result = $this->rateLimiter->checkLimit($identifier, $limit, $window);
        $this->assertTrue($result['allowed']);
        $this->assertEquals(5000, $result['remaining']); // 10000 - 5000 = 5000

        // Increment to the limit
        for ($i = 0; $i < 5000; $i++) {
            $this->rateLimiter->increment($identifier, $window);
        }

        $result = $this->rateLimiter->checkLimit($identifier, $limit, $window);
        $this->assertFalse($result['allowed']);
        $this->assertEquals(0, $result['remaining']);
    }

    /**
     * Test that hour, day, and month limits work independently
     */
    public function testIndependentHourDayMonthLimits(): void
    {
        $baseIdentifier = '127.0.0.1';
        $hourIdentifier = $baseIdentifier . '_hour';
        $dayIdentifier = $baseIdentifier . '_day';
        $monthIdentifier = $baseIdentifier . '_month';

        $limit = 100;
        $hourWindow = 3600;
        $dayWindow = 86400;
        $monthWindow = 2592000;

        // Increment hour limit
        $this->rateLimiter->increment($hourIdentifier, $hourWindow);
        $this->rateLimiter->increment($hourIdentifier, $hourWindow);

        // Increment day limit
        $this->rateLimiter->increment($dayIdentifier, $dayWindow);
        $this->rateLimiter->increment($dayIdentifier, $dayWindow);
        $this->rateLimiter->increment($dayIdentifier, $dayWindow);

        // Increment month limit
        $this->rateLimiter->increment($monthIdentifier, $monthWindow);

        // Check each limit independently
        $hourResult = $this->rateLimiter->checkLimit($hourIdentifier, $limit, $hourWindow);
        $dayResult = $this->rateLimiter->checkLimit($dayIdentifier, $limit, $dayWindow);
        $monthResult = $this->rateLimiter->checkLimit($monthIdentifier, $limit, $monthWindow);

        $this->assertEquals(98, $hourResult['remaining']); // 100 - 2 = 98
        $this->assertEquals(97, $dayResult['remaining']); // 100 - 3 = 97
        $this->assertEquals(99, $monthResult['remaining']); // 100 - 1 = 99
    }

    /**
     * Test hour window expiration
     */
    public function testHourWindowExpiration(): void
    {
        $identifier = '127.0.0.1_hour';
        $limit = 10;
        $window = 2; // Short window for testing (normally 3600)

        // Increment to limit
        for ($i = 0; $i < $limit; $i++) {
            $this->rateLimiter->increment($identifier, $window);
        }

        $result = $this->rateLimiter->checkLimit($identifier, $limit, $window);
        $this->assertFalse($result['allowed']);

        // Wait for window to expire
        sleep($window + 1);

        // Should be reset after window expiration
        $result = $this->rateLimiter->checkLimit($identifier, $limit, $window);
        $this->assertTrue($result['allowed']);
        $this->assertEquals($limit, $result['remaining']);
    }

    /**
     * Test day window expiration
     */
    public function testDayWindowExpiration(): void
    {
        $identifier = '127.0.0.1_day';
        $limit = 10;
        $window = 2; // Short window for testing (normally 86400)

        // Increment to limit
        for ($i = 0; $i < $limit; $i++) {
            $this->rateLimiter->increment($identifier, $window);
        }

        $result = $this->rateLimiter->checkLimit($identifier, $limit, $window);
        $this->assertFalse($result['allowed']);

        // Wait for window to expire
        sleep($window + 1);

        // Should be reset after window expiration
        $result = $this->rateLimiter->checkLimit($identifier, $limit, $window);
        $this->assertTrue($result['allowed']);
        $this->assertEquals($limit, $result['remaining']);
    }

    /**
     * Test reset for hour identifier
     */
    public function testResetHourIdentifier(): void
    {
        $identifier = '127.0.0.1_hour';
        $limit = 100;
        $window = 3600;

        // Increment several times
        for ($i = 0; $i < 50; $i++) {
            $this->rateLimiter->increment($identifier, $window);
        }

        $result = $this->rateLimiter->checkLimit($identifier, $limit, $window);
        $this->assertEquals(50, $result['remaining']);

        // Reset
        $this->rateLimiter->reset($identifier);

        // Should be back to full limit
        $result = $this->rateLimiter->checkLimit($identifier, $limit, $window);
        $this->assertTrue($result['allowed']);
        $this->assertEquals($limit, $result['remaining']);
    }

    /**
     * Test reset for day identifier
     */
    public function testResetDayIdentifier(): void
    {
        $identifier = '127.0.0.1_day';
        $limit = 1000;
        $window = 86400;

        // Increment several times
        for ($i = 0; $i < 500; $i++) {
            $this->rateLimiter->increment($identifier, $window);
        }

        $result = $this->rateLimiter->checkLimit($identifier, $limit, $window);
        $this->assertEquals(500, $result['remaining']);

        // Reset
        $this->rateLimiter->reset($identifier);

        // Should be back to full limit
        $result = $this->rateLimiter->checkLimit($identifier, $limit, $window);
        $this->assertTrue($result['allowed']);
        $this->assertEquals($limit, $result['remaining']);
    }

    /**
     * Test concurrent hour and day limits with same base identifier
     */
    public function testConcurrentHourAndDayLimits(): void
    {
        $baseIdentifier = '192.168.1.100';
        $hourIdentifier = $baseIdentifier . '_hour';
        $dayIdentifier = $baseIdentifier . '_day';

        $hourLimit = 100;
        $dayLimit = 1000;
        $hourWindow = 3600;
        $dayWindow = 86400;

        // Simulate requests that increment both hour and day counters
        for ($i = 0; $i < 50; $i++) {
            $this->rateLimiter->increment($hourIdentifier, $hourWindow);
            $this->rateLimiter->increment($dayIdentifier, $dayWindow);
        }

        $hourResult = $this->rateLimiter->checkLimit($hourIdentifier, $hourLimit, $hourWindow);
        $dayResult = $this->rateLimiter->checkLimit($dayIdentifier, $dayLimit, $dayWindow);

        $this->assertEquals(50, $hourResult['remaining']); // 100 - 50 = 50
        $this->assertEquals(950, $dayResult['remaining']); // 1000 - 50 = 950
        $this->assertTrue($hourResult['allowed']);
        $this->assertTrue($dayResult['allowed']);
    }
}
