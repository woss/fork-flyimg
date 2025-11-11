# Rate Limiting

Flyimg includes a built-in rate limiting feature to protect your image processing service from abuse and excessive usage. 
Rate limiting can be configured to control the number of requests per minute, hour, or day based on client IP addresses.

## Configuration

Rate limiting is disabled by default. To enable it, update your `config/parameters.yml` file:

```yaml
# Enable or disable rate limiting (default: false)
rate_limit_enabled: true

# Rate limit storage backend: 'file' or 'memory'
# - 'file': Stores rate limit data in files (persistent, suitable for multi-instance)
# - 'memory': Stores rate limit data in memory (fast, lost on restart, single-instance only)
rate_limit_storage: file

# Optional: Custom directory for file-based rate limit storage
# If not set, defaults to var/tmp/ratelimit/
# rate_limit_storage_dir: ''

# Rate limit requests per minute (default: 100)
rate_limit_requests_per_minute: 100

# Optional: Rate limit requests per hour (uncomment to enable)
# rate_limit_requests_per_hour: 1000

# Optional: Rate limit requests per day (uncomment to enable)
# rate_limit_requests_per_day: 10000

# Enable IP-based rate limiting (default: true)
rate_limit_by_ip: true

# Rate limit strategy: 'fixed_window' or 'sliding_window'
# - 'fixed_window': Fixed time windows (simpler, recommended for file storage)
# - 'sliding_window': Sliding time windows (more accurate but more complex)
rate_limit_strategy: fixed_window

```

## Storage Backends

### File-based Storage (Default)

The file-based storage backend stores rate limit data in files under `var/tmp/ratelimit/`. This is the default option and is suitable for:

- Multi-instance deployments (when files are on a shared filesystem)
- Persistent rate limiting across restarts
- Production environments where data persistence is important

**Pros:**
- Persistent across application restarts
- Works with multiple application instances (if using shared storage)
- No additional dependencies

**Cons:**
- Slower than in-memory storage
- Requires filesystem access and permissions

### In-Memory Storage

The in-memory storage backend stores rate limit data in PHP memory. This is suitable for:

- Single-instance deployments
- High-performance requirements
- Development and testing

**Pros:**
- Fastest performance
- No filesystem I/O

**Cons:**
- Data is lost on application restart
- Not suitable for multi-instance deployments (each instance tracks separately)

## Rate Limit Configuration

### Requests Per Minute

The primary rate limit is set by `rate_limit_requests_per_minute`. This is the most commonly used limit and is always enforced when rate limiting is enabled.

### Requests Per Hour

Optionally, you can set an hourly limit by uncommenting and setting `rate_limit_requests_per_hour`. This provides an additional safeguard against burst traffic.

### Requests Per Day

Similarly, you can set a daily limit using `rate_limit_requests_per_day`. This helps protect against sustained abuse.

All three limits are checked independently. If any limit is exceeded, the request is blocked.

## IP Address Detection

Rate limiting uses IP addresses to identify clients. The system checks headers in the following order:

1. `X-Forwarded-For` header (for proxied requests) - uses the first IP in the chain
2. `X-Real-IP` header (alternative for proxied requests)
3. Client IP from the request

This ensures rate limiting works correctly behind reverse proxies, load balancers, and CDNs.

## Scope

Rate limiting applies globally to transformation requests. There is no per-endpoint configuration.

## Rate Limit Responses

When a rate limit is exceeded, the service returns:

- **HTTP Status Code:** `429 Too Many Requests`
- **Response Body:** JSON with error details
- **Headers:**
  - `X-RateLimit-Limit`: Maximum requests allowed
  - `X-RateLimit-Remaining`: Remaining requests in current window
  - `X-RateLimit-Reset`: Unix timestamp when the limit resets
  - `Retry-After`: Seconds to wait before retrying

Example response:

```json
{
  "error": "Rate limit exceeded",
  "message": "Rate limit exceeded. Maximum 100 requests per minute allowed."
}
```

## Successful Request Headers

Even when requests are successful, the service includes rate limit headers in the response:

```
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 95
X-RateLimit-Reset: 1640995200
```

These headers help clients monitor their usage and avoid hitting limits.

## Rate Limit Strategy

Currently, only `fixed_window` strategy is implemented. This means:

- Time windows are fixed (e.g., minute 1:00-1:59, minute 2:00-2:59)
- Counters reset at the start of each window
- Simpler and more efficient, especially for file-based storage

The `sliding_window` option is reserved for future implementation.

## Troubleshooting

### Rate limit files accumulating

Rate limit files are stored in `var/tmp/ratelimit/`. If you're using file-based storage and notice many files:

1. This is normal - each IP address gets its own file
2. Files are automatically cleaned up when windows expire
3. You can manually clean the directory if needed:
   ```bash
   rm -rf var/tmp/ratelimit/*
   ```

### Rate limiting not working

1. **Check if rate limiting is enabled:**
   ```yaml
   rate_limit_enabled: true
   ```

2. **Check storage directory permissions:**
   - For file-based storage, ensure `var/tmp/ratelimit/` is writable
   - The directory is created automatically if it doesn't exist

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

3. **Use file storage for production:** In-memory storage is fine for single-instance deployments, but file storage is more reliable for production

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

### Strict Rate Limiting (Multiple Limits)

```yaml
rate_limit_enabled: true
rate_limit_storage: file
rate_limit_requests_per_minute: 60
rate_limit_requests_per_hour: 1000
rate_limit_requests_per_day: 10000
```

### High-Performance Single Instance

```yaml
rate_limit_enabled: true
rate_limit_storage: memory
rate_limit_requests_per_minute: 500
```


