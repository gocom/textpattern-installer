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

namespace Rah\TextpatternPluginInstaller\Installer;

/**
 * Installer for compiled plugin package collection repositories.
 *
 * Installs all installer packages found in the composer package.
 */

class Package extends Base
{
    protected $textpatternType = 'textpattern-plugin-package';
    protected $textpatternPackager = 'Rah\TextpatternPluginInstaller\Plugin\Package';
}