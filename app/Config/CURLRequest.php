<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class CURLRequest extends BaseConfig
{
    /** @var list<int> */
    public array $shareConnectionOptions = [CURL_LOCK_DATA_CONNECT, CURL_LOCK_DATA_DNS];

    // Keep credentials and headers isolated between outgoing requests.
    public bool $shareOptions = false;
}
