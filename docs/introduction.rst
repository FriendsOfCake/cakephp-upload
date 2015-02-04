Introduction
------------

Upload Plugin 2.0
~~~~~~~~~~~~~~~~~
The Upload Plugin is an attempt to sanely upload files using techniques garnered from packages such as MeioUpload , UploadPack and PHP documentation.

Background
~~~~~~~~~~
Media Plugin is too complicated, and it was a PITA to merge the latest updates into MeioUpload, so here I am, building yet another upload plugin. I'll build another in a month and call it "YAUP".

Requirements
~~~~~~~~~~~~
* CakePHP 2.x
* Imagick/GD PHP Extension (for thumbnail creation)
* PHP 5
* Patience

What does this plugin do?
~~~~~~~~~~~~~~~~~~~~~~~~~
* The Upload plugin will transfer files from a form in your application to (by default) the ``webroot/files`` directory organised by the model name and primaryKey field.
* It can also move files around programatically. Such as from the filesystem.
* The path to which the files are saved can be customised.
* It can also create thumbnails for image files if the ``thumbnails`` option is set in the behaviour options.
* The plugin can also upload multiple files at the same time to different fields.
* Each upload field can be configured independantly of each other, such as changing the upload path or thumbnail options.
* Uploaded file information can be stored in a data store, such as a MySQL database.
* A variety of validation rules are provided to help validate against common rules.

This plugin does not do
~~~~~~~~~~~~~~~~~~~~~~~
* It will not convert files between file types. You cannot use it convert a JPG to a PNG
* It will not add watermarks to images for you.
