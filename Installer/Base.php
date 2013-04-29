<?php

namespace Rah\TextpatternPluginInstaller\Installer;
use Composer\Package\PackageInterface;
use Composer\Repository\InstalledRepositoryInterface;
use Composer\Installer\LibraryInstaller;
use Rah\TextpatternPluginInstaller\Textpattern\Find as Textpattern;

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
        $plugin = new $textpatternPackager($this->getInstallPath($package));
        $textpatternPackager->install();
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
        $plugin = new $textpatternPackager($this->getInstallPath($target));
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
        parent::uninstall($repo, $package);
        $plugin = new $textpatternPackager($this->getInstallPath($package));
        $plugin->uninstall();
    }
}