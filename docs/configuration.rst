Configuration
=============

Protected field names
---------------------

As this plugin is a Behavior, there are some field names you can not use
because they are used by the internal CakePHP system. Please do not use these
field names:

- priority

Behavior configuration options
------------------------------

This is a list of all the available configuration options which can be
passed in under each field in your behavior configuration.

-  ``pathProcessor``: Returns a ProcessorInterface class name.

   - Default: (string) ``Josegonzalez\Upload\File\Path\DefaultProcessor``

-  ``writer``: Returns a WriterInterface class name.

   - Default: (string) ``Josegonzalez\Upload\File\Writer\DefaultWriter``

-  ``transformer``: Returns a TransformerInterface class name. Can also be a PHP `callable`.

   - Default: (string) ``Josegonzalez\Upload\File\Transformer\DefaultTransformer``

-  ``path``: A path relative to the ``filesystem.root``.

   -  Default: (string)
      ``'webroot{DS}files{DS}{model}{DS}{field}{DS}'``
   -  Tokens:

      -  {DS}: Replaced by a ``DIRECTORY_SEPARATOR``
      -  {model}: Replaced by the Table-alias() method.
      -  {table}: Replaced by the Table->table() method.
      -  {field}: Replaced by the name of the field which will store
         the upload filename.
      -  {field-value:(\w+)}: Replaced by value contained in the
         current entity in the specified field. As an example, if
         your path has ``{field-value:unique_id}`` and the entity
         being saved has a value of ``4b3403665fea6`` for the field
         ``unique_id``, then ``{field-value:unique_id}`` will be
         replaced with ``4b3403665fea6``. This replacement can be used
         multiple times for one or more fields. If the value is not
         a string or zero-length, a LogicException will be thrown.
      -  {primaryKey}: Replaced by the entity primary key, when
         available. If used on a new record being created, a
         LogicException will be thrown.
      -  {year}: Replaced by ``date('Y')``
      -  {month}: Replaced by ``date('m')``
      -  {day}: Replaced by ``date('d')``
      -  {time}: Replaced by ``time()``
      -  {microtime}: Replaced by ``microtime()``

-  ``fields``: An array of fields to use when uploading files

   -  Options:

      - ``fields.dir``: (default ``dir``) Field to use for storing the directory
      - ``fields.type``: (default ``type``) Field to use for storing the filetype
      - ``fields.size``: (default ``size``) Field to use for storing the filesize

- ``filesystem``: An array of configuration info for configuring the writer

  If using the DefaultWriter, the following options are available:

  - Options:

    - ``filesystem.root``: (default ``ROOT . DS``) Directory where files should be written to by default
    - ``filesystem.adapter``: (default Local Flysystem Adapter) A Flysystem-compatible adapter. Can also be a callable that returns an adapter.
    - ``filesystem.visibility``: (default ``'public'``) Sets the related file permissions. Should either be ``'public'`` or ``'private'``.

-  ``nameCallback``: A callable that can be used by the default pathProcessor to rename a file. Only handles original file naming.

   -  Default: ``NULL``
   -  Available arguments:

      -  ``Table $table``: The table of the current entity
      -  ``Entity $entity``: The entity you want to add/edit
      -  ``array $data``: The upload data
      -  ``string $field``: The field for which data will be added/edited
      -  ``array $settings``: UploadBehavior settings for the current field

   -  Return: (string) the new name for the file

-  ``keepFilesOnDelete``: Keep *all* files when deleting a record.

   -  Default: (boolean) ``true``

-  ``deleteCallback``: A callable that can be used to delete different versions of the file.

   -  Default: ``NULL``
   -  Available arguments:

      -  ``string $path``: Basepath of the file you want to delete
      -  ``Entity $entity``: The entity you want to delete
      -  ``string $field``: The field for which data will be removed
      -  ``array $settings``: UploadBehavior settings for the current field

   -  Return: (array) the files you want to be deleted

-  ``restoreValueOnFailure``: Restores original value of the current field when uploaded file has error

   - Defaults: (boolean) ``true``
