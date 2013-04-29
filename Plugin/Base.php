<?php

namespace Rah\TextpatternPluginInstaller\Plugin;

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
     * Stores the plugin contents.
     *
     * @var Package
     */

    protected $plugin;

    /**
     * Packaged plugin installer.
     *
     * @var string
     */

    protected $package;

    /**
     * Constructor.
     */

    public function __construct($directory)
    {
        if ($manifest = $this->find($directory))
        {
            $this->dir = dirname($manifest);
            $this->import();
            $this->package();
        }
        else
        {
            throw new \InvalidArgumentException('The plugin directory was not found.');
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
        if (!file_exists($directory) || !is_dir($directory) || !is_readable($directory))
        {
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
        $this->package = base64_encode(gzencode(serialize((array) $this->plugin)));
    }

    /**
     * Installs a plugin.
     */

    public function install()
    {
        $this->update();
        safe_update('txp_plugin', 'status = 1', "name = '".doSlash($this->plugin->name)."'");
    }

    /**
     * Updates a plugin.
     */

    public function update()
    {
        $_POST['plugin64'] = $this->package;
        ob_start();
        plugin_install();
        ob_end_clean();
    }

    /**
     * Uninstalls a plugin.
     */

    public function uninstall()
    {
        $_POST['selected'] = array($this->plugin->name);
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
        if (strpos($path, './') === 0)
        {
            return $this->dir . '/' . substr($path, 2);
        }

        if (strpos($path, '../') === 0)
        {
            return dirname($this->dir) . '/' . substr($path, 3);
        }

        return $path;
    }
}