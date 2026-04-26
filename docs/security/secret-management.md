# Secret Management

Secrets belong in `.env` or the deployment secret store. Do not commit or log plaintext secrets.

Required production secrets:

- `APP_KEY`
- `JWT_SECRET`
- `JWT_REFRESH_HASH_KEY`
- `PASSWORD_PEPPER`
- database credentials
- `CORS_ALLOWED_ORIGINS`

Production boot should fail closed when token, password, or CORS secrets are missing.
