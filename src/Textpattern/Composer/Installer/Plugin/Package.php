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

/**
 * Processes packaged installer collections.
 */

class Package extends Base
{
    /**
     * Finds plugin packages.
     *
     * @param  string $directory
     * @return bool
     */

    protected function find($directory)
    {
        if ($iterator = parent::find($directory)) {
            foreach ($iterator as $file) {
                if (preg_match('/^[a-z0-9]{3}_[a-z0-9\_]{0,64}_v[a-z0-9\-\.]+(_zip)?\.txt$/i', basename($file)) && is_file($file) && is_readable($file) && $contents = file_get_contents($file)) {
                    $plugin = (object) null;
                    $plugin->name = implode('_v', array_slice(explode('_v', basename($file, '.txt')), 0, -1));
                    $this->plugin[] = $plugin;
                    $this->package[] = $contents;
                }
            }
        }

        return !empty($this->plugin);
    }

    /**
     * Skip packager, just inject Textpattern.
     */

    protected function package()
    {
        new Textpattern();
    }
}
