<?php

/*
 * Textpattern Plugin Installer for Composer
 * https://github.com/gocom/textpattern-plugin-installer
 *
 * Copyright (C) 2013 Jukka Svahn
 *
 * This file is part of Textpattern Plugin Installer.
 *
 * Textpattern Plugin Installer is free software; you can redistribute
 * it and/or modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation, version 2.
 *
 * Textpattern Plugin Installer is distributed in the hope that it
 * will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Textpattern Plugin Installer. If not, see
 * <http://www.gnu.org/licenses/>.
 */

namespace Rah\TextpatternPluginInstaller\Installer;
use Composer\Package\PackageInterface;
use Composer\Repository\InstalledRepositoryInterface;
use Composer\Installer\LibraryInstaller;
use Rah\TextpatternPluginInstaller\Textpattern\Find as Textpattern;

/**
 * Installer for the admin-side Textpattern themes
 */

class AdminTheme extends LibraryInstaller
{
    /**
     * Supports 'textpattern-admin-side' theme.
     *
     * @param  string $packageType
     * @return bool
     */

    public function supports($packageType)
    {
        return $packageType === 'textpattern-admin-theme';
    }

    /**
     * Points the package to the theme directory.
     *
     * @param  PackageInterface $package
     * @return string
     */

    public function getInstallPath(PackageInterface $package)
    {
        $path = (string) new Textpattern();
        return basename($path) . '/theme/' . basename($package->getPrettyName());
    }
}