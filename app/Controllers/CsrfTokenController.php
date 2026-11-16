<?php
namespace App\Controllers;
class CsrfTokenController extends BaseController
{
    public function show()
    {
        return $this->response->setHeader('Cache-Control','private, no-store, max-age=0')->setHeader('Vary','Cookie')->setHeader('X-Content-Type-Options','nosniff')->setJSON(['name'=>csrf_token(),'hash'=>csrf_hash()]);
    }
}
