# API Error Model

All API errors use a stable JSON envelope:

```json
{
  "success": false,
  "error": {
    "code": "REQUEST_MALFORMED_JSON",
    "message": "Invalid JSON body",
    "details": {},
    "documentation_url": "/docs/api/errors#REQUEST_MALFORMED_JSON"
  },
  "request_id": "8f3a4d7c1b6e4a2f9d0c3b5a7e8f9012"
}
```

## Standard Codes

| Code | HTTP | Meaning |
| --- | ---: | --- |
| `REQUEST_INVALID` | 400 | Request syntax or parameters are invalid. |
| `REQUEST_MALFORMED_JSON` | 400 | Body is not valid JSON or is not a JSON object. |
| `AUTH_UNAUTHORIZED` | 401 | Authentication is missing or failed. |
| `AUTH_INVALID_TOKEN` | 401 | Bearer token verification failed. |
| `AUTH_FORBIDDEN` | 403 | Authenticated caller is not allowed to perform the action. |
| `REQUEST_METHOD_NOT_ALLOWED` | 405 | HTTP method is not supported for the endpoint. |
| `RESOURCE_CONFLICT` | 409 | Request conflicts with current resource state. |
| `VALIDATION_FAILED` | 422 | Input failed semantic validation. |
| `AUTH_ACCOUNT_LOCKED` | 423 | Account is locked. |
| `INTERNAL_SERVER_ERROR` | 500 | Unexpected server error. |

`details` is always an object. For validation failures it should contain field keys with arrays of human-readable messages.
