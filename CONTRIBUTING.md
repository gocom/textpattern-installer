Contributing
=====

Please take a quick look at this document before to make contribution process easier for all parties involved.

License
-----

[GNU General Public License Version 2](https://raw.github.com/gocom/textpattern-installer/blob/master/LICENSE). By contributing code, you agree to license your additions under the GPLv2 license.

Configure git
-----

For convenience your committer, git user, should be linked to your GitHub account:

```shell
$ git config --global user.name "John Doe"
$ git config --global user.email john.doe@example.com
```

Make sure to use an email address that is linked to your GitHub account. It can be a throwaway address or you can use GitHub's email protection features. We don't want your emails, but this is to make sure we know who did what. All commits nicely link to their author, instead of them coming from foobar@invalid.tld.

Development
-----

For list of available commands, run:

```shell
$ make help
```

Coding standard
-----

The project follows the [PSR-0](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md) and [PSR-2](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide-meta.md) standards. You can use PHP_CodeSniffer to make sure your additions follow them too by running:

```shell
$ make lint
```

Versioning
-----

[Semantic Versioning](http://semver.org/).
