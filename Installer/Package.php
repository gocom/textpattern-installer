<?php

namespace Rah\TextpatternPluginInstaller\Installer;

/**
 * Installer for the manifest format.
 */

class Package extends Base
{
    protected $textpatternType = 'textpattern-plugin-package';
    protected $textpatternPackager = 'Rah\TextpatternPluginInstaller\Plugin\Package';
}