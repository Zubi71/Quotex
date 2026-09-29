# OTC Signal Intelligence — Security Architecture & Hardening

## 1. Authentication & Token Lifecycle

- Authentication is powered by **Laravel Sanctum** using cryptographically secure SHA-256 bearer tokens.
- Tokens expire and can be revoked instantly upon logout or administrative session invalidation.
- Passwords are encrypted using **Argon2id / Bcrypt** with high computational work factors. Plaintext passwords are never stored.

---

## 2. Broker Credential Isolation

- **Absolute Zero-Client Exposure**: The frontend client (Next.js) **never** receives or handles broker session tokens, passwords, or trading credentials.
- All broker communication is mediated exclusively by backend PHP adapter classes.
- Sensitive environment secrets (such as `QUOTEX_SESSION_TOKEN`) are encrypted at rest using AES-256-CBC via Laravel's `Crypt` facade.

---

## 3. Rate Limiting & Abuse Prevention

Nginx and Laravel enforce strict multi-tier rate limiting:
- **Authentication Endpoints**: 10 requests per minute (`limit_req zone=auth burst=5 nodelay`).
- **Signal Generation**: 30 requests per minute (`limit_req zone=signals burst=3 nodelay`).
- **General API**: 60 requests per minute (`limit_req zone=api burst=20 nodelay`).

---

## 4. Input Sanitization & SQL Injection Prevention

- All SQL queries execute through **Eloquent ORM** and PDO parameterized prepared statements. Raw SQL string concatenation is forbidden across the codebase.
- User and API inputs are validated strictly against typed schemas using Laravel `FormRequest` and Zod on the web terminal.
- Strict typing (`declare(strict_types=1);` in PHP 8.3 and `strict: true` in TypeScript) prevents type coercion vulnerabilities.

---

## 5. HTTP Hardening & Security Headers

Nginx injects the following security headers on all incoming requests:
```nginx
add_header X-Frame-Options "SAMEORIGIN" always;
add_header X-Content-Type-Options "nosniff" always;
add_header X-XSS-Protection "1; mode=block" always;
add_header Referrer-Policy "strict-origin-when-cross-origin" always;
add_header Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; connect-src 'self' ws: wss:;" always;
add_header Permissions-Policy "geolocation=(), microphone=(), camera=()" always;
```

---

## 6. Audit Logging

Administrative parameter alterations (such as changing confidence thresholds, altering strategy weights, or modifying broker configurations) are permanently recorded in the `audit_log` table with user ID, IP address, user agent, old values, and new values.
