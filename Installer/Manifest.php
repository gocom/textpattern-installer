<?php

namespace Rah\TextpatternPluginInstaller\Installer;

/**
 * Installer for the manifest format.
 */

class Manifest extends Base
{
    protected $textpatternType = 'textpattern-plugin';
    protected $textpatternPackager = 'Rah\TextpatternPluginInstaller\Plugin\Manifest';
}