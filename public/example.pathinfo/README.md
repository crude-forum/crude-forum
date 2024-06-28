# Legacy PATH_INFO-based Routing

This folder contains an example to do PATH_INFO-based routing.

This setup is useful if your hosting do not support .htaccess or similar
routing rewrites. Then all the routes would have to be served as part of
path info to a single file.

The paths of the forum would look like this:

* /forum.php
* /forum.php/post/{postID}
* /forum.php/rss

The assets folder will still have to be accessible and `CRUDE_ASSETS_PATH`
should be setup properly to the folder (NOT as subpath of forum.php/*).