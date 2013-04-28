<?php

namespace Rah\TextpatternPluginInstaller;
use Composer\Package\PackageInterface;
use Composer\Repository\InstalledRepositoryInterface;
use Composer\Installer\LibraryInstaller;
use Rah\TextpatternPluginInstaller\Plugin\Manifest as Plugin;
use Rah\TextpatternPluginInstaller\Textpattern\Find as Textpattern;

/**
 * Custom composer installer for Textpattern plugins.
 */

class Installer extends LibraryInstaller
{
    /**
     * Supports 'textpattern-plugin' type.
     *
     * @param  string $packageType
     * @return bool
     */

    public function supports($packageType)
    {
        return $packageType === 'textpattern-plugin';
    }

    /**
     * Writes the plugin package to the database on install.
     *
     * @param InstalledRepositoryInterface $repo
     * @param PackageInterface             $package
     */

    public function install(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        parent::install($repo, $package);
        new Textpattern();
        $plugin = new Plugin($this->getInstallPath($package));
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
        parent::update($repo, $initial, $target);
        new Textpattern();
        $plugin = new Plugin($this->getInstallPath($target));
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
        parent::uninstall($repo, $package);
        new Textpattern();
        $plugin = new Plugin($this->getInstallPath($package));
        $plugin->uninstall();
    }
}