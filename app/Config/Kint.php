<?php

namespace Config;

/**
 * Kint debug renderer settings used by CodeIgniter 4.7.
 * No debug output is enabled in production (CI_DEBUG is false there).
 */
class Kint
{
    public $plugins;
    public int $maxDepth = 6;
    public bool $displayCalledFrom = true;
    public bool $expanded = false;

    public string $richTheme = 'aante-light.css';
    public bool $richFolder = false;
    public $richObjectPlugins;
    public $richTabPlugins;

    public bool $cliColors = true;
    public bool $cliForceUTF8 = false;
    public bool $cliDetectWidth = true;
    public int $cliMinWidth = 40;
}
