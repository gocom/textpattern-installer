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
     * @var stdClass
     */

    protected $plugin;

    /**
     * Constructor.
     */

    public function __construct($directory)
    {
        if ($manifest = $this->find($directory))
        {
            $this->dir = dirname($manifest);
            $this->import();
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

    public function find($directory)
    {
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
        if ($flags & PLUGIN_LIFECYCLE_NOTIFY)
        {
            load_plugin($this->manifest->name, true);
            callback_event('plugin_lifecycle.'.$this->manifest->name, 'disabled');
            callback_event('plugin_lifecycle.'.$this->manifest->name, 'deleted');
        }

        safe_delete('txp_plugin', "name = '".doSlash($this->manifest->name)."'");
        safe_delete('txp_lang', "owner = '".doSlash($this->manifest->name)."'");
    }

    /**
     * Imports plugin manifest files to the database.
     */

    public function import()
    {
        $this->plugin->code = $this->template();
        $this->plugin->md5 = md5($this->plugin->code);
        $this->plugin->flags = (int) $this->manifest->flags;
        $this->plugin->code = $this->code();
        $this->plugin->help = $this->help();
        $this->plugin->textpack = $this->textpack();
    }

    /**
     * Updates a plugin.
     */

    public function update()
    {
        $r = safe_update(
            'txp_plugin',
            "author = '".doSlash($this->manifest->author)."',
            author_uri = '".doSlash($this->manifest->author_uri)."',
            version = '".doSlash($this->manifest->version)."',
            description = '".doSlash($this->manifest->description)."',
            help = '".doSlash($this->plugin->help)."',
            code = '".doSlash($this->plugin->code)."',
            code_restore = '".doSlash($this->plugin->code)."',
            code_md5 = '".doSlash($this->plugin->md5)."',
            type = '".doSlash($this->manifest->type)."',
            load_order = '".doSlash($this->manifest->order)."',
            flags = ".intval($this->manifest->flags),
            "name = '".doSlash($this->manifest->name)."'"
        );

        if ($r)
        {
            $this->post();
        }
    }

    /**
     * Installs a plugin.
     */

    public function install()
    {
        $r = safe_insert(
            'txp_plugin',
            "name = '".doSlash($this->manifest->name)."',
            status = 1,
            author = '".doSlash($this->manifest->author)."',
            author_uri = '".doSlash($this->manifest->author_uri)."',
            version = '".doSlash($this->manifest->version)."',
            description = '".doSlash($this->manifest->description)."',
            help = '".doSlash($this->plugin->help)."',
            code = '".doSlash($this->plugin->code)."',
            code_restore = '".doSlash($this->plugin->code)."',
            code_md5 = '".doSlash($this->plugin->md5)."',
            type = '".doSlash($this->manifest->type)."',
            load_order = '".doSlash($this->manifest->order)."',
            flags = ".intval($this->manifest->flags)
        );

        if ($r)
        {
            $this->post();
        }
    }

    /**
     * Runs post install process.
     */

    protected function post()
    {
        if ($this->plugin->textpack)
        {
            $textpack = '#@owner '.$this->manifest->name.n.$this->plugin->textpack;
            install_textpack($textpack, false);
        }

        if ($this->plugin->flags & PLUGIN_LIFECYCLE_NOTIFY)
        {
            load_plugin($this->manifest->name, true);
            callback_event('plugin_lifecycle.'.$this->manifest->name, 'installed');
            callback_event('plugin_lifecycle.'.$this->manifest->name, 'enabled');
        }
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

        if ($out)
        {
            $textile = new Textpattern_Textile_Parser();
            return $textile->TextileRestricted(implode(n, $out), 0, 0);
        }

        return '';
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