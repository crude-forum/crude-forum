# Legacy Routing Support

This is an example folder to demonstrate how to do legacy file-based
routing with CrudeForum.

Basically, the forum core support emulated "routing" when bootstraped.
This provide unique opportunity to alter the base route file (i.e.
something other than "index.php"). Also provide routes to legacy files
to co-exists with modern PHP routing.

This is still based on the assumption that the proper PHP routing works
in your enviornment (or the URL in navigation would not work).

To completely use legacy routing with CrudeForum, you'll need to override
the template file and modify the navigation links and post form's action
URL.