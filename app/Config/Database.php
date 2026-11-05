<?php

namespace Config;

use CodeIgniter\Database\Config;

class Database extends Config
{
    public string $filesPath = APPPATH . 'Database' . DIRECTORY_SEPARATOR;

    public string $defaultGroup = 'default';

    public array $default = [
        'DSN'      => '',
        'hostname' => '',
        'username' => '',
        'password' => '',
        'database' => '',
        'DBDriver' => 'MySQLi',
        'DBPrefix' => '',
        'pConnect' => false,
        'DBDebug'  => false,
        'charset'  => 'utf8mb4',
        'DBCollat' => 'utf8mb4_unicode_ci',
        'swapPre'  => '',
        'encrypt'  => false,
        'compress' => false,
        'strictOn' => true,
        'failover' => [],
        'port'     => 3306,
        'numberNative' => false,
        'foundRows' => false,
        'dateFormat' => [
            'date'     => 'Y-m-d',
            'datetime' => 'Y-m-d H:i:s',
            'time'     => 'H:i:s',
        ],
    ];

    public function __construct()
    {
        parent::__construct();

        $this->default['hostname'] = env('database.default.hostname', '127.0.0.1');
        $this->default['username'] = env('database.default.username', '');
        $this->default['password'] = env('database.default.password', '');
        $this->default['database'] = env('database.default.database', 'gereh');
        $this->default['DBDriver'] = env('database.default.DBDriver', 'MySQLi');
        $this->default['DBPrefix'] = env('database.default.DBPrefix', '');
        $this->default['port'] = (int) env('database.default.port', 3306);
        $this->default['charset'] = env('database.default.charset', 'utf8mb4');
        $this->default['DBCollat'] = env('database.default.DBCollat', 'utf8mb4_unicode_ci');
        $this->default['DBDebug'] = ENVIRONMENT !== 'production';
    }
}
