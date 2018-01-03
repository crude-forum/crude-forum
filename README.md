# CrudeForum

A very simple, crude and insecure web dicussion forum developed using PHP. Store data in text files in back-end.

Originally forked from [github.com/stupidsing/crude_forum](https://github.com/stupidsing/crude_forum) but changed drastically since.

# Prerequisites

The installation depends on [composer] and [node-sass].

1. Install [composer] to your system `$PATH`.

2. Install [npm] to your system. Then, with `npm`, install `node-sass` to anywhere in your `$PATH`. Probably with this command:
   ```
   npm install -g node-sass
   ```
[composer]: https://getcomposer.org/download/
[node-sass]: https://www.npmjs.com/package/node-sass
[npm]: https://www.npmjs.com/package/npm


## Basic Installation

First, install CrudeForum into the folder `myForum` (which you may rename as you see fit):
```
composer -vvv create-project --prefer-dist crude-forum/crude-forum myForum dev-master
```

Then setup your web server to use document of the full path to `myForum/public`.

Should all the setup correct, you can now browse your forum in browser.

**Note:** If you do not have [node-sass] prior to running the composer `create-project` command,
you'd need to run the composer build command to rebuild CSS stylesheet after install:
```
composer run build
```

## Development

Simply clone this repository. You can run crude forum with modern PHP 7.1+ command line tools:
```
composer run --timeout=0 dev
```

which effectively runs `php -S localhost:8080 -t ./public`.

If you want to continuously develop the CSS stylesheet, you may consider to use the watch mode of node-sass:
```
composer run --timeout=0 watch
```
