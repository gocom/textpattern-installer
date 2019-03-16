Textpattern Installer for Composer
=====

[Package directory](https://packagist.org/search/?q=textpattern) | [Issues](https://github.com/gocom/textpattern-installer/issues)

Install plugins and themes to [Textpattern CMS](https://textpattern.com) with [Composer](https://getcomposer.org/).

```shell
$ composer require rah/rah_replace
```

Overview
-----

Normally installing Textpattern plugins requires that you manually download an installation package, upload the package through your admin-panel, and then go through a multi-step installer process; rinse and repeat for every plugin, and when you need to update one.

With [Composer](https://getcomposer.org/), it's all managed through the dependency manager. Any theme or a plugin can be installed, updated or uninstalled, using a single command. And it comes with all other Composers benefits such as being able to commit your package manifesto under version control system and all of your teammates will have the same set of plugins synched without any extra fiddling.

Quick start for end-users
-----

After [installing Composer](https://getcomposer.org/doc/00-intro.md) to the host system your Textpattern installation is running on:

```shell
$ cd /path/to/your/textpattern/installation/root
$ composer require textpattern/lock:4.6.2
$ composer require rah/rah_replace rah/rah_flat
```

As in the above, to set up:

1. Navigate to your Textpattern installation location containing a `textpattern` directory and a `index.php` file.
2. Lock-in your installed Textpattern version using the [textpattern/lock](https://github.com/gocom/textpattern-lock) meta-package so that Composer knows your Textpattern version.
3. You can now install any plugins, themes and libraries like any other Composer package.

Quick start for developers
-----

Plugins and themes are just like any other normal Composer package, but with a special [type](https://getcomposer.org/doc/04-schema.md#type) and a matching installer requirement in your [composer.json](https://getcomposer.org/doc/04-schema.md). The package should be named after the plugin or the theme too. An example `composer.json` stub would look like the following:

```json
{
  "name": "vendor/pfx_pluginname",
  "type": "textpattern-plugin",
  "require": {
      "textpattern/installer" : "*"
  }
}
```

### Package types

| Type                   | Description |
|------------------------|-------------|
| `textpattern-plugin` | The package contains manifest.json formatted plugin sources. See [an example plugin](https://github.com/gocom/abc_plugin) |
| `textpattern-plugin-package`| The package contains collection of compiled plugin installer files. Any file that's name matches the format `pfx_pluginname_v0.1.0.txt` will be installed. |
| `textpattern-admin-theme` | The package is [an admin-side theme](https://docs.textpattern.com/themes/admin-side-themes). See [an example admin-theme](https://github.com/gocom/abc_theme).  |
| `textpattern-public-theme` | The package is [a front-end theme](https://docs.textpattern.com/themes/front-end-themes) |

Internals
-----

The installer works by scanning `composer.json` file's sibling and child directories for a Textpattern installation. If found, it injects the whole Textpattern application to the currently running Composer process. It then collects any plugins and themes from Composer packages and installs them, invoking plugin-lifecycle updaters and installers as needed. This process can be compatible with, and used by, any plugin or a theme.

The installer doesn't require any extra configuration from the end-user or the developer. All it needs is a functional Textpattern installation, located either in the same directory as the `composer.json` file or in a child directory. Just note that the system the composer command is ran at, needs to have access to the database; take this in mind if you are, for instance, running the composer command outside a virtualized container.

Requirements
-----

* [Textpattern CMS](https://textpattern.com/) 4.4.1, 4.5.7, 4.6.x, 4.7.x
* [PHP](https://secure.php.net/) >=5.5.38
* [PDO](https://secure.php.net/manual/en/book.pdo.php)
