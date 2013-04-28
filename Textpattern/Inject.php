<?php

namespace Rah\TextpatternPluginInstaller\Textpattern;
use Rah\TextpatternPluginInstaller\Textpattern\Find as Textpattern;

/**
 * Injects Textpattern sources to the process.
 */

class Inject
{
    /**
     * Whether injection is ready.
     *
     * @var bool
     */

    static public $ready = false;

    /**
     * Working directory.
     *
     * @var string
     */

    static public $cwd = '';

    /**
     * Original plugin status.
     *
     * @var int
     */

    static public $plugins = 1;

    /**
     * Original admin-side plugin status.
     */

    static public $admin_side_plugins = 1;
}

if (!Inject::$ready && new Textpattern() && Textpattern::$path)
{
    global $txpcfg, $DB, $connected;

    Inject::$ready = true;
    Inject::$cwd = getcwd();
    chdir(Textpattern::$path);
    define('txpinterface', 'admin');

    require_once './config.php';
    require_once './lib/constants.php';
    require_once './lib/txplib_misc.php';
    require_once './lib/txplib_db.php';

    Inject::$plugins = get_pref('use_plugins', 1, true);
    Inject::$admin_side_plugins = get_pref('admin_side_plugins', 1, true);
    set_pref('use_plugins', 0);
    set_pref('admin_side_plugins', 0);

    require_once './publish.php';

    set_pref('admin_side_plugins', Inject::$admin_side_plugins);
    set_pref('use_plugins', Inject::$plugins);
    $admin_side_plugins = $prefs['admin_side_plugins'] = $use_plugin = $prefs['use_plugins'] = 0;
    chdir(Inject::$cwd);
}