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

use Composer\Package\PackageInterface;
use Composer\Repository\InstalledRepositoryInterface;
use Composer\Installer\LibraryInstaller;
use Textpattern\Composer\Installer\Textpattern\Find as Textpattern;

/**
 * Installer for the public-side Textpattern themes.
 */

class PublicTheme extends LibraryInstaller
{
    /**
     * Supports 'textpattern-public-theme' packages.
     *
     * @param  string $packageType
     * @return bool
     */

    public function supports($packageType)
    {
        return $packageType === 'textpattern-public-theme';
    }

    /**
     * Points the package to the theme directory.
     *
     * @param  PackageInterface $package
     * @return string
     */

    public function getInstallPath(PackageInterface $package)
    {
        $textpattern = new Textpattern();
        $path = dirname($textpattern->getRelativePath());
        $themes = $path . '/themes';
        $legacy = $path . '/theme';

        // Textpattern <= 4.7.0 used theme directory.
        if (file_exists($legacy) && !file_exists($themes)) {
            $themes = $legacy;
        }

        return $themes . '/' . basename($package->getPrettyName());
    }
}
