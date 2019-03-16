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
     * @var string[]
     */
    protected $manifestNames = [
        'manifest.json',
        'composer.json',
    ];

    /**
     * Help filenames.
     *
     * @var string[]
     */
    protected $helpNames = [
        './README.textile',
        './readme.textile',
    ];

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

            foreach ($this->manifestNames as $manifestName) {
                foreach ($iterator as $file) {
                    if (basename($file) === $manifestName && $this->isManifest($file)) {
                        $this->dir = dirname($file);
                        $this->import();
                    }
                }

                if (!empty($this->plugin)) {
                    return true;
                }
            }
        }

        return false;
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
        if (is_file($file) && is_readable($file)) {
            if ($contents = file_get_contents($file)) {
                $this->manifest = @json_decode($contents);

                if ($this->manifest && isset($this->manifest->name) && is_string($this->manifest->name)) {
                    $this->manifest->name = basename($this->manifest->name);

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
        $plugin->name = basename($this->composerPackage->getPrettyName());
        $plugin->version = substr($this->composerPackage->getVersion(), 0, 10);
        $plugin->author = '';
        $plugin->author_uri = '';
        $plugin->description = '';
        $plugin->type = 0;
        $plugin->order = 5;
        $plugin->flags = 0;
        $plugin->allow_html_help = 0;
        $plugin->help = '';

        if (!empty($this->manifest->extra->manifest)) {
            foreach ((array) get_object_vars($plugin) as $name => $value) {
                if (isset($this->manifest->extra->manifest->$name)) {
                    $this->manifest->$name = $this->manifest->extra->manifest->$name;
                }
            }
        }

        if (!empty($this->manifest->authors) && is_array($this->manifest->authors)) {
            if (isset($this->manifest->authors[0]->name)) {
                $plugin->author = $this->manifest->authors[0]->name;
            }

            if (isset($this->manifest->authors[0]->homepage)) {
                $plugin->author_uri = $this->manifest->authors[0]->homepage;
            }
        }

        if (isset($this->manifest->author)) {
            $plugin->author = $this->manifest->author;
        }

        if (isset($this->manifest->author_uri)) {
            $plugin->author_uri = $this->manifest->author_uri;
        }

        if (isset($this->manifest->homepage)) {
            $plugin->author_uri = $this->manifest->homepage;
        }

        if (isset($this->manifest->description)) {
            $plugin->description = $this->manifest->description;
        }

        if (isset($this->manifest->type)) {
            $plugin->type = (int) $this->manifest->type;
        }

        if (version_compare(get_pref('version'), '4.5.0') < 0 && $plugin->type === 5) {
            $plugin->type = 0;
        }

        if (isset($this->manifest->order)) {
            $plugin->order = (int) $this->manifest->order;
        }

        if (isset($this->manifest->flags)) {
            $plugin->flags = (int) $this->manifest->flags;
        }

        $plugin->code = $this->code();
        $plugin->md5 = md5($plugin->code);
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
        $files = $out = [];

        if (isset($this->manifest->code->file)) {
            $files = array_map([$this, 'path'], (array) $this->manifest->code->file);
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

        $out = [];

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
        $out = [];

        if (isset($this->manifest->help) && is_string($this->manifest->help)) {
            return (string) $this->manifest->help;
        }

        if (empty($this->manifest->file)) {
            $files = $this->helpNames;
        } else {
            $files = $this->manifest->help->file;
        }

        foreach ((array) $files as $file) {
            $file = $this->path($file);

            if (file_exists($file) && is_file($file) && is_readable($file)) {
                $out[] = file_get_contents($file);
            }
        }

        return implode("\n\n", $out);
    }
}
