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

namespace Textpattern\Composer\Installer\Installer;

/**
 * Installer for compiled plugin package collection repositories.
 *
 * Installs all installer packages found in the composer package.
 * Installers are detected by the standardized naming convention:
 * {pfx}_{pluginName}_v{version}[_zip].txt.
 *
 * Any file that matches the pattern will be considered as a plugin
 * package.
 */
class Package extends Base
{
    protected $textpatternType = 'textpattern-plugin-package';
    protected $textpatternPackager = 'Textpattern\Composer\Installer\Plugin\Package';
}
