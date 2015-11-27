Thumbnail shell
---------------

What it does
~~~~~~~~~~~~
The shell will look through your database for images and regenerate the thumbnails based on
your models Upload behaviour configuration. This allows you to change your thumbnail configuration and run the
shell to update your images without having to re-upload the image.

How it works
~~~~~~~~~~~~
The shell takes the model you provide and checks that the Upload plugin is present and configured. Then it will loop
though all the images checking that the configured upload field is populated in the database and also ensuring that the
file exists on the file system. Then it will regenerate the thumbnails using the current model configuration.

Running the shell
~~~~~~~~~~~~~~~~~
You can run the shell from the command line as you would any cake shell.

.. code:: bash

    Console/cake upload.thumbnail generate

You will then be asked which model you want to process, and the shell will then process your images.