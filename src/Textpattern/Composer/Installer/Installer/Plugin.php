<?php

/*
 * Textpattern Installer for Composer
 * https://github.com/gocom/textpattern-installer
 *
 * Copyright (C) 2013 Jukka Svahn
 *
 * This file is part of Textpattern Installer.
 *
 * Textpattern Installer is free software; you can redistribute
 * it and/or modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation, version 2.
 *
 * Textpattern Installer is distributed in the hope that it
 * will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Textpattern Installer. If not, see
 * <http://www.gnu.org/licenses/>.
 */

namespace Textpattern\Composer\Installer\Installer;

use Composer\Composer;
use Composer\Installer\InstallerInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;

/**
 * Plugin.
 */
class Plugin implements PluginInterface
{
    /**
     * @var InstallerInterface[]
     */
    private $installers = [];

    /**
     * {@inheritdoc}
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        $this->installers = [
            new AdminTheme($io, $composer),
            new Manifest($io, $composer),
            new Package($io, $composer),
            new PublicTheme($io, $composer),
            new Textpack($io, $composer),
        ];

        foreach ($this->installers as $installer) {
            $composer->getInstallationManager()->addInstaller($installer);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deactivate(Composer $composer, IOInterface $io)
    {
        foreach ($this->installers as $installer) {
            $composer->getInstallationManager()->removeInstaller($installer);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function uninstall(Composer $composer, IOInterface $io)
    {
    }
}
