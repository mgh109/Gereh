<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Security extends BaseConfig
{
    // Session-backed CSRF is appropriate for this same-origin, session-backed store.
    public string $csrfProtection = 'session';
    public bool $tokenRandomize = false;
    public string $tokenName = 'gereh_csrf_token';
    public string $headerName = 'X-CSRF-TOKEN';
    public string $cookieName = 'gereh_csrf';
    public int $expires = 7200;
    public bool $regenerate = true;

    // Keep API 403 failures explicit rather than redirecting JSON clients.
    public bool $redirect = false;
}
