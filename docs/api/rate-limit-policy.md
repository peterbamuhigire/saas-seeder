# API Rate Limit Policy

Rate limiting is a documented contract for API clients and will be enforced by a later runtime phase.

## Planned Defaults

| Scope | Limit | Window |
| --- | ---: | --- |
| Login by IP | 10 attempts | 1 minute |
| Login by account | 20 attempts | 15 minutes |
| Token refresh by user | 60 requests | 1 minute |
| Public register by IP | 5 attempts | 1 hour |
| General authenticated API | 100 requests | 1 minute |

## Headers

When enforcement is enabled, responses will include:

```http
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 99
X-RateLimit-Reset: 1777113600
```

Limit errors will return `429` with error code `RATE_LIMIT_EXCEEDED`.
