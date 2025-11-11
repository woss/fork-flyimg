<?php

namespace Tests\Core\Handler;

use Core\Entity\AppParameters;
use Core\Exception\RateLimitExceededException;
use Core\Handler\RateLimitHandler;
use Core\RateLimiter\MemoryRateLimiter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Test rate limit handler
 *
 * Class RateLimitHandlerTest
 * @package Tests\Core\Handler
 */
class RateLimitHandlerTest extends TestCase
{
    /**
     * @var RateLimitHandler
     */
    private $handler;

    /**
     * @var AppParameters
     */
    private $params;

    /**
     * Setup test environment
     */
    protected function setUp(): void
    {
        $paramsArray = [
            'rate_limit_requests_per_minute' => 5,
            'rate_limit_requests_per_hour' => 100,
            'rate_limit_requests_per_day' => 1000,
            'rate_limit_by_ip' => true
        ];

        $this->params = $this->createMock(AppParameters::class);
        $this->params->method('parameterByKey')
            ->willReturnCallback(function ($key, $default = null) use ($paramsArray) {
                return $paramsArray[$key] ?? $default;
            });

        $rateLimiter = new MemoryRateLimiter();
        $this->handler = new RateLimitHandler($rateLimiter, $this->params);
    }

    /**
     * Test IP extraction from X-Forwarded-For header
     */
    public function testExtractClientIpFromForwardedFor(): void
    {
        $request = Request::create('/upload/test', 'GET');
        $request->headers->set('X-Forwarded-For', '192.168.1.1, 10.0.0.1');

        $ip = $this->handler->extractClientIp($request);

        $this->assertEquals('192.168.1.1', $ip);
    }

    /**
     * Test IP extraction from X-Real-IP header
     */
    public function testExtractClientIpFromRealIp(): void
    {
        $request = Request::create('/upload/test', 'GET');
        $request->headers->set('X-Real-IP', '192.168.1.1');

        $ip = $this->handler->extractClientIp($request);

        $this->assertEquals('192.168.1.1', $ip);
    }

    /**
     * Test IP extraction from client IP
     */
    public function testExtractClientIpFromClient(): void
    {
        $request = Request::create('/upload/test', 'GET');
        $request->server->set('REMOTE_ADDR', '192.168.1.1');

        $ip = $this->handler->extractClientIp($request);

        $this->assertEquals('192.168.1.1', $ip);
    }

    /**
     * Test rate limit check with successful request
     */
    public function testCheckRateLimitSuccess(): void
    {
        $request = Request::create('/upload/test', 'GET');
        $request->server->set('REMOTE_ADDR', '127.0.0.1');

        $result = $this->handler->checkRateLimit($request);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('X-RateLimit-Limit', $result);
        $this->assertArrayHasKey('X-RateLimit-Remaining', $result);
        $this->assertArrayHasKey('X-RateLimit-Reset', $result);
        $this->assertEquals(5, $result['X-RateLimit-Limit']);
        $this->assertGreaterThanOrEqual(3, $result['X-RateLimit-Remaining']); // Should be 4 after increment
    }

    /**
     * Test rate limit exceeded exception
     */
    public function testCheckRateLimitExceeded(): void
    {
        $request = Request::create('/upload/test', 'GET');
        $request->server->set('REMOTE_ADDR', '127.0.0.1');

        // Make requests up to the limit
        for ($i = 0; $i < 5; $i++) {
            try {
                $this->handler->checkRateLimit($request);
            } catch (RateLimitExceededException $e) {
                // Expected on the 6th request
                $this->assertEquals(429, $e->getStatusCode());
                $this->assertNotNull($e->getRetryAfter());
                return;
            }
        }

        // 6th request should exceed limit
        $this->expectException(RateLimitExceededException::class);
        $this->handler->checkRateLimit($request);
    }

    /**
     * Test rate limit headers generation
     */
    public function testGetRateLimitHeaders(): void
    {
        $request = Request::create('/upload/test', 'GET');
        $request->server->set('REMOTE_ADDR', '127.0.0.1');
        $headers = $this->handler->checkRateLimit($request);

        $this->assertArrayHasKey('X-RateLimit-Limit', $headers);
        $this->assertArrayHasKey('X-RateLimit-Remaining', $headers);
        $this->assertArrayHasKey('X-RateLimit-Reset', $headers);
    }
}
