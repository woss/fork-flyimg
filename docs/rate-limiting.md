# Rate Limiting

Flyimg includes a built-in rate limiting feature to protect your image processing service from abuse and excessive usage. 
Rate limiting can be configured to control the number of transformation requests per minute, hour, or day based on client IP addresses.

**Important:** Rate limiting only applies to **new image transformations** - requests that require actual image processing. Requests for already generated/cached images are not rate limited, allowing fast serving of cached content without consuming rate limit quota.

## Configuration

Rate limiting is disabled by default. To enable it, update your `config/parameters.yml` file:

```yaml
# Enable or disable rate limiting (default: false)
rate_limit_enabled: true

# Rate limit storage backend: 'file' or 'redis'
# - 'file': Stores rate limit data in files (persistent, suitable for single-instance, default)
# - 'redis': Stores rate limit data in Redis (persistent, suitable for multi-instance, recommended for production)
rate_limit_storage: file

# Redis configuration (only used when rate_limit_storage is 'redis')
# Optional: Redis connection configuration
# Uncomment and configure if using Redis storage
# rate_limit_redis:
#   host: '127.0.0.1'
#   port: 6379
#   scheme: 'tcp'
#   # Or use connection URL:
#   # url: 'redis://localhost:6379'

# Rate limit requests per minute (default: 100). Required if rate limiting is enabled.
rate_limit_requests_per_minute: 100

# Optional: Rate limit requests per hour (uncomment to enable)
# rate_limit_requests_per_hour: 1000

# Optional: Rate limit requests per day (uncomment to enable)
# rate_limit_requests_per_day: 10000

```

## Storage Backends

### Redis Storage (Recommended for Production)

The Redis storage backend stores rate limit data in Redis. This is the recommended option for production environments and is suitable for:

- Multi-instance deployments
- High-performance requirements
- Production environments where data persistence and scalability are important

**Pros:**
- Persistent across application restarts
- Works seamlessly with multiple application instances
- High performance with atomic operations
- Automatic expiration of rate limit data
- Scalable and battle-tested

**Cons:**
- Requires Redis server to be running
- Additional dependency (predis/predis)

**Configuration:**

```yaml
rate_limit_storage: redis

rate_limit_redis:
  host: '127.0.0.1'
  port: 6379
  scheme: 'tcp'
  # Or use connection URL:
  # url: 'redis://localhost:6379'
```

### File Storage (Default)

The file storage backend stores rate limit data in files on the filesystem. This is the default option and is suitable for:

- Single-instance deployments
- Development and testing
- Production environments where Redis is not available

**Pros:**
- Persistent across application restarts
- No additional dependencies
- Simple to set up and use
- Files are stored in `/tmp/flyimg/ratelimit/` directory

**Cons:**
- Slightly slower than Redis or memory storage
- Not suitable for multi-instance deployments (each instance tracks separately)
- Requires filesystem write permissions

## Rate Limit Configuration

**Note:** All rate limits apply only to new image transformations. Cached images are served without rate limiting.

### Requests Per Minute

The primary rate limit is set by `rate_limit_requests_per_minute`. This limits the number of new image transformations per minute per IP address. This is the most commonly used limit and is always enforced when rate limiting is enabled.

### Requests Per Hour

Optionally, you can set an hourly limit by uncommenting and setting `rate_limit_requests_per_hour`. This provides an additional safeguard against burst traffic of new transformations.

### Requests Per Day

Similarly, you can set a daily limit using `rate_limit_requests_per_day`. This helps protect against sustained abuse of image processing resources.

All three limits are checked independently. If any limit is exceeded, the new transformation request is blocked. However, requests for already cached images will still be served normally.

## IP Address Detection

Rate limiting uses IP addresses to identify clients. The system checks headers in the following order:

1. `X-Forwarded-For` header (for proxied requests) - uses the first IP in the chain
2. `X-Real-IP` header (alternative for proxied requests)
3. Client IP from the request

This ensures rate limiting works correctly behind reverse proxies, load balancers, and CDNs.

## Scope

Rate limiting applies only to **new image transformations** that require processing. Specifically:

- **Rate limited:** Requests that trigger new image generation (image doesn't exist in cache, or `refresh` option is used)
- **Not rate limited:** Requests for already generated/cached images (served directly from cache)

This design ensures that:
- Image processing resources are protected from abuse
- Cached images can be served quickly without rate limit restrictions
- Users can freely access previously generated images

Rate limiting applies globally to all transformation requests. There is no per-endpoint configuration.

## Rate Limit Responses

When a rate limit is exceeded, the service returns:

- **HTTP Status Code:** `429 Too Many Requests`
- **Response Body:** Error message with error details
- **Headers:**
  - `X-RateLimit-Limit`: Maximum requests allowed
  - `X-RateLimit-Remaining`: Remaining requests in current window
  - `X-RateLimit-Reset`: Unix timestamp when the limit resets
  - `Retry-After`: Seconds to wait before retrying

## Successful Request Headers

Even when requests are successful, the service includes rate limit headers in the response:

```
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 95
X-RateLimit-Reset: 1640995200
```

These headers help clients monitor their usage and avoid hitting limits.

## Rate Limit Strategy

The rate limiter uses a fixed window strategy. This means:

- Time windows are fixed (e.g., minute 1:00-1:59, minute 2:00-2:59)
- Counters reset at the start of each window
- Simple and efficient, especially for Redis and memory storage

## Troubleshooting

### Rate limiting not working

1. **Check if rate limiting is enabled:**
   ```yaml
   rate_limit_enabled: true
   ```

2. **Check Redis connection (if using Redis storage):**
   - Ensure Redis server is running
   - Verify Redis connection settings in `rate_limit_redis` configuration
   - Test Redis connection: `redis-cli ping` should return `PONG`

3. **Check logs:**
   - Rate limit exceeded events are logged (if log level is appropriate)
   - Check application logs for any errors

### Behind a proxy or load balancer

Rate limiting should work correctly behind proxies. Make sure:

- Your proxy forwards `X-Forwarded-For` or `X-Real-IP` headers
- The application can access these headers

If IP detection isn't working correctly, check the `X-Forwarded-For` header value in your logs.

## Best Practices

1. **Start with conservative limits:** Begin with lower limits and adjust based on your usage patterns

2. **Monitor rate limit headers:** Clients can use the rate limit headers to implement backoff strategies

3. **Use Redis storage for production:** In-memory storage is fine for single-instance deployments, but Redis storage is recommended for production and multi-instance deployments

4. **Set appropriate limits:** Balance between protecting your service and allowing legitimate traffic
   - Per-minute: For burst protection
   - Per-hour: For sustained traffic limits
   - Per-day: For abuse prevention

5. **Test rate limiting:** Enable rate limiting and test with various scenarios to ensure it behaves as expected

## Examples

### Basic Configuration (100 requests per minute)

```yaml
rate_limit_enabled: true
rate_limit_storage: file
rate_limit_requests_per_minute: 100
```

### Production Configuration with Redis

```yaml
rate_limit_enabled: true
rate_limit_storage: redis
rate_limit_redis:
  host: '127.0.0.1'
  port: 6379
rate_limit_requests_per_minute: 100
```

### Strict Rate Limiting (Multiple Limits)

```yaml
rate_limit_enabled: true
rate_limit_storage: redis
rate_limit_redis:
  host: '127.0.0.1'
  port: 6379
rate_limit_requests_per_minute: 60
rate_limit_requests_per_hour: 1000
rate_limit_requests_per_day: 10000
```

### High-Performance Single Instance

```yaml
rate_limit_enabled: true
rate_limit_storage: file
rate_limit_requests_per_minute: 500
```


