<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Feature extends BaseConfig
{
    public bool $autoRoutesImproved = true;
    public bool $oldFilterOrder = false;
    public bool $limitZeroAsAll = false;
    public bool $strictLocaleNegotiation = false;
}
