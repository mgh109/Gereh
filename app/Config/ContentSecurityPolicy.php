<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class ContentSecurityPolicy extends BaseConfig
{
    public bool $reportOnly = false;
    public ?string $reportURI = null;
    public ?string $reportTo = null;
    public bool $upgradeInsecureRequests = false;
    public $defaultSrc = 'self';
    public $scriptSrc = 'self';
    public array|string $scriptSrcElem = 'self';
    public array|string $scriptSrcAttr = 'self';
    public $styleSrc = 'self';
    public array|string $styleSrcElem = 'self';
    public array|string $styleSrcAttr = 'self';
    public $imageSrc = 'self';
    public $baseURI = 'self';
    public $childSrc = 'self';
    public $connectSrc = 'self';
    public $fontSrc = 'self';
    public $formAction = 'self';
    public $frameAncestors = 'self';
    public $frameSrc = 'self';
    public $mediaSrc = 'self';
    public $objectSrc = 'none';
    public $manifestSrc = 'self';
    public array|string $workerSrc = [];
    public $pluginTypes = null;
    public $sandbox = null;
    public string $styleNonceTag = '{csp-style-nonce}';
    public string $scriptNonceTag = '{csp-script-nonce}';
    public bool $autoNonce = true;
}
