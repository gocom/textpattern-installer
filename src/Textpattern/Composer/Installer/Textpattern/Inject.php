<?php
// phpcs:ignoreFile -- this is an injector file
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

use Textpattern\Composer\Installer\Textpattern\Find as Textpattern;
use Textpattern\Composer\Installer\Textpattern\ErrorHandler as Error;

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
     *
     * @var int
     */
    static public $admin_side_plugins = 1;
}

if (!Inject::$ready && new Textpattern() && Textpattern::$path) {
    // Allow all variables Textpattern creates in its global scope
    // go to the global namespace here.

    global $here, $txpcfg, $loader, $connected, $DB, $txpac, $txp_permissions, $txp_groups,
    $microstart, $txptrace, $txptracelevel, $txp_current_tag, $txp_user, $qcount, $qtime,
    $production_status, $prefs, $prefs_id, $sitename, $siteurl, $site_slogan, $language,
    $url_mode, $timeoffset, $comments_on_default, $comments_default_invite, $comments_mode,
    $comments_disabled_after, $use_textile, $ping_weblogsdotcom, $rss_how_many, $logging,
    $use_comments, $use_categories, $use_sections, $send_lastmod, $path_from_root, $lastmod,
    $comments_dateformat, $dateformat, $archive_dateformat, $comments_moderate, $img_dir,
    $comments_disallow_images, $comments_sendmail, $file_max_upload_size, $path_to_site,
    $timezone_key, $default_event, $auto_dst, $permlink_mode, $comments_are_ol, $is_dst,
    $locale, $tempdir, $file_base_path, $blog_uid, $blog_mail_uid, $blog_time_uid, $publisher_email,
    $allow_page_php_scripting, $allow_article_php_scripting, $default_section, $comments_use_fat_textile,
    $show_article_category_count, $show_comment_count_in_feed, $syndicate_body_or_excerpt,
    $include_email_atom, $comment_means_site_updated, $never_display_email, $comments_require_name,
    $comments_require_email, $articles_use_excerpts, $allow_form_override, $attach_titles_to_permalinks,
    $permalink_title_format, $expire_logs_after, $use_plugins, $custom_1_set, $custom_2_set,
    $custom_3_set, $custom_4_set, $custom_5_set, $custom_6_set, $custom_7_set, $custom_8_set,
    $custom_9_set, $custom_10_set, $ping_textpattern_com, $use_dns, $admin_side_plugins, $comment_nofollow,
    $use_mail_on_feeds_id, $max_url_len, $spam_blacklists, $override_emailcharset, $comments_auto_append,
    $dbupdatetime, $version, $doctype, $theme_name, $gmtoffset, $plugin_cache_dir, $textile_updated,
    $title_no_widow, $lastmod_keepalive, $enable_xmlrpc_server, $smtp_from, $publish_expired_articles,
    $searchable_article_fields, $textarray, $plugins, $plugins_ver, $app_mode, $s, $pretext, $plugin_callback,
    $is_article_list, $status, $id, $c, $context, $q, $m, $pg, $p, $month, $author, $request_uri, $qs,
    $subpath, $req, $page, $css, $pfr, $nolog, $has_article_tag, $txp_current_form, $parentid, $thisauthor,
    $thissection, $is_article_body, $stack_article, $thispage, $uPosted, $limit, $permlinks, $thiscategory,
    $thisarticle, $variable, $thislink, $theme, $event, $step, $trace;

    Inject::$ready = true;
    Inject::$cwd = getcwd();
    chdir(Textpattern::$path);
    define('txpinterface', 'admin');
    define('txpath', Textpattern::$path);

    if (file_exists('./config.php')) {
        require_once './config.php';
    }

    new Validate();

    foreach ([
        './lib/constants.php',
        './lib/txplib_misc.php',
        './lib/txplib_db.php',
        './lib/class.trace.php',
    ] as $file) {
        if (file_exists($file)) {
            require_once $file;
        }
    }

    if (class_exists('\Trace')) {
        $trace = new \Trace();
    }

    // Disable plugins during the updating and installing.
    // Loading plugins here would cause a FATAL error when invoking
    // plugin's updater callback, as the plugin would be evaluated
    // twice.

    Inject::$plugins = get_pref('use_plugins', 1, true);
    Inject::$admin_side_plugins = get_pref('admin_side_plugins', 1, true);
    set_pref('use_plugins', 0);
    set_pref('admin_side_plugins', 0);

    try {
        foreach ([
            './publish.php',
            './lib/txplib_head.php',
            './lib/txplib_theme.php',
            './lib/txplib_validator.php',
        ] as $file) {
            if (file_exists($file)) {
                require_once $file;
            }
        }

        error_reporting(0);
        set_error_handler([new Error, 'clean']);
        $theme = \theme::init();
        $event = '';
        $step = '';

        if (file_exists('./include/txp_plugin.php')) {
            require_once './include/txp_plugin.php';
        }
    } catch (Exception $e) {
    }

    // Reset plugin preferences to what they were before disabling.
    // Hopefully the installed plugin was written properly, and we
    // can get there and the process doesn't die on errors.

    set_pref('admin_side_plugins', Inject::$admin_side_plugins);
    set_pref('use_plugins', Inject::$plugins);
    $admin_side_plugins = $prefs['admin_side_plugins'] = $use_plugin = $prefs['use_plugins'] = 0;
    chdir(Inject::$cwd);
}
