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

namespace Textpattern\Composer\Installer\Plugin;

use Composer\Package\PackageInterface;

/**
 * Process the manifest configuration.
 */
abstract class Base
{
    /**
     * Path to the plugin directory.
     *
     * @var string
     */
    protected $dir;

    /**
     * Stores an array of plugin contents.
     *
     * @var array
     */
    protected $plugin = [];

    /**
     * Stores an array of packaged plugin installers.
     *
     * @var array
     */
    protected $package = [];

    /**
     * Stores the composer package instance.
     *
     * @var PackageInterface
     */

    protected $composerPackage;

    /**
     * Constructor.
     */
    public function __construct($directory, PackageInterface $composerPackage = null)
    {
        $this->dir = $directory;
        $this->composerPackage = $composerPackage;

        if ($this->find($directory)) {
            $this->package();
        } else {
            throw new \InvalidArgumentException('No plugins found in the package.');
        }
    }

    /**
     * Lists the package contents.
     *
     * @param  string                         $directory
     * @return RecursiveIteratorIterator|bool
     */
    protected function find($directory)
    {
        if (!file_exists($directory) || !is_dir($directory) || !is_readable($directory)) {
            return false;
        }

        $iterator = new \RecursiveDirectoryIterator(realpath($directory));
        return new \RecursiveIteratorIterator($iterator);
    }

    /**
     * Packages the plugin data.
     */
    protected function package()
    {
        foreach ((array) $this->plugin as $plugin) {
            $this->package[] = base64_encode(gzencode(serialize((array) $plugin)));
        }
    }

    /**
     * Installs a plugin.
     */
    public function install()
    {
        $this->update();

        foreach ((array) $this->plugin as $plugin) {
            safe_update('txp_plugin', 'status = 1', "name = '".doSlash($plugin->name)."'");
        }
    }

    /**
     * Updates a plugin.
     */
    public function update()
    {
        foreach ((array) $this->package as $package) {
            $_POST['plugin64'] = $package;
            ob_start();
            plugin_install();
            ob_end_clean();
        }
    }

    /**
     * Uninstalls a plugin.
     */
    public function uninstall()
    {
        $_POST['selected'] = [];

        foreach ((array) $this->plugin as $plugin) {
            $_POST['selected'][] = $plugin->name;
        }

        $_POST['edit_method'] = 'delete';
        ob_start();
        plugin_multi_edit();
        ob_end_clean();
    }

    /**
     * Forms absolute file path.
     *
     * @param  string $path
     * @return string
     */
    protected function path($path)
    {
        if (strpos($path, './') === 0) {
            return $this->dir . '/' . substr($path, 2);
        }

        if (strpos($path, '../') === 0) {
            return dirname($this->dir) . '/' . substr($path, 3);
        }

        return $path;
    }

    /**
     * Gets a relative path to a file.
     *
     * @param  string $from The path from
     * @param  string $to   The path to
     * @return string
     */
    protected function getRelativePath($from, $to)
    {
        $from = explode('/', str_replace('\\', '/', $from));
        $to = explode('/', str_replace('\\', '/', $to));

        foreach ($from as $depth => $dir) {
            if (isset($to[$depth]) && $dir === $to[$depth]) {
                unset($to[$depth], $from[$depth]);
            }
        }

        for ($i = 0; $i < count($from) - 1; $i++) {
            array_unshift($to, '..');
        }

        return implode('/', $to);
    }
}
