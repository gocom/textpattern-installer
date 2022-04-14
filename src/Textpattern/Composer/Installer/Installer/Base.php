<?php

/*
 * Textpattern Installer for Composer
 * https://github.com/gocom/textpattern-installer
 *
 * Copyright (C) 2022 Jukka Svahn
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
use React\Promise\PromiseInterface;
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
     * Plugin packager.
     *
     * @var string
     */
    protected $textpatternPackager;

    /**
     * {@inheritdoc}
     */
    public function supports($packageType)
    {
        return $packageType === $this->textpatternType;
    }

    /**
     * {@inheritdoc}
     */
    public function install(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        new Textpattern();

        $then = function () use ($package) {
            $plugin = new $this->textpatternPackager($this->getInstallPath($package), $package);
            $plugin->install();
        };

        $result = parent::install($repo, $package);

        if ($result instanceof PromiseInterface) {
            return $result->then($then);
        }

        $then();
    }

    /**
     * {@inheritdoc}
     */
    public function update(InstalledRepositoryInterface $repo, PackageInterface $initial, PackageInterface $target)
    {
        new Textpattern();

        $then = function () use ($target) {
            $plugin = new $this->textpatternPackager($this->getInstallPath($target), $target);
            $plugin->update();
        };

        $result = parent::update($repo, $initial, $target);

        if ($result instanceof PromiseInterface) {
            return $result->then($then);
        }

        $then();
    }

    /**
     * {@inheritdoc}
     */
    public function uninstall(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        new Textpattern();

        $plugin = new $this->textpatternPackager($this->getInstallPath($package), $package);
        $plugin->uninstall();

        return parent::uninstall($repo, $package);
    }
}
