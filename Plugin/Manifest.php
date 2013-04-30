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

namespace Rah\TextpatternPluginInstaller\Plugin;
use Rah\TextpatternPluginInstaller\Textpattern\Inject as Textpattern;

/**
 * Processes the manifest configuration.
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
     * @return bool
     */

    protected function find($directory)
    {
        if ($iterator = parent::find($directory))
        {
            new Textpattern();

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
        $plugin->md5 = md5($plugin->code);
        $plugin->flags = (int) $this->manifest->flags;
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

        return implode("\n", $out);
    }

    /**
     * Gets Textpacks.
     *
     * @return string
     */

    protected function textpack()
    {
        $textpacks = $this->dir . '/textpacks';

        if (!file_exists($textpacks) || !is_dir($textpacks) || !is_readable($textpacks))
        {
            return '';
        }

        if (($cwd = getcwd()) === false || !chdir($textpacks))
        {
            return '';
        }

        $out = array();

        foreach ((array) glob('*.textpack', GLOB_NOSORT) as $file)
        {
            if (!is_file($file) || !is_readable($file))
            {
                continue;
            }

            $file = file_get_contents($file);

            if (!preg_match('/^#language|\n#language\s/', $file))
            {
                array_unshift($out, $file);
                continue;
            }

            $out[] =  $file;
        }

        chdir($cwd);
        return implode("\n", $out);
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
                $file = $this->path($file);

                if (file_exists($file) && is_file($file) && is_readable($file))
                {
                    $out[] = file_get_contents($file);
                }
            }
        }
        else if (isset($this->manifest->help))
        {
            $out[] = $this->manifest->help;
        }

        return implode("\n", $out);
    }
}