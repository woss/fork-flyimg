<?php

namespace Core\RateLimiter;

use Core\Entity\AppParameters;

/**
 * Factory to create rate limiter instances
 *
 * Class RateLimiterFactory
 * @package Core\RateLimiter
 */
class RateLimiterFactory
{
    /**
     * Create a rate limiter based on configuration
     *
     * @param AppParameters $params Application parameters
     * @return RateLimiterInterface
     */
    public static function create(AppParameters $params): RateLimiterInterface
    {
        $storageType = $params->parameterByKey('rate_limit_storage', 'file');

        switch ($storageType) {
            case 'memory':
                return new MemoryRateLimiter();
            case 'file':
            default:
                $storageDir = $params->parameterByKey('rate_limit_storage_dir');
                return new FileRateLimiter($storageDir);
        }
    }
}

