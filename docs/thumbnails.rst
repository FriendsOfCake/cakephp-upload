Thumbnail Sizes and Styles
--------------------------

The Upload plugin can automatically generate various thumbnails at
different sizes for you when uploading files. The thumbnails must be
configured in order for thumbnails to be generated.

To generate thumbnails you will need to configure the ``thumbnailSizes``
option under the field you are configuring.

.. code:: php

    <?php
    class User extends AppModel {
        public $name = 'User';
        public $actsAs = array(
            'Upload.Upload' => array(
                'photo' => array( // The field we are configuring for
                    'thumbnailSizes' => array( // Various sizes of thumbnail to generate
                        'big' => '200x200', // Resize for best fit to 200px by 200px, cropped from the center of the image. Prefix with big_
                        'small' => '120x120',
                        'thumb' => '80x80'
                    )
                )
            )
        );
    }
    ?>

Once this configuration is set when uploading a file a thumbnail will
automatically be generated with the prefix defined in the options. For
example (using default configuration)
``app/webroot/files/Example/photo/1/big_example.jpg``. Where ``Example``
is the model, ``photo`` is the field, ``1`` is the model primaryKey
value and finally ``big_`` is the thumbnail size prefix to the filename.

Thumbnail sizes only apply to images of the following types:

-  image/bmp
-  image/gif
-  image/jpeg
-  image/pjpeg
-  image/png
-  image/vnd.microsoft.icon
-  image/x-icon

You can specify any of the following resize modes for your sizes:

-  ``100x80`` - resize for best fit into these dimensions, with
   overlapping edges trimmed if original aspect ratio differs
-  ``[100x80]`` - resize to fit these dimensions, with white banding if
   original aspect ratio differs
-  ``100w`` - maintain original aspect ratio, resize to 100 pixels wide
-  ``80h`` - maintain original aspect ratio, resize to 80 pixels high
-  ``80l`` - maintain original aspect ratio, resize so that longest side
   is 80 pixels
-  ``600mw`` - maintain original aspect ratio, resize to max 600 pixels
   wide, or copy the original image if it is less than 600 pixels wide
-  ``800mh`` - maintain original aspect ratio, resize to max 800 pixels
   high, or copy the original image if it is less than 800 pixels high
-  ``960ml`` - maintain original aspect ratio, resize so that longest
   side is max 960 pixels, or copy the original image if the thumbnail
   would be bigger than the original.

PDF Support
~~~~~~~~~~~

It is now possible to generate a thumbnail for the first page of a PDF
file. (Only works with the ``imagick`` ``thumbnailMethod``.) Please read
about the `Behavior options <configuration.md>`__ for more details as to
how to `configure this plugin <configuration.md>`__.
