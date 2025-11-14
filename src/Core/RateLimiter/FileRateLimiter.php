<?php

namespace Core\RateLimiter;

/**
 * File-based rate limiter implementation
 *
 * Class FileRateLimiter
 * @package Core\RateLimiter
 */
class FileRateLimiter implements RateLimiterInterface
{
    /**
     * @var string Directory to store rate limit files
     */
    private $storageDir;

    /**
     * @param string|null $storageDir Optional storage directory (defaults to var/tmp/ratelimit)
     */
    public function __construct(?string $storageDir = null)
    {
        if ($storageDir === null) {
            $storageDir = '/tmp/flyimg/ratelimit';
        }
        $this->storageDir = $storageDir;

        // Create storage directory if it doesn't exist
        if (!is_dir($this->storageDir)) {
            mkdir($this->storageDir, 0777, true);
        }
    }

    /**
     * Get the file path for a given identifier and window
     *
     * @param string $identifier
     * @param int $window
     * @return string
     */
    private function getFilePath(string $identifier, int $window): string
    {
        $windowStart = $this->getWindowStart(time(), $window);
        $key = sprintf('rate_limit_%s_%d_%d', md5($identifier), $window, $windowStart);
        return $this->storageDir . '/' . $key . '.txt';
    }

    /**
     * Calculate the start of the current window
     *
     * @param int $currentTime
     * @param int $window
     * @return int
     */
    private function getWindowStart(int $currentTime, int $window): int
    {
        return (int)floor($currentTime / $window) * $window;
    }

    /**
     * Read count from file
     *
     * @param string $filePath
     * @param int $window Window size in seconds (for expiration check)
     * @return int
     */
    private function readCount(string $filePath, int $window): int
    {
        if (!file_exists($filePath)) {
            return 0;
        }

        $content = file_get_contents($filePath);
        if ($content === false) {
            return 0;
        }

        // File format: windowStart|count
        $parts = explode('|', trim($content));
        if (count($parts) !== 2) {
            return 0;
        }

        $windowStart = (int)$parts[0];
        $count = (int)$parts[1];

        // Check if file is expired (older than window)
        $currentTime = time();
        if (($currentTime - $windowStart) >= $window) {
            // File expired, delete it
            @unlink($filePath);
            return 0;
        }

        return $count;
    }

    /**
     * Write count to file
     *
     * @param string $filePath
     * @param int $count
     * @param int $windowStart Window start timestamp
     * @return void
     */
    private function writeCount(string $filePath, int $count, int $windowStart): void
    {
        $content = $windowStart . '|' . $count;
        file_put_contents($filePath, $content, LOCK_EX);
    }

    /**
     * {@inheritdoc}
     */
    public function checkLimit(string $identifier, int $limit, int $window): array
    {
        $currentTime = time();
        $windowStart = $this->getWindowStart($currentTime, $window);
        $filePath = $this->getFilePath($identifier, $window);

        $count = $this->readCount($filePath, $window);

        if ($count === 0) {
            return [
                'allowed' => true,
                'remaining' => $limit,
                'reset' => $windowStart + $window
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
        $currentTime = time();
        $windowStart = $this->getWindowStart($currentTime, $window);
        $filePath = $this->getFilePath($identifier, $window);

        $count = $this->readCount($filePath, $window);
        $count++;
        $this->writeCount($filePath, $count, $windowStart);
    }

    /**
     * {@inheritdoc}
     */
    public function reset(string $identifier): void
    {
        $identifierHash = md5($identifier);
        $pattern = $this->storageDir . '/rate_limit_' . $identifierHash . '_*.txt';

        $files = glob($pattern);
        if ($files !== false) {
            foreach ($files as $file) {
                @unlink($file);
            }
        }
    }
}