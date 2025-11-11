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
     * @param string $message Error message
     * @param int $retryAfter Unix timestamp when the rate limit resets
     */
    public function __construct(string $message = 'Rate limit exceeded', int $retryAfter = null)
    {
        parent::__construct(429, $message);
        $this->retryAfter = $retryAfter;
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
}

