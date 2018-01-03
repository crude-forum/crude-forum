# CrudeForum

A very simple, crude and insecure web dicussion forum developed using PHP. Store data in text files in back-end.

Originally forked from [github.com/stupidsing/crude_forum](https://github.com/stupidsing/crude_forum) but changed drastically since.

## install

The installation depends on [composer] and [node-sass].

1. Install [composer] to your system.

2. Install vendor dependencies to your crude forum local folder:
   ```
   composer install
   ```
3. Install [npm] to your system. Then, with `npm`, install `node-sass` to anywhere in your `$PATH`. Probably with this command:
   ```
   npm install -g node-sass
   ```

4. Run this command to generate the stylesheet:
   ```
   composer run build
   ```
5. Visit the forum front page.

[composer]: https://getcomposer.org/download/
[node-sass]: https://www.npmjs.com/package/node-sass
[npm]: https://www.npmjs.com/package/npm

## Development

You can run crude forum with modern PHP 7.1+ command line tools:
```
php -S http://localhost:8080 -t ./public
```

If you want to continuously develop the CSS stylesheet, you may consider to use the watch mode of node-sass:
```
composer run --timeout=0 watch
```
