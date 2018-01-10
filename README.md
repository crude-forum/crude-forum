# CrudeForum [![Travis CI results][travis-badge]][travis]

A very simple, crude and insecure web dicussion forum developed using PHP. Store data in text files in back-end.

Originally forked from [github.com/stupidsing/crude_forum](https://github.com/stupidsing/crude_forum) but changed drastically since.

[travis]: https://travis-ci.org/crude-forum/crude-forum
[travis-badge]: https://api.travis-ci.org/crude-forum/crude-forum.svg?branch=master

## Prerequisites

The installation depends on [composer] and [node-sass].

1. Install [composer] to your system `$PATH`.

1. Install [npm] to your system. Then, with `npm`, install `node-sass` to anywhere in your `$PATH`. Probably with this command:

   ```shell
   npm install -g node-sass
   ```
[composer]: https://getcomposer.org/download/
[node-sass]: https://www.npmjs.com/package/node-sass
[npm]: https://www.npmjs.com/package/npm

## Basic Install

### Getting the project files

First, install CrudeForum into the folder `myForum` (which you may rename as you see fit):

```shell
composer create-project --prefer-dist crude-forum/crude-forum myForum
```

**Note:** If you do not have [node-sass] prior to running the composer `create-project` command,
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
composer run --timeout=0 dev
```

which effectively runs `php -S localhost:8080 -t ./public`.

If you want to continuously develop the CSS stylesheet, you may consider to use the watch mode of node-sass:

```shell
composer run --timeout=0 watch
```

# License

This software is licensed under [the MIT License](https://opensource.org/licenses/MIT).

You may get a copy of [the license](LICENSE.md) along with the software.