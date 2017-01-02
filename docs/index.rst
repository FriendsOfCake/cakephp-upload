Introduction
------------

Upload Plugin 3.0
~~~~~~~~~~~~~~~~~

The Upload Plugin is an attempt to sanely upload files using techniques garnered from packages such as MeioUpload , UploadPack and PHP documentation. It uses the excellent `Flysystem <http://flysystem.thephpleague.com/>` library to handle file uploads, and can be easily integrated with any image library to handle thumbnail extraction to your exact specifications.

Background
~~~~~~~~~~

Media Plugin is too complicated, and it was a PITA to merge the latest updates into MeioUpload, so here I am, building yet another upload plugin. I'll build another in a month and call it "YAUP".

Requirements
~~~~~~~~~~~~
* CakePHP 3.x
* PHP 5.4+

What does this plugin do?
~~~~~~~~~~~~~~~~~~~~~~~~~
* The Upload plugin will transfer files from a form in your application to (by default) the ``webroot/files`` directory organised by the model name and upload field name.
* It can also move files around programatically. Such as from the filesystem.
* The path to which the files are saved can be customised.
* The plugin can also upload multiple files at the same time to different fields.
* Each upload field can be configured independently of each other, such as changing the upload path etc.
* Uploaded file information can be stored in a data store, such as a MySQL database.
* You can upload files to both disk as well as distributed datastores such as S3 or Dropbox.
* It can optionally delete the files on record deletion
* It offers multiple validation providers but doesn't validate automatically

This plugin does not do
~~~~~~~~~~~~~~~~~~~~~~~
* Create thumbnails. You can use a custom Transformer to create modified versions of file uploads.
* It will not convert files between file types. You cannot use it convert a JPG to a PNG
* It will not add watermarks to images for you.
