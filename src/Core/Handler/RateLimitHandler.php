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
     * Seconds per unit for rate limit window calculation
     */
    private const UNIT_SECONDS = [
        'minute' => 60,
        'minutes' => 60,
        'hour' => 3600,
        'hours' => 3600,
        'day' => 86400,
        'days' => 86400,
        'month' => 2592000,   // 30 days
        'months' => 2592000,
    ];

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
     * Convert (value, unit) to window size in seconds
     *
     * @param int $value
     * @param string $unit One of: minute, hour, day, month (singular or plural)
     * @return int Window in seconds
     */
    public static function windowSeconds(int $value, string $unit): int
    {
        $unit = strtolower(trim($unit));
        if (!isset(self::UNIT_SECONDS[$unit])) {
            throw new \InvalidArgumentException(
                sprintf('Invalid rate limit unit: %s. Use: minute, hour, day, month.', $unit)
            );
        }
        return $value * self::UNIT_SECONDS[$unit];
    }

    /**
     * Check rate limit for a request
     *
     * @param Request $request
     * @return array Rate limit information with headers (from first configured limit)
     * @throws RateLimitExceededException
     */
    public function checkRateLimit(Request $request): array
    {
        $identifier = $this->extractClientIp($request);

        $limits = $this->params->parameterByKey('rate_limit_limits', []);
        if (!is_array($limits) || count($limits) === 0) {
            throw new \RuntimeException('rate_limit_limits must be set and non-empty when rate limiting is enabled.');
        }

        $primaryResult = null;
        $primaryLimit = null;

        foreach ($limits as $entry) {
            $value = (int) ($entry['value'] ?? $entry['interval_value'] ?? 0);
            $unit = (string) ($entry['unit'] ?? $entry['interval_unit'] ?? 'minute');
            $requests = (int) ($entry['requests'] ?? 0);

            if ($value <= 0 || $requests <= 0) {
                continue;
            }

            $window = self::windowSeconds($value, $unit);
            $key = $identifier . '_' . $window;

            $result = $this->rateLimiter->checkLimit($key, $requests, $window);

            if ($primaryResult === null) {
                $primaryResult = $result;
                $primaryLimit = $requests;
            }

            if (!$result['allowed']) {
                $unitLabel = $value === 1 ? rtrim($unit, 's') : $unit;
                throw new RateLimitExceededException(
                    sprintf(
                        'Rate limit exceeded. Maximum %d requests per %d %s allowed.',
                        $requests, $value, $unitLabel
                    ),
                    $result['reset'],
                    $requests
                );
            }
        }

        if ($primaryResult === null || $primaryLimit === null) {
            throw new
                \RuntimeException('rate_limit_limits must contain at least one valid entry (value, unit, requests).');
        }

        // Increment all configured limits
        foreach ($limits as $entry) {
            $value = (int) ($entry['value'] ?? $entry['interval_value'] ?? 0);
            $unit = (string) ($entry['unit'] ?? $entry['interval_unit'] ?? 'minute');
            $requests = (int) ($entry['requests'] ?? 0);

            if ($value <= 0 || $requests <= 0) {
                continue;
            }

            $window = self::windowSeconds($value, $unit);
            $key = $identifier . '_' . $window;
            $this->rateLimiter->increment($key, $window);
        }

        $remaining = $primaryResult['remaining'];

        return [
            'X-RateLimit-Limit' => $primaryLimit,
            'X-RateLimit-Remaining' => max(0, $remaining - 1),
            'X-RateLimit-Reset' => $primaryResult['reset']
        ];
    }
}
