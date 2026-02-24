# API Documentation - SaaS Seeder Template

## Base URL

```
http://localhost:8000/api/v1
```

In production:
```
https://yourdomain.com/api/v1
```

---

## Authentication

> **Password hashing:** All API auth endpoints use `PasswordHelper::verifyPassword()` / `hashPassword()`
> (Argon2ID + salt + pepper). Never use raw `password_verify()` or `password_hash()` in API code.

### 1. Login

Authenticate user and receive JWT token.

**Endpoint:** `POST /auth/login`

**Request:**
```json
{
  "username": "root",
  "password": "password",
  "remember_me": false
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "expires_at": "2026-02-02T12:00:00Z",
    "user": {
      "id": 1,
      "username": "root",
      "email": "peter@techguypeter.com",
      "user_type": "super_admin",
      "franchise_id": null,
      "full_name": "Peter Bamuhigire"
    }
  }
}
```

**Error Response (401):**
```json
{
  "success": false,
  "message": "Invalid credentials",
  "errors": {
    "code": "INVALID_PASSWORD"
  }
}
```

**Error Codes:**
- `USER_NOT_FOUND` - Username/email not found
- `INVALID_PASSWORD` - Password incorrect
- `ACCOUNT_INACTIVE` - Account is not active
- `ACCOUNT_LOCKED` - Too many failed attempts

---

### 2. Logout

Invalidate current session and token.

**Endpoint:** `POST /auth/logout`

**Headers:**
```
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

---

### 3. Logout All Sessions

Invalidate all active sessions for the user.

**Endpoint:** `POST /auth/logout-all`

**Headers:**
```
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "All sessions logged out successfully"
}
```

---

### 4. Refresh Token

Get a new JWT token before current one expires.

**Endpoint:** `POST /auth/refresh`

**Headers:**
```
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Token refreshed",
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "expires_at": "2026-02-02T13:00:00Z"
  }
}
```

---

## User Registration (Public)

### Register New User

Create a new user account (if self-registration is enabled).

**Endpoint:** `POST /public/auth/register`

**Request:**
```json
{
  "username": "johndoe",
  "email": "john@example.com",
  "password": "SecurePassword123",
  "password_confirmation": "SecurePassword123",
  "first_name": "John",
  "last_name": "Doe",
  "phone": "+1234567890"
}
```

**Success Response (201):**
```json
{
  "success": true,
  "message": "Registration successful. Please check your email to verify your account.",
  "data": {
    "user_id": 15,
    "username": "johndoe",
    "email": "john@example.com",
    "status": "pending"
  }
}
```

**Error Response (400):**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["Email already exists"],
    "password": ["Password must be at least 8 characters"]
  }
}
```

---

## Using the API

### Authorization Header

Include JWT token in all authenticated requests:

```
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
```

### Content Type

All requests should use JSON:

```
Content-Type: application/json
```

### Example cURL Request

```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "username": "root",
    "password": "password"
  }'
```

### Example JavaScript (Fetch)

```javascript
const response = await fetch('http://localhost:8000/api/v1/auth/login', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    username: 'root',
    password: 'password'
  })
});

const data = await response.json();

if (data.success) {
  // Store token
  localStorage.setItem('auth_token', data.data.token);
  console.log('Login successful:', data.data.user);
} else {
  console.error('Login failed:', data.message);
}
```

### Example Authenticated Request

```javascript
const token = localStorage.getItem('auth_token');

const response = await fetch('http://localhost:8000/api/v1/protected-endpoint', {
  method: 'GET',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${token}`
  }
});

const data = await response.json();
```

---

## Error Handling

### Standard Error Response Format

```json
{
  "success": false,
  "message": "Human-readable error message",
  "errors": {
    "field_name": ["Error detail 1", "Error detail 2"]
  }
}
```

### HTTP Status Codes

| Code | Meaning | When Used |
|------|---------|-----------|
| 200 | OK | Successful request |
| 201 | Created | Resource created successfully |
| 400 | Bad Request | Validation error or malformed request |
| 401 | Unauthorized | Authentication required or failed |
| 403 | Forbidden | User lacks permission |
| 404 | Not Found | Resource doesn't exist |
| 422 | Unprocessable Entity | Validation failed |
| 500 | Internal Server Error | Server-side error |

---

## Rate Limiting (To Be Implemented)

Future implementation will include:
- **Rate limit:** 100 requests per minute per IP
- **Headers:**
  - `X-RateLimit-Limit` - Max requests per window
  - `X-RateLimit-Remaining` - Requests remaining
  - `X-RateLimit-Reset` - Timestamp when limit resets

---

## CORS Configuration

The API includes CORS headers for cross-origin requests:

```php
Access-Control-Allow-Origin: *
Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS
Access-Control-Allow-Headers: Content-Type, Authorization
```

For production, update `api/bootstrap.php` to restrict origins:

```php
header('Access-Control-Allow-Origin: https://yourdomain.com');
```

---

## Testing the API

### Using Postman

1. **Create Collection:** "SaaS Seeder API"
2. **Add Login Request:**
   - Method: POST
   - URL: `http://localhost:8000/api/v1/auth/login`
   - Body (JSON):
     ```json
     {
       "username": "root",
       "password": "password"
     }
     ```
3. **Save Token:** Extract `data.token` from response
4. **Add to Collection Variable:** `auth_token`
5. **Use in Other Requests:**
   - Headers: `Authorization: Bearer {{auth_token}}`

### Using Thunder Client (VS Code)

1. Install Thunder Client extension
2. Create new request
3. Set URL and method
4. Add body/headers as needed
5. Save to collection for reuse

---

## Building Your Own API Endpoints

### Example: Get User Profile

**File:** `api/v1/users/me.php`

```php
<?php
require_once '../../bootstrap.php';

use App\Auth\Middleware\AuthMiddleware;
use App\Config\Database;

// Authenticate request
$authMiddleware = new AuthMiddleware((new Database())->getConnection());
$user = $authMiddleware->authenticate();

if (!$user) {
    errorResponse('Unauthorized', 401);
}

// Get user data
$db = (new Database())->getConnection();
$stmt = $db->prepare("
    SELECT id, username, email, first_name, last_name, user_type, franchise_id
    FROM tbl_users
    WHERE id = ?
");
$stmt->execute([$user['id']]);
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$userData) {
    errorResponse('User not found', 404);
}

// Return user data
jsonResponse(true, $userData, 'User profile retrieved');
```

**Usage:**
```bash
curl -X GET http://localhost:8000/api/v1/users/me \
  -H "Authorization: Bearer {token}"
```

---

## Permission Checking in API

```php
<?php
require_once '../../bootstrap.php';

use App\Auth\Middleware\{AuthMiddleware, PermissionMiddleware};
use App\Config\Database;

$db = (new Database())->getConnection();

// Authenticate
$authMiddleware = new AuthMiddleware($db);
$user = $authMiddleware->authenticate();

// Check permission
$permissionMiddleware = new PermissionMiddleware($db);
$permissionMiddleware->requirePermission($user['id'], $user['franchise_id'], 'INVOICE_CREATE');

// If we get here, user has permission
// ... proceed with endpoint logic
```

---

## WebSocket Support (Future)

Planned support for real-time features:
- Live notifications
- Multi-user collaboration
- Real-time data updates

---

## API Versioning

Current version: **v1**

Future versions will be accessible via:
- `api/v2/auth/login`
- `api/v3/auth/login`

Old versions will be deprecated with 6-month notice.

---

## Additional Resources

- **Postman Collection:** (To be created)
- **OpenAPI/Swagger Spec:** (To be created)
- **API Client Library:** (To be created)

---

**Last Updated:** 2026-02-01
**Version:** 1.0
