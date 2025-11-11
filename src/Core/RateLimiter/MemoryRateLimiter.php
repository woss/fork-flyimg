<?php

namespace Core\RateLimiter;

/**
 * In-memory rate limiter implementation
 *
 * Class MemoryRateLimiter
 * @package Core\RateLimiter
 */
class MemoryRateLimiter implements RateLimiterInterface
{
    /**
     * @var array Storage for rate limit data
     */
    private $storage = [];

    /**
     * Clean up expired entries periodically
     *
     * @var int Last cleanup time
     */
    private $lastCleanup = 0;

    /**
     * Cleanup interval in seconds (clean up every 60 seconds)
     */
    private const CLEANUP_INTERVAL = 60;

    /**
     * {@inheritdoc}
     */
    public function checkLimit(string $identifier, int $limit, int $window): array
    {
        $this->cleanup();
        $currentTime = time();

        if (!isset($this->storage[$identifier])) {
            return [
                'allowed' => true,
                'remaining' => $limit,
                'reset' => $currentTime + $window
            ];
        }

        $data = $this->storage[$identifier];
        $windowStart = $data['window_start'];
        $count = $data['count'];

        // Check if window has expired
        if ($currentTime - $windowStart >= $window) {
            unset($this->storage[$identifier]);
            return [
                'allowed' => true,
                'remaining' => $limit,
                'reset' => $currentTime + $window
            ];
        }

        $remaining = max(0, $limit - $count);
        $allowed = $count < $limit;
        $reset = $windowStart + $window;

        return [
            'allowed' => $allowed,
            'remaining' => $remaining,
            'reset' => $reset
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function increment(string $identifier, int $window): void
    {
        $this->cleanup();
        $currentTime = time();

        if (!isset($this->storage[$identifier])) {
            $this->storage[$identifier] = [
                'count' => 1,
                'window_start' => $currentTime
            ];
            return;
        }

        $data = &$this->storage[$identifier];
        $windowStart = $data['window_start'];
        $count = $data['count'];

        // Reset window if expired
        if ($currentTime - $windowStart >= $window) {
            $windowStart = $currentTime;
            $count = 0;
        }

        $count++;
        $data = [
            'count' => $count,
            'window_start' => $windowStart
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function reset(string $identifier): void
    {
        unset($this->storage[$identifier]);
    }

    /**
     * Clean up expired entries to prevent memory leaks
     *
     * @return void
     */
    private function cleanup(): void
    {
        $currentTime = time();

        // Only cleanup every CLEANUP_INTERVAL seconds
        if ($currentTime - $this->lastCleanup < self::CLEANUP_INTERVAL) {
            return;
        }

        $this->lastCleanup = $currentTime;

        foreach ($this->storage as $identifier => $data) {
            // Remove entries older than 24 hours (longest reasonable window)
            if ($currentTime - $data['window_start'] > 86400) {
                unset($this->storage[$identifier]);
            }
        }
    }
}

