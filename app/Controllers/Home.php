<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Exceptions\PageNotFoundException;

class Home extends BaseController
{
    public function index(): string|ResponseInterface
    {
        return view('home', [
            'title' => 'GEREH Store',
            'environment' => ENVIRONMENT,
        ]);
    }
}
