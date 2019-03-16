<?php

/*
 * Textpattern Installer for Composer
 * https://github.com/gocom/textpattern-installer
 *
 * Copyright (C) 2019 Jukka Svahn
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

use Composer\Package\PackageInterface;
use Composer\Repository\InstalledRepositoryInterface;
use Composer\Installer\LibraryInstaller;
use Textpattern\Composer\Installer\Textpattern\Find as Textpattern;

/**
 * Custom composer installer for Textpattern plugins.
 */
abstract class Base extends LibraryInstaller
{
    /**
     * Accepted type.
     *
     * @var string
     */
    protected $textpatternType;

    /**
     * The plugin packager.
     *
     * @var string
     */
    protected $textpatternPackager;

    /**
     * Supports 'textpattern-plugin' type.
     *
     * @param  string $packageType
     * @return bool
     */
    public function supports($packageType)
    {
        return $packageType === $this->textpatternType;
    }

    /**
     * Writes the plugin package to the database on install.
     *
     * @param InstalledRepositoryInterface $repo
     * @param PackageInterface             $package
     */
    public function install(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        new Textpattern();
        parent::install($repo, $package);
        $plugin = new $this->textpatternPackager($this->getInstallPath($package), $package);
        $plugin->install();
    }

    /**
     * Runs updater on package updates.
     *
     * @param InstalledRepositoryInterface $repo
     * @param PackageInterface             $initial
     * @param PackageInterface             $target
     */
    public function update(InstalledRepositoryInterface $repo, PackageInterface $initial, PackageInterface $target)
    {
        new Textpattern();
        parent::update($repo, $initial, $target);
        $plugin = new $this->textpatternPackager($this->getInstallPath($target), $package);
        $plugin->update();
    }

    /**
     * Removes the plugin from database when the package is uninstalled.
     *
     * @param InstalledRepositoryInterface $repo
     * @param PackageInterface             $package
     */
    public function uninstall(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        new Textpattern();
        $plugin = new $this->textpatternPackager($this->getInstallPath($package), $package);
        $plugin->uninstall();
        parent::uninstall($repo, $package);
    }
}
