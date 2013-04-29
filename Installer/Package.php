<?php

namespace Rah\TextpatternPluginInstaller\Installer;

/**
 * Installer for compiled plugin package collection repositories.
 *
 * Installs all installer packages found in the composer package.
 */

class Package extends Base
{
    protected $textpatternType = 'textpattern-plugin-package';
    protected $textpatternPackager = 'Rah\TextpatternPluginInstaller\Plugin\Package';
}