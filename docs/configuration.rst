Behavior configuration options
------------------------------

This is a list of all the available configuration options which can be
passed in under each field in your behavior configuration.

-  ``pathMethod``: The method to use for file paths. This is appended to
   the ``path`` option below

   -  Default: (string) ``primaryKey``
   -  Options:

      -  ``flat``: Does not create a path for each record. Files are
         moved to the value of the 'path' option.
      -  ``primaryKey``: Path based upon the record's primaryKey is
         generated. Persists across a record update.
      -  ``random``: Random path is generated for each file upload in
         the form ``nn/nn/nn`` where ``nn`` are random numbers. Does not
         persist across a record update.
      -  ``randomCombined``: Random path - with model id - is generated
         for each file upload in the form ``ID/nn/nn/nn`` where ``ID``
         is the current model's ID and ``nn`` are random numbers. Does
         not persist across a record update.

-  ``path``: A path relative to the ``rootDir``. Should end in ``{DS}``

   -  Default: (string)
      ``'{ROOT}webroot{DS}files{DS}{model}{DS}{field}{DS}'``
   -  Tokens:

      -  {ROOT}: Replaced by a ``rootDir`` option
      -  {DS}: Replaced by a ``DIRECTORY_SEPARATOR``
      -  {model}: Replaced by the Model Alias.
      -  {field}: Replaced by the field name.
      -  {primaryKey}: Replaced by the record primary key, when
         available. If used on a new record being created, will have
         undefined behavior.
      -  {size}: Replaced by a zero-length string (the empty string)
         when used for the regular file upload path. Only available for
         resized thumbnails.
      -  {geometry}: Replaced by a zero-length string (the empty string)
         when used for the regular file upload path. Only available for
         resized thumbnails.

-  ``fields``: An array of fields to use when uploading files

   -  Default: (array)
      ``array('dir' => 'dir', 'type' => 'type', 'size' => 'size')``
   -  Options:

      -  dir: Field to use for storing the directory
      -  type: Field to use for storing the filetype
      -  size: Field to use for storing the filesize

-  ``rootDir``: Root directory for moving images. Auto-prepended to
   ``path`` and ``thumbnailPath`` where necessary

   -  Default (string) ``ROOT . DS . APP_DIR . DS``

-  ``mimetypes``: Array of mimetypes to use for validation

   -  Default: (array) empty

-  ``extensions``: Array of extensions to use for validation

   -  Default: (array) empty

-  ``maxSize``: Max filesize in bytes for validation

   -  Default: (int) ``2097152``

-  ``minSize``: Minimum filesize in bytes for validation

   -  Default: (int) ``8``

-  ``maxHeight``: Maximum image height for validation

   -  Default: (int) ``0``

-  ``minHeight``: Minimum image height for validation

   -  Default: (int) ``0``

-  ``maxWidth``: Maximum image width for validation

   -  Default: (int) ``0``

-  ``minWidth``: Minimum image width for validation

   -  Default: (int) ``0``

-  ``deleteOnUpdate``: Whether to delete files when uploading new
   versions (potentially dangerous due to naming conflicts)

   -  Default: (boolean) ``false``

-  ``thumbnails``: Whether to create thumbnails or not

   -  Default: (boolean) ``true``

-  ``thumbnailMethod``: The method to use for resizing thumbnails

   -  Default: (string) ``imagick``
   -  Options:

      -  imagick: Uses the PHP ``imagick`` extension to generate
         thumbnails
      -  php: Uses the built-in PHP methods (``GD`` extension) to
         generate thumbnails. Does not support BMP images.

-  ``thumbnailName``: Naming style for a thumbnail

   -  Default: ``NULL``
   -  Note: The tokens ``{size}``, ``{geometry}`` and ``{filename}`` are
      valid for naming and will be auto-replaced with the actual terms.
   -  Note: As well, the extension of the file will be automatically
      added.
   -  Note: When left unspecified, will be set to ``{size}_{filename}``
      or ``{filename}_{size}`` depending upon the value of
      ``thumbnailPrefixStyle``

-  ``thumbnailPath``: A path relative to the ``rootDir`` where
   thumbnails will be saved. Should end in ``{DS}``. If not set,
   thumbnails will be saved at ``path``.

   -  Default: ``NULL``
   -  Tokens:

      -  {ROOT}: Replaced by a ``rootDir`` option
      -  {DS}: Replaced by a ``DIRECTORY_SEPARATOR``
      -  {model}: Replaced by the Model Alias
      -  {field}: Replaced by the field name
      -  {size}: Replaced by the size key specified by a given
         ``thumbnailSize``
      -  {geometry}: Replaced by the geometry value specified by a given
         ``thumbnailSize``

-  ``thumbnailPrefixStyle``: Whether to prefix or suffix the style onto
   thumbnails

   -  Default: (boolean) ``true`` prefix the thumbnail
   -  Note that this overrides ``thumbnailName`` when ``thumbnailName``
      is not specified in your config

-  ``thumbnailQuality``: Quality of thumbnails that will be generated,
   on a scale of 0-100. Not supported gif images when using GD for image
   manipulation.

   -  Default: (int) ``75``

-  ``thumbnailSizes``: Array of thumbnail sizes, with the size-name
   mapping to a geometry

   -  Default: (array) empty

-  ``thumbnailType``: Override the type of the generated thumbnail

   -  Default: (mixed) ``false`` or ``png`` when the upload is a Media
      file
   -  Options:

      -  Any valid image type

-  ``mediaThumbnailType``: Override the type of the generated thumbnail
   for a non-image media (``pdfs``). Overrides ``thumbnailType``

   -  Default: (mixed) ``png``
   -  Options:

      -  Any valid image type

-  ``saveDir``: Can be used to turn off saving the directory

   -  Default: (boolean) ``true``
   -  Note: Because of the way in which the directory is saved, if you
      are using a ``pathMethod`` other than flat and you set ``saveDir``
      to false, you may end up in situations where the file is in a
      location that you cannot predict. This is more of an issue for a
      ``pathMethod`` of ``random`` and ``randomCombined`` than
      ``primaryKey``, but keep this in mind when fiddling with this
      option

-  ``deleteFolderOnDelete``: Delete folder related to current record on
   record delete

   -  Default: (boolean) ``false``
   -  Note: Because of the way in which the directory is saved, if you
      are using a ``pathMethod`` of flat, turning this setting on will
      delete all your images. As such, setting this to true can be
      potentially dangerous.

-  ``keepFilesOnDelete``: Keep *all* files when uploading/deleting a
   record.

   -  Default: (boolean) ``false``
   -  Note: This does not override ``deleteFolderOnDelete``. If you set
      that setting to true, your images may still be deleted. This is so
      that existing uploads are not deleted - unless overwritten.

-  ``mode``: The UNIX permissions to set on the created upload
   directories.

   -  Default: (integer) ``0777``

-  ``handleUploadedFileCallback``: If set to a method name available on
   your model, this model method will handle the movement of the
   original file on disk. Can be used in conjunction with
   ``thumbnailMethod`` to store your files in alternative locations,
   such as S3.

   -  Default: ``NULL``
   -  Available arguments:

      -  ``string $field``: Field being manipulated
      -  ``string $filename``: The filename of the uploaded file
      -  ``string $destination``: The configured destination of the
         moved file

-  ``nameCallback``: A callback that can be used to rename a file.
   Currently only handles original file naming.

   -  Default: ``NULL``
   -  Available arguments:

      -  ``string $field``: Field being manipulated
      -  ``string $currentName``
      -  ``array $data``
      -  ``array options``:

         -  ``isThumbnail`` - a boolean field that is on when we are
            trying to infer a thumbnail path
         -  ``rootDir`` - root directory to replace ``{ROOT}``
         -  ``geometry``
         -  ``size``
         -  ``thumbnailType``
         -  ``thumbnailName``
         -  ``thumbnailMethod``
         -  ``mediaThumbnailType``
         -  ``dir`` field name
         -  ``saveType`` - create, update, delete

   -  Return: String - returns the new name for the file


