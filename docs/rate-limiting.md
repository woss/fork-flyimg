# Rate Limiting

Flyimg includes a built-in rate limiting feature to protect your image processing service from abuse and excessive usage.
You configure one or more **custom intervals**: each limit has a **value**, **unit** (minute, hour, day, or month), and **requests** (max requests in that window). Rate limiting is applied per client IP address.

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

# Rate limit intervals: list of { value, unit, requests }
# value: number; unit: minute|hour|day|month (singular or plural); requests: max requests in that window.
# At least one limit is required when rate limiting is enabled.
rate_limit_limits:
  - { value: 1, unit: minute, requests: 100 }
  - { value: 1, unit: hour, requests: 1000 }
  - { value: 30, unit: day, requests: 10000 }

```

## Custom rate limit intervals

Each entry in `rate_limit_limits` has:

- **value** – Size of the time window (integer, e.g. `1`, `30`).
- **unit** – Time unit: `minute`, `hour`, `day`, or `month` (singular or plural, e.g. `minute` or `minutes`).
- **requests** – Maximum number of new transformations allowed per client IP in that window.

Examples:

- `{ value: 1, unit: minute, requests: 100 }` – 100 requests per 1 minute.
- `{ value: 2, unit: hours, requests: 500 }` – 500 requests per 2 hours.
- `{ value: 30, unit: day, requests: 10000 }` – 10,000 requests per 30 days.
- `{ value: 1, unit: month, requests: 100000 }` – 100,000 requests per month (month = 30 days).

All configured limits are checked independently. If any limit is exceeded, the new transformation request is blocked. Requests for already cached images are still served normally.

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
  - `X-RateLimit-Limit`: Maximum requests allowed (for the limit that was exceeded)
  - `X-RateLimit-Remaining`: Remaining requests in current window
  - `X-RateLimit-Reset`: Unix timestamp when the limit resets
  - `Retry-After`: Seconds to wait before retrying

## Successful Request Headers

Even when requests are successful, the service includes rate limit headers in the response (from the first configured limit):

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

2. **Check that `rate_limit_limits` is set:** You must have at least one entry with `value`, `unit`, and `requests`.

3. **Check Redis connection (if using Redis storage):**
   - Ensure Redis server is running
   - Verify Redis connection settings in `rate_limit_redis` configuration
   - Test Redis connection: `redis-cli ping` should return `PONG`

4. **Check logs:**
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

4. **Set appropriate limits:** Balance between protecting your service and allowing legitimate traffic. Use custom intervals to match your needs, e.g.:
   - Short windows (e.g. 1 minute) for burst protection
   - Longer windows (e.g. 1 hour, 30 days) for sustained or monthly caps

5. **Test rate limiting:** Enable rate limiting and test with various scenarios to ensure it behaves as expected

## Examples

### Basic configuration (100 requests per minute)

```yaml
rate_limit_enabled: true
rate_limit_storage: file
rate_limit_limits:
  - { value: 1, unit: minute, requests: 100 }
```

### Scoping Redis rate limits by deployment (instance id)

When using Redis storage in multi-environment setups (e.g. staging, production, multiple clusters),
you typically want:

- All replicas of the **same** deployment to share rate limit counters.
- **Different** deployments/environments to use separate rate limit counters.

Flyimg supports this via an optional **instance-group identifier**:

```yaml
# Optional: scope Redis rate limit keys per deployment
# All replicas of the same deployment should share this value.
# Different environments should use different values.
rate_limit_instance_id: 'my-deployment-id'
```

Behavior:

- If `rate_limit_instance_id` is **not** set, Redis keys use the
  original (legacy) format, so existing deployments keep their behavior.
- If it **is** set, Redis rate limit keys are prefixed with this id, so different deployments
  no longer share rate limit counters while replicas of the same deployment still do.

### Production configuration with Redis

```yaml
rate_limit_enabled: true
rate_limit_storage: redis
rate_limit_redis:
  host: '127.0.0.1'
  port: 6379
rate_limit_limits:
  - { value: 1, unit: minute, requests: 100 }
```

### Multiple custom limits

```yaml
rate_limit_enabled: true
rate_limit_storage: redis
rate_limit_redis:
  host: '127.0.0.1'
  port: 6379
rate_limit_limits:
  - { value: 1, unit: minute, requests: 60 }
  - { value: 1, unit: hour, requests: 1000 }
  - { value: 30, unit: day, requests: 10000 }
  - { value: 1, unit: month, requests: 100000 }
```

### High-performance single instance

```yaml
rate_limit_enabled: true
rate_limit_storage: file
rate_limit_limits:
  - { value: 1, unit: minute, requests: 500 }
```
