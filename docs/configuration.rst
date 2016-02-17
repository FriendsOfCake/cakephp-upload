Behavior configuration options
------------------------------

This is a list of all the available configuration options which can be
passed in under each field in your behavior configuration.

-  ``pathProcessor``: Returns a ProcessorInterface class name.

   - Default: (string)
     ``Josegonzalez\Upload\File\Path\DefaultProcessor``

-  ``writer``: Returns a WriterInterface class name.

    - Default: (string)
      ``Josegonzalez\Upload\File\Writer\DefaultWriter``

-  ``transformer``: Returns a TransformerInterface class name. Can also be a PHP `callable`.

    - Default: (string)
      ``Josegonzalez\Upload\File\Transformer\DefaultTransformer``

-  ``path``: A path relative to the ``filesystem.root``. Should end in ``{DS}``

   -  Default: (string)
      ``'webroot{DS}files{DS}{model}{DS}{field}{DS}'``
   -  Tokens:

      -  {DS}: Replaced by a ``DIRECTORY_SEPARATOR``
      -  {model}: Replaced by the Table-alias() method.
      -  {table}: Replaced by the Table->table() method.
      -  {field}: Replaced by the field name.
      -  {primaryKey}: Replaced by the entity primary key, when
         available. If used on a new record being created, will have
         undefined behavior.
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

-  ``nameCallback``: A callable that can be used by the default pathProcessor to rename a file. Only handles original file naming.

   -  Default: ``NULL``
   -  Available arguments:

      -  ``array $data``: The upload data
      -  ``array $settings``: UploadBehavior settings for the current field

   -  Return: (string) the new name for the file

-  ``keepFilesOnDelete``: Keep *all* files when uploading/deleting a record.

   -  Default: (boolean) ``true``
