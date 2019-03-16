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

namespace Textpattern\Composer\Installer\Textpattern;

/**
 * Custom error handler.
 */
class ErrorHandler
{
    /**
     * Eats all error messages it can.
     *
     * @return bool TRUE
     */
    public function clean()
    {
        return true;
    }
}
