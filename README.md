# CrudeForum
[![CI results][ci-badge]][ci-url] [![Stable Version][packagist-stable-badge]][packagist] [![Downloads][packagist-total-badge]][packagist] [![License][license-badge]](LICENSE.md)

A very simple, crude and insecure web dicussion forum developed using PHP. Store data in text files in back-end.

Originally forked from [github.com/stupidsing/crude_forum](https://github.com/stupidsing/crude_forum) but changed drastically since.

[ci-url]: https://github.com/crude-forum/crude-forum/actions?query=branch%3Amain
[ci-badge]: https://github.com/crude-forum/crude-forum/actions/workflows/main.yml/badge.svg?branch=main
[packagist]: https://packagist.org/packages/crude-forum/crude-forum
[packagist-stable-badge]: https://poser.pugx.org/crude-forum/crude-forum/v/stable
[packagist-total-badge]: https://poser.pugx.org/crude-forum/crude-forum/downloads
[license-badge]: https://poser.pugx.org/crude-forum/crude-forum/license

## Prerequisites

The installation depends on [composer]. Install [composer] to your system `$PATH`.

[composer]: https://getcomposer.org/download/

## Development

All style changes should be done in forum.scss and built by [sass]. To setup for development:

1. Install [nodejs] to your system. The package manager [npm] will be installed along.
1. Then with `npm`, install `sass` to anywhere in your `$PATH`. Probably with this command:

```shell
npm install -g sass
```

[nodejs]: https://nodejs.org/
[npm]: https://www.npmjs.com/package/npm
[sass]: https://www.npmjs.com/package/sass

## Basic Install

### Getting the project files

First, install CrudeForum into the folder `myForum` (which you may rename as you see fit):

```shell
composer create-project --prefer-dist crude-forum/crude-forum myForum
```

**Note:** If you do not have [sass] prior to running the composer `create-project` command,
you'd need to run the composer build command to rebuild static assets after install:

```shell
composer run build
```

### Web server configurations

Then setup your web server to use document of the full path to `myForum/public`. Please remember
to setup your server to route to `myForum/public/index.php` by default.

<details><summary>Nginx installation</summary><p>

For [Nginx][nginx], assuming you have `$document_root` points to `myForum/public`, this means
to have something like this in your config:

```nginx
location /  {
    ...
    fastcgi_param   SCRIPT_FILENAME  $document_root/index.php;
    ...
}
```

</p>
</details>

<details><summary>Apache installation</summary><p>

For [Apache][apache], please remember to setup [AllowOverride all][AllowOverride] in the appropriate
[Directory] section so the [.htaccess](public/.htaccess) file can work for you. Probably something
like this:

```apache
<VirtualHost "my-forum.com">
    DocumentRoot "/home/to/myForum/public"
    <Directory "/home/to/myForum/public">
        AllowOverride all
    </Directory>
</VirtualHost>
```

</p>
</details>


[nginx]: https://nginx.org/en/
[apache]: https://httpd.apache.org/
[Directory]: https://httpd.apache.org/docs/2.4/mod/core.html#directory
[AllowOverride]: https://httpd.apache.org/docs/2.4/mod/core.html#allowoverride

Should all the setup correct, you can now browse your forum in browser.

## Development

Simply clone this repository. You can run crude forum with modern PHP 7.1+ command line tools:

```shell
composer dev
```

which effectively runs `php -S localhost:8080 -t ./public`.

If you want to continuously develop the CSS stylesheet, you may consider to use the watch mode of node-sass:

```shell
composer watch
```

Both `watch` and `build` requires [node-sass]. You may supply additional argument by using the `--` syntax.
For example, to watch and build asset with embeded source map:

```shell
composer watch -- --source-map-embed
```

For detail descriptions for the composer scripts available, use the command:

```shell
composer list
```

# License

This software is licensed under [the MIT License](https://opensource.org/licenses/MIT).

You may get a copy of [the license](LICENSE.md) along with the software.
