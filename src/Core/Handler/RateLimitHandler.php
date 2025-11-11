<?php

namespace Core\Handler;

use Core\Entity\AppParameters;
use Core\Exception\RateLimitExceededException;
use Core\RateLimiter\RateLimiterInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Handler for rate limiting logic
 *
 * Class RateLimitHandler
 * @package Core\Handler
 */
class RateLimitHandler
{
    /**
     * @var RateLimiterInterface
     */
    private $rateLimiter;

    /**
     * @var AppParameters
     */
    private $params;

    /**
     * @param RateLimiterInterface $rateLimiter
     * @param AppParameters $params
     */
    public function __construct(RateLimiterInterface $rateLimiter, AppParameters $params)
    {
        $this->rateLimiter = $rateLimiter;
        $this->params = $params;
    }

    /**
     * Extract client IP from request
     *
     * @param Request $request
     * @return string
     */
    public function extractClientIp(Request $request): string
    {
        // Check X-Forwarded-For header (for proxied requests)
        $forwardedFor = $request->headers->get('X-Forwarded-For');
        if ($forwardedFor) {
            // Get the first IP in the chain
            $ips = explode(',', $forwardedFor);
            $ip = trim($ips[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }

        // Check X-Real-IP header
        $realIp = $request->headers->get('X-Real-IP');
        if ($realIp && filter_var($realIp, FILTER_VALIDATE_IP)) {
            return $realIp;
        }

        // Fall back to client IP
        return $request->getClientIp() ?: 'unknown';
    }

    /**
     * Check rate limit for a request
     *
     * @param Request $request
     * @return array Rate limit information with headers
     * @throws RateLimitExceededException
     */
    public function checkRateLimit(Request $request): array
    {
        $identifier = $this->extractClientIp($request);
        
        // Get limits from configuration
        $requestsPerMinute = $this->params->parameterByKey('rate_limit_requests_per_minute', 100);
        $requestsPerHour = $this->params->parameterByKey('rate_limit_requests_per_hour');
        $requestsPerDay = $this->params->parameterByKey('rate_limit_requests_per_day');

        // Check per-minute limit (primary limit)
        $result = $this->rateLimiter->checkLimit($identifier, $requestsPerMinute, 60);
        
        if (!$result['allowed']) {
            throw new RateLimitExceededException(
                sprintf('Rate limit exceeded. Maximum %d requests per minute allowed.', $requestsPerMinute),
                $result['reset']
            );
        }

        // Check per-hour limit if configured
        if ($requestsPerHour !== null) {
            $hourResult = $this->rateLimiter->checkLimit($identifier . '_hour', $requestsPerHour, 3600);
            if (!$hourResult['allowed']) {
                throw new RateLimitExceededException(
                    sprintf('Rate limit exceeded. Maximum %d requests per hour allowed.', $requestsPerHour),
                    $hourResult['reset']
                );
            }
        }

        // Check per-day limit if configured
        if ($requestsPerDay !== null) {
            $dayResult = $this->rateLimiter->checkLimit($identifier . '_day', $requestsPerDay, 86400);
            if (!$dayResult['allowed']) {
                throw new RateLimitExceededException(
                    sprintf('Rate limit exceeded. Maximum %d requests per day allowed.', $requestsPerDay),
                    $dayResult['reset']
                );
            }
        }

        // Get remaining count before incrementing (will be used in response)
        $remaining = $result['remaining'];

        // Increment counters
        $this->rateLimiter->increment($identifier, 60);
        if ($requestsPerHour !== null) {
            $this->rateLimiter->increment($identifier . '_hour', 3600);
        }
        if ($requestsPerDay !== null) {
            $this->rateLimiter->increment($identifier . '_day', 86400);
        }

        // Return rate limit headers (remaining - 1 since we just incremented)
        return [
            'X-RateLimit-Limit' => $requestsPerMinute,
            'X-RateLimit-Remaining' => max(0, $remaining - 1),
            'X-RateLimit-Reset' => $result['reset']
        ];
    }
}

