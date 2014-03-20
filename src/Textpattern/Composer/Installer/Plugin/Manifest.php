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

namespace Textpattern\Composer\Installer\Plugin;

use Textpattern\Composer\Installer\Textpattern\Inject as Textpattern;
use Textpattern\Composer\Installer\Textpattern\Find as FindTextpattern;

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
     * An array of manifest filenames.
     *
     * @var array
     */

    protected $manifestNames = array(
        'manifest.json',
    );

    /**
     * Pattern for validating the plugin name.
     *
     * @var string
     */

    protected $pluginNamePattern = '/^[a-z][a-z0-9]{2}_[a-z0-9\_]{1,64}$/i';

    /**
     * Find the plugin project directory and manifest from the package.
     *
     * @param  string      $directory
     * @return bool
     */

    protected function find($directory)
    {
        if ($iterator = parent::find($directory)) {
            new Textpattern();

            foreach ($iterator as $file) {
                if ($this->isManifest($file)) {
                    $this->dir = dirname($file);
                    $this->import();
                }
            }
        }

        return !empty($this->plugin);
    }

    /**
     * Whether a file is a valid plugin manifest.
     *
     * This function makes sure the file contains JSON, the name property
     * follows plugin naming convention and the parent directory is the same
     * as the name property.
     *
     * A valid plugin name follows the pattern {pfx}_{pluginName}, where the
     * {pfx} is the plugin author prefix and the {pluginName} is the name of
     * the plugin consisting of up to 64 ASCII letter and numbers.
     *
     * @param  string $file Full resolved pathname to the file
     * @return bool   TRUE if valid, FALSE otherwise
     */

    protected function isManifest($file)
    {
        if (in_array(basename($file), $this->manifestNames, true) && is_file($file) && is_readable($file)) {
            if ($contents = file_get_contents($file)) {
                $this->manifest = @json_decode($contents);

                if ($this->manifest && isset($this->manifest->name) && is_string($this->manifest->name)) {
                    if ($this->manifest->name === basename(dirname($file))) {
                        return (bool) preg_match($this->pluginNamePattern, $this->manifest->name);
                    }
                }
            }
        }

        return false;
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
     * .php files in the directory or the files
     * specified with the 'file' property of 'code'.
     *
     * @return string
     */

    protected function code()
    {
        $files = $out = array();

        if (isset($this->manifest->code->file)) {
            $files = array_map(array($this, 'path'), (array) $this->manifest->code->file);
        } else {
            if (($cwd = getcwd()) !== false && chdir($this->dir)) {
                $files = (array) glob('*.php');
            }
        }

        $pathFrom = (string) new FindTextpattern() . '/index.php';

        foreach ($files as $path) {
            if (file_exists($path) && is_file($path) && is_readable($path)) {
                $includePath = $this->getRelativePath($pathFrom, realpath($path));

                if ($includePath !== $path) {
                    $out[] = "include txpath.'/".addslashes($includePath)."';";
                } else {
                    $out[] = "include '".addslashes($includePath)."';";
                }
            }
        }

        if (isset($cwd) && $cwd !== false) {
            chdir($cwd);
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

        if (!file_exists($textpacks) || !is_dir($textpacks) || !is_readable($textpacks)) {
            return '';
        }

        if (($cwd = getcwd()) === false || !chdir($textpacks)) {
            return '';
        }

        $out = array();

        foreach ((array) glob('*.textpack', GLOB_NOSORT) as $file) {
            if (!is_file($file) || !is_readable($file)) {
                continue;
            }

            $file = file_get_contents($file);

            if (!preg_match('/^#@language|\n#@language\s/', $file)) {
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

        if (isset($this->manifest->help->file)) {
            foreach ((array) $this->manifest->help->file as $file) {
                $file = $this->path($file);

                if (file_exists($file) && is_file($file) && is_readable($file)) {
                    $out[] = file_get_contents($file);
                }
            }
        } elseif (isset($this->manifest->help)) {
            $out[] = (string) $this->manifest->help;
        }

        return implode("\n\n", $out);
    }
}
