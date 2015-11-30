Upload Plugin Interfaces
--------------------------

For advanced usage of the upload plugin, you will need to implement
one or more of the followng interfaces.


ProcessorInterface
~~~~~~~~~~~~~~~~~~

Fully-namespaced class name: ``Josegonzalez\Upload\File\Path\ProcessorInterface``

This interface is used to create a class that knows how to build paths for a given file upload. Other than the constructor, it contains two methods:

- ``basepath``: Returns the basepath for the current field/data combination
- ``filename``: Returns the filename for the current field/data combination

Refer to ``Josegonzalez\Upload\File\Path\DefaultProcessor`` for more details.

TransformerInterface
~~~~~~~~~~~~~~~~~~~~

Fully-namespaced class name: ``Josegonzalez\Upload\File\Transformer\TransformerInterface``

This interface is used to transform the uploaded file into one or more files that will be written somewhere to disk. This can be useful in cases where you may wish to use an external library to extract thumbnails or create PDF previews. The previous image manipulation functionality should be created at this layer.

Other than the constructor, it contains one method:

- ``transform``: Returns an array of key/value pairs, where the key is a file on disk and the value is the name of the output file. This can be used for properly naming uploaded/created files.

Refer to ``Josegonzalez\Upload\File\Transformer\DefaultTransformer`` for more details. You may **also** wish to look at ``Josegonzalez\Upload\File\Transformer\SlugTransformer`` as an alternative.

WriterInterface
~~~~~~~~~~~~~~~

Fully-namespaced class name: ``Josegonzalez\Upload\File\Writer\WriterInterface``

This interface is used to actually write files to disk. It writes files to disk using the ``Flysystem`` library, and defaults to local storage by default. Implement this interface if you want to customize the file writing process.

Other than the constructor, it contains one methods:

- ``write``: Writes a set of files to an output.

Refer to ``Josegonzalez\Upload\File\Writer\DefaultWriter`` for more details.
