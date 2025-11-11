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
     * @param string $storageDir Directory to store rate limit files
     */
    public function __construct(string $storageDir = null)
    {
        $this->storageDir = $storageDir ?: TMP_DIR . 'ratelimit/';
        if (!is_dir($this->storageDir)) {
            mkdir($this->storageDir, 0777, true);
        }
    }

    /**
     * Get the file path for a given identifier
     *
     * @param string $identifier
     * @return string
     */
    private function getFilePath(string $identifier): string
    {
        $safeIdentifier = preg_replace('/[^a-zA-Z0-9\-_]/', '_', $identifier);
        return $this->storageDir . md5($safeIdentifier) . '.json';
    }

    /**
     * Read rate limit data from file
     *
     * @param string $filePath
     * @return array
     */
    private function readData(string $filePath): array
    {
        if (!file_exists($filePath)) {
            return ['count' => 0, 'window_start' => time()];
        }

        $content = file_get_contents($filePath);
        $data = json_decode($content, true);

        if (!is_array($data)) {
            return ['count' => 0, 'window_start' => time()];
        }

        return $data;
    }

    /**
     * Write rate limit data to file with locking
     *
     * @param string $filePath
     * @param array $data
     * @return void
     */
    private function writeData(string $filePath, array $data): void
    {
        $fp = fopen($filePath, 'c+');
        if (!$fp) {
            return;
        }

        if (flock($fp, LOCK_EX)) {
            ftruncate($fp, 0);
            fwrite($fp, json_encode($data));
            fflush($fp);
            flock($fp, LOCK_UN);
        }

        fclose($fp);
    }

    /**
     * {@inheritdoc}
     */
    public function checkLimit(string $identifier, int $limit, int $window): array
    {
        $filePath = $this->getFilePath($identifier);
        $currentTime = time();

        $fp = fopen($filePath, 'c+');
        if (!$fp) {
            return [
                'allowed' => true,
                'remaining' => $limit,
                'reset' => $currentTime + $window
            ];
        }

        if (!flock($fp, LOCK_SH)) {
            fclose($fp);
            return [
                'allowed' => true,
                'remaining' => $limit,
                'reset' => $currentTime + $window
            ];
        }

        $data = $this->readData($filePath);
        $windowStart = $data['window_start'] ?? $currentTime;
        $count = $data['count'] ?? 0;

        // Check if window has expired
        if ($currentTime - $windowStart >= $window) {
            flock($fp, LOCK_UN);
            fclose($fp);
            return [
                'allowed' => true,
                'remaining' => $limit,
                'reset' => $currentTime + $window
            ];
        }

        $remaining = max(0, $limit - $count);
        $allowed = $count < $limit;
        $reset = $windowStart + $window;

        flock($fp, LOCK_UN);
        fclose($fp);

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
        $filePath = $this->getFilePath($identifier);
        $currentTime = time();

        $fp = fopen($filePath, 'c+');
        if (!$fp) {
            return;
        }

        if (!flock($fp, LOCK_EX)) {
            fclose($fp);
            return;
        }

        $data = $this->readData($filePath);
        $windowStart = $data['window_start'] ?? $currentTime;
        $count = $data['count'] ?? 0;

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

        ftruncate($fp, 0);
        fwrite($fp, json_encode($data));
        fflush($fp);
        flock($fp, LOCK_UN);
        fclose($fp);
    }

    /**
     * {@inheritdoc}
     */
    public function reset(string $identifier): void
    {
        $filePath = $this->getFilePath($identifier);
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
}
