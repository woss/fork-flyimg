<?php

namespace Core\RateLimiter;

/**
 * Interface for rate limiting implementations
 *
 * Interface RateLimiterInterface
 * @package Core\RateLimiter
 */
interface RateLimiterInterface
{
    /**
     * Check if the request exceeds the rate limit
     *
     * @param string $identifier The identifier (e.g., IP address) to check
     * @param int $limit The maximum number of requests allowed
     * @param int $window The time window in seconds
     *
     * @return array Returns an array with:
     *               - 'allowed' (bool): Whether the request is allowed
     *               - 'remaining' (int): Number of remaining requests
     *               - 'reset' (int): Unix timestamp when the limit resets
     */
    public function checkLimit(string $identifier, int $limit, int $window): array;

    /**
     * Increment the request count for the given identifier
     *
     * @param string $identifier The identifier (e.g., IP address)
     * @param int $window The time window in seconds
     *
     * @return void
     */
    public function increment(string $identifier, int $window): void;

    /**
     * Reset the rate limit for the given identifier
     *
     * @param string $identifier The identifier (e.g., IP address)
     *
     * @return void
     */
    public function reset(string $identifier): void;
}
