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

namespace Textpattern\Composer\Installer\Textpattern;

/**
 * Finds closest Textpattern installation location.
 */

class Find
{
    /**
     * Path to Textpattern installation.
     *
     * @var string|bool
     */

    static public $path = false;

    /**
     * Candidates for the installation location.
     *
     * @var array
     */

    protected $candidates = array('./');

    /**
     * Constructor.
     */

    public function __construct()
    {
        if (self::$path === false)
        {
            foreach ($this->candidates as $candidate)
            {
                if (self::$path = $this->find($candidate))
                {
                    break;
                }
            }

            if (!self::$path)
            {
                throw new \InvalidArgumentException('Textpattern installation location was not found.');
            }
        }
    }

    /**
     * Finds the closest Textpattern installation path.
     *
     * @param  string      The directory
     * @return string|bool The path, or FALSE
     */
 
    public function find($directory)
    {
        if (!file_exists($directory) || !is_dir($directory) || !is_readable($directory))
        {
            return false;
        }

        $iterator = new \RecursiveDirectoryIterator(realpath($directory));
        $iterator = new \RecursiveIteratorIterator($iterator);

        foreach ($iterator as $file)
        {
        	if (basename($file) === 'config.php' && is_file($file) && is_readable($file) && $contents = file_get_contents($file))
            {
                if (strpos($contents, '$txpcfg') !== false && file_exists(dirname($file) . '/publish.php'))
                {
                    return dirname($file);
                }
            }
        }

        return false;
    }

    /**
     * Gives out the path.
     *
     * @return string The path or an empty string
     */

    public function __toString()
    {
        return (string) self::$path;
    }
}