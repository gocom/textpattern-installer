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

class Validate
{
    /**
     * Installations config.
     *
     * @var array
     */

    protected $txpcfg = array();

    /**
     * Required configuration options.
     *
     * @var array
     */

    protected $required = array(
        'db',
        'user',
        'pass',
        'host',
        'table_prefix',
    );

    /**
     * Constructor.
     */

    public function __construct()
    {
        global $txpcfg;

        if (is_array($txpcfg)) {
            $this->txpcfg = $txpcfg;
        }

        $this->isValidConfig();
        $this->hasDatabase();
    }

    /**
     * Checks that the config contains all needed options.
     *
     * @throws \InvalidArgumentException
     */

    public function isValidConfig()
    {
        $missing = array();

        foreach ($this->required as $required) {
            if (!array_key_exists($required, $this->txpcfg)) {
                $missing[] = $required;
            }
        }

        if ($missing) {
            throw new \InvalidArgumentException('Textpattern installation missing config values: '.implode(', ', $missing));
        }
    }

    /**
     * Checks if the database exists.
     *
     * @throws \InvalidArgumentException
     */

    public function hasDatabase()
    {
        try {
            $pdo = new \PDO(
                'mysql:host='.$this->txpcfg['host'].';dbname='.$this->txpcfg['db'],
                $this->txpcfg['user'],
                $this->txpcfg['pass']
            );
        } catch (\PDOException $e) {
            $pdo = false;
        }

        if ($pdo === false) {
            throw new \InvalidArgumentException('Unable connect to Textpattern database: '.$this->txpcfg['db'].'@'.$this->txpcfg['host']);
        }

        if (!$pdo->prepare('SHOW TABLES LIKE ?')->execute(array($this->txpcfg['table_prefix'] . 'textpattern'))) {
            throw new \InvalidArgumentException('Textpattern is not installed');
        }
    }
}
