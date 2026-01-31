<?php

namespace Core\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class RateLimitExceededException extends HttpException
{
    /**
     * @var int Unix timestamp when the rate limit resets
     */
    private $retryAfter;

    /**
     * @var int|null Maximum requests allowed for the limit that was exceeded
     */
    private $limit;

    /**
     * @param string $message Error message
     * @param int|null $retryAfter Unix timestamp when the rate limit resets
     * @param int|null $limit Maximum requests allowed (for X-RateLimit-Limit header)
     */
    public function __construct(string $message = 'Rate limit exceeded', ?int $retryAfter = null, ?int $limit = null)
    {
        parent::__construct(429, $message);
        $this->retryAfter = $retryAfter;
        $this->limit = $limit;
    }

    /**
     * Get the retry-after timestamp
     *
     * @return int|null
     */
    public function getRetryAfter(): ?int
    {
        return $this->retryAfter;
    }

    /**
     * Get the maximum requests allowed for the limit that was exceeded
     *
     * @return int|null
     */
    public function getLimit(): ?int
    {
        return $this->limit;
    }
}
