<?php

namespace Rah\TextpatternPluginInstaller\Plugin;

/**
 * Process the manifest configuration.
 */

class Manifest extends Base
{
    /**
     * Stores manifest files contents.
     *
     * @var stdClass
     */

    protected $manifest;

    /**
     * Find the plugin project directory and manifest from the package.
     *
     * @param  string      $directory
     * @return string|bool The path to the manifest file, or FALSE
     */

    protected function find($directory)
    {
        if ($iterator = parent::find($directory))
        {
            foreach ($iterator as $file)
            {
                if (basename($file) === 'manifest.json' && is_file($file) && is_readable($file) && $contents = file_get_contents($file))
                {
                    if (($this->manifest = @json_decode($contents)) && isset($this->manifest->name) && $this->manifest->name === basename(dirname($file)))
                    {
                        $this->dir = dirname($file);
                        $this->import();
                    }
                }
            }
        }

        return !empty($this->plugin);
    }

    /**
     * Imports plugin manifest files to the database.
     */

    protected function import()
    {
        $plugin = (object) null;
        $plugin->name = $this->manifest->name;
        $plugin->version = $this->manifest->version;
        $plugin->author = $this->manifest->author;
        $plugin->author_uri = $this->manifest->author_uri;
        $plugin->description = $this->manifest->description;
        $plugin->type = $this->manifest->type;
        $plugin->order = (int) $this->manifest->order;
        $plugin->allow_html_help = 0;
        $plugin->code = $this->code();
        $plugin->md5 = md5($this->plugin->code);
        $plugin->flags = (int) $this->manifest->flags;
        $plugin->code = $this->code();
        $plugin->help = '';
        $plugin->help_raw = $this->help();
        $plugin->textpack = $this->textpack();
        $this->plugin[] = $plugin;
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
     * Gets help files.
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
}