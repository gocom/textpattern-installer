<?php

namespace Rah\TextpatternPluginInstaller\Plugin;
use Rah\TextpatternPluginInstaller\Textpattern\Find as Textpattern;

/**
 * Process the manifest configuration.
 */

class Manifest
{
    /**
     * Path to the plugin directory.
     *
     * @var string
     */
 
    protected $dir;

    /**
     * Stores manifest files contents.
     *
     * @var stdClass
     */

    protected $manifest;

    /**
     * Stores the plugin contents.
     *
     * @var Package
     */

    protected $plugin;

    /**
     * Package plugin installer.
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
            throw new \InvalidArgumentException('The manifest.json was not found.');
        }
    }

    /**
     * Find the plugin project directory and manifest from the package.
     *
     * @param  string      $directory
     * @return string|bool The path to the manifest file, or FALSE
     */

    protected function find($directory)
    {
        if (!file_exists($directory) || !is_dir($directory) || !is_readable($directory))
        {
            return false;
        }

        $iterator = new \RecursiveDirectoryIterator(realpath($directory));
        $iterator = new \RecursiveIteratorIterator($iterator);

        foreach ($iterator as $file)
        {
            if (basename($file) === 'manifest.json' && is_file($file) && is_readable($file) && $contents = file_get_contents($file))
            {
                if (($this->manifest = @json_decode($contents)) && isset($this->manifest->name) && $this->manifest->name === basename(dirname($file)))
                {
                    return $file;
                }
            }
        }

        return false;
    }

    /**
     * Uninstalls a plugin.
     */

    public function uninstall()
    {
        $_POST['selected'] = array($this->manifest->name);
        $_POST['edit_method'] = 'delete';
        ob_start();
        plugin_multi_edit();
        ob_end_clean();
    }

    /**
     * Imports plugin manifest files to the database.
     */

    protected function import()
    {
        $this->plugin = new Package();
        $this->plugin->name = $this->manifest->name;
        $this->plugin->version = $this->manifest->version;
        $this->plugin->author = $this->manifest->author;
        $this->plugin->author_uri = $this->manifest->author_uri;
        $this->plugin->description = $this->manifest->description;
        $this->plugin->type = $this->manifest->type;
        $this->plugin->order = (int) $this->manifest->order;
        $this->plugin->allow_html_help = 0;
        $this->plugin->code = $this->code();
        $this->plugin->md5 = md5($this->plugin->code);
        $this->plugin->flags = (int) $this->manifest->flags;
        $this->plugin->code = $this->code();
        $this->plugin->help = '';
        $this->plugin->help_raw = $this->help();
        $this->plugin->textpack = $this->textpack();
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
        $_POST['plugin64'] = $this->package;
        ob_start();
        plugin_install();
        ob_end_clean();
    }

    /**
     * Updates a plugin.
     */

    public function update()
    {
        $this->install();
    }

    /**
     * Plugin code template.
     *
     * Generates PHP source code that either imports
     * the first .php file in the directory or the files
     * specified with the 'file' property of 'code'.
     *
     * @return string
     */

    protected function code()
    {
        $files = $out = array();

        if (isset($this->manifest->code->file))
        {
            $files = array_map(array($this, 'path'), (array) $this->manifest->code->file);
        }
        else
        {
            $files = (array) glob($this->dir . '/*.php');
        }

        foreach ($files as $path)
        {
            if (file_exists($path) && is_file($path) && is_readable($path) && $contents = file_get_contents($path))
            {
                $out[] = preg_replace('/^<\?(php)?|\?>$/', '', $contents);
            }
        }

        return implode(n, $out);
    }

    /**
     * Gets Textpacks.
     *
     * @return string
     */

    protected function textpack()
    {
        if (!file_exists($this->dir . '/textpacks') || !is_dir($this->dir . '/textpacks'))
        {
            return '';
        }

        $out = array();

        foreach ((array) glob($this->dir . '/textpacks/*.textpack', GLOB_NOSORT) as $file)
        {
            $file = file_get_contents($file);

            if (!preg_match('/^#language|\n#language\s/', $file))
            {
                array_unshift($out, $file);
                continue;
            }

            $out[] =  $file;
        }

        return implode(n, $out);
    }

    /**
     * Processes help files.
     *
     * @return string
     */

    protected function help()
    {
        $out = array();

        if (isset($this->manifest->help->file))
        {
            foreach ((array) $this->manifest->help->file as $file)
            {
                $out[] = file_get_contents($this->path($file));
            }
        }
        else if (isset($this->manifest->help))
        {
            $out[] = $this->manifest->help;
        }

        return implode(n, $out);
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