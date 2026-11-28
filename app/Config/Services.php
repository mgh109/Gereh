<?php

namespace Config;

class Services extends \CodeIgniter\Config\Services
{
    public static function session(?Session $config = null, bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('session', $config);
        }

        $request = static::get('request');
        if ($config === null && $request instanceof \CodeIgniter\HTTP\IncomingRequest
            && preg_match('#^(?:admin|api/v1/admin)(?:/|$)#i', trim($request->getPath(), '/'))
        ) {
            $config = clone config(Session::class);
            $config->cookieName = 'gereh_admin_session';
            $config->expiration = 30 * 86400;
            $config->savePath = WRITEPATH . 'admin-session';
        }

        return parent::session($config, false);
    }
}
