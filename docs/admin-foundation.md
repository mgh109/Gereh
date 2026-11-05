# Admin foundation

The admin area is a shared, extensible back-office for GEREH.

## Initial scope
- Session-based administrator authentication
- Admin dashboard shell
- Product/catalog/inventory summary endpoint
- Admin user table with role and status fields

## Routes
- GET /admin/login
- POST /admin/login
- POST /admin/logout
- GET /admin
- GET /admin/api/summary

## Security notes
- Passwords are stored as password_hash() output and verified with password_verify().
- Login regenerates the session identifier.
- Protected routes use the adminauth filter.
- CSRF protection is enabled on the login form.
- No default administrator account or password is seeded. Create the first administrator through a controlled setup process before deployment.
- Role values are stored now; granular permission policies will be implemented before exposing staff management.

The dashboard is a starter shell. Navigation items are placeholders until their modules are implemented.
