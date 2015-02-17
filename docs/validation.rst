Validation rules
----------------

By default, no validation rules are attached to the model. You must
explicitly attach each rule if needed. Rules not referring to PHP upload
errors are configurable but fallback to the behavior configuration.

isUnderPhpSizeLimit
^^^^^^^^^^^^^^^^^^^

Check that the file does not exceed the max file size specified by PHP

.. code:: php

    <?php
    public $validate = array(
        'photo' => array(
            'rule' => 'isUnderPhpSizeLimit',
            'message' => 'File exceeds upload filesize limit'
        )
    );
    ?>

isUnderFormSizeLimit
^^^^^^^^^^^^^^^^^^^^

Check that the file does not exceed the max file size specified in the
HTML Form

.. code:: php

    <?php
    public $validate = array(
        'photo' => array(
            'rule' => 'isUnderFormSizeLimit',
            'message' => 'File exceeds form upload filesize limit'
        )
    );
    ?>

isCompletedUpload
^^^^^^^^^^^^^^^^^

Check that the file was completely uploaded

.. code:: php

    <?php
    public $validate = array(
        'photo' => array(
            'rule' => 'isCompletedUpload',
            'message' => 'File was not successfully uploaded'
        )
    );
    ?>

isFileUpload
^^^^^^^^^^^^

Check that a file was uploaded

.. code:: php

    <?php
    public $validate = array(
        'photo' => array(
            'rule' => 'isFileUpload',
            'message' => 'File was missing from submission'
        )
    );
    ?>

isFileUploadOrHasExistingValue
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Check that either a file was uploaded, or the existing value in the
database is not blank

.. code:: php

    <?php
    public $validate = array(
        'photo' => array(
            'rule' => 'isFileUploadOrHasExistingValue',
            'message' => 'File was missing from submission'
        )
    );
    ?>

tempDirExists
^^^^^^^^^^^^^

Check that the PHP temporary directory is missing

.. code:: php

    <?php
    public $validate = array(
        'photo' => array(
            'rule' => 'tempDirExists',
            'message' => 'The system temporary directory is missing'
        )
    );
    ?>

If the argument ``$requireUpload`` is passed, we can skip this check
when a file is not uploaded:

.. code:: php

    <?php
    public $validate = array(
        'photo' => array(
            'rule' => array('tempDirExists', false),
            'message' => 'The system temporary directory is missing'
        )
    );
    ?>

In the above, the variable ``$requireUpload`` has a value of false. By
default, ``requireUpload`` is set to true.

isSuccessfulWrite
^^^^^^^^^^^^^^^^^

Check that the file was successfully written to the server

.. code:: php

    <?php
    public $validate = array(
        'photo' => array(
            'rule' => 'isSuccessfulWrite',
            'message' => 'File was unsuccessfully written to the server'
        )
    );
    ?>

If the argument ``$requireUpload`` is passed, we can skip this check
when a file is not uploaded:

.. code:: php

    <?php
    public $validate = array(
        'photo' => array(
            'rule' => array('isSuccessfulWrite', false),
            'message' => 'File was unsuccessfully written to the server'
        )
    );
    ?>

In the above, the variable ``$requireUpload`` has a value of false. By
default, ``requireUpload`` is set to true.

noPhpExtensionErrors
^^^^^^^^^^^^^^^^^^^^

Check that a PHP extension did not cause an error

.. code:: php

    <?php
    public $validate = array(
        'photo' => array(
            'rule' => 'noPhpExtensionErrors',
            'message' => 'File was not uploaded because of a faulty PHP extension'
        )
    );
    ?>

If the argument ``$requireUpload`` is passed, we can skip this check
when a file is not uploaded:

.. code:: php

    <?php
    public $validate = array(
        'photo' => array(
            'rule' => array('noPhpExtensionErrors', false),
            'message' => 'File was not uploaded because of a faulty PHP extension'
        )
    );
    ?>

In the above, the variable ``$requireUpload`` has a value of false. By
default, ``requireUpload`` is set to true.

isValidMimeType
^^^^^^^^^^^^^^^

Check that the file is of a valid mimetype

.. code:: php

    <?php
    public $validate = array(
        'photo' => array(
            'rule' => array('isValidMimeType', array('application/pdf', 'image/png')),
            'message' => 'File is not a pdf or png'
        )
    );
    ?>

If the argument ``$requireUpload`` is passed, we can skip this check
when a file is not uploaded:

.. code:: php

    <?php
    public $validate = array(
        'photo' => array(
            'rule' => array('isValidMimeType', array('application/pdf', 'image/png'), false),
            'message' => 'File is not a pdf or png'
        )
    );
    ?>

In the above, the variable ``$requireUpload`` has a value of false. By
default, ``requireUpload`` is set to true.

isWritable
^^^^^^^^^^

Check that the upload directory is writable

.. code:: php

    <?php
    public $validate = array(
        'photo' => array(
            'rule' => array('isWritable'),
            'message' => 'File upload directory was not writable'
        )
    );
    ?>

If the argument ``$requireUpload`` is passed, we can skip this check
when a file is not uploaded:

.. code:: php

    <?php
    public $validate = array(
        'photo' => array(
            'rule' => array('isWritable', false),
            'message' => 'File upload directory was not writable'
        )
    );
    ?>

In the above, the variable ``$requireUpload`` has a value of false. By
default, ``requireUpload`` is set to true.

isValidDir
^^^^^^^^^^

Check that the upload directory exists

.. code:: php

    <?php
    public $validate = array(
        'photo' => array(
            'rule' => array('isValidDir'),
            'message' => 'File upload directory does not exist'
        )
    );
    ?>

If the argument ``$requireUpload`` is passed, we can skip this check
when a file is not uploaded:

.. code:: php

    <?php
    public $validate = array(
        'photo' => array(
            'rule' => array('isValidDir', false),
            'message' => 'File upload directory does not exist'
        )
    );
    ?>

In the above, the variable ``$requireUpload`` has a value of false. By
default, ``requireUpload`` is set to true.

isBelowMaxSize
^^^^^^^^^^^^^^

Check that the file is below the maximum file upload size (checked in
bytes)

.. code:: php

    <?php
    public $validate = array(
        'photo' => array(
            'rule' => array('isBelowMaxSize', 1024),
            'message' => 'File is larger than the maximum filesize'
        )
    );
    ?>

If the argument ``$requireUpload`` is passed, we can skip this check
when a file is not uploaded:

.. code:: php

    <?php
    public $validate = array(
        'photo' => array(
            'rule' => array('isBelowMaxSize', 1024, false),
            'message' => 'File is larger than the maximum filesize'
        )
    );
    ?>

In the above, the variable ``$requireUpload`` has a value of false. By
default, ``requireUpload`` is set to true.

isAboveMinSize
^^^^^^^^^^^^^^

Check that the file is above the minimum file upload size (checked in
bytes)

.. code:: php

    <?php
    public $validate = array(
        'photo' => array(
            'rule' => array('isAboveMinSize', 1024),
            'message' => 'File is below the mimimum filesize'
        )
    );
    ?>

If the argument ``$requireUpload`` is passed, we can skip this check
when a file is not uploaded:

.. code:: php

    <?php
    public $validate = array(
        'photo' => array(
            'rule' => array('isAboveMinSize', 1024, false),
            'message' => 'File is below the mimimum filesize'
        )
    );
    ?>

In the above, the variable ``$requireUpload`` has a value of false. By
default, ``requireUpload`` is set to true.

isValidExtension
^^^^^^^^^^^^^^^^

Check that the file has a valid extension

.. code:: php

    <?php
    public $validate = array(
        'photo' => array(
            'rule' => array('isValidExtension', array('pdf', 'png', 'txt')),
            'message' => 'File does not have a pdf, png, or txt extension'
        )
    );
    ?>

If the argument ``$requireUpload`` is passed, we can skip this check
when a file is not uploaded:

.. code:: php

    <?php
    public $validate = array(
        'photo' => array(
            'rule' => array('isValidExtension', array('pdf', 'png', 'txt'), false),
            'message' => 'File does not have a pdf, png, or txt extension'
        )
    );
    ?>

In the above, the variable ``$requireUpload`` has a value of false. By
default, ``requireUpload`` is set to true.

isAboveMinHeight
^^^^^^^^^^^^^^^^

Check that the file is above the minimum height requirement (checked in
pixels)

.. code:: php

    <?php
    public $validate = array(
        'photo' => array(
            'rule' => array('isAboveMinHeight', 150),
            'message' => 'File is below the minimum height'
        )
    );
    ?>

If the argument ``$requireUpload`` is passed, we can skip this check
when a file is not uploaded:

.. code:: php

    <?php
    public $validate = array(
        'photo' => array(
            'rule' => array('isAboveMinHeight', 150, false),
            'message' => 'File is below the minimum height'
        )
    );
    ?>

In the above, the variable ``$requireUpload`` has a value of false. By
default, ``requireUpload`` is set to true.

isBelowMaxHeight
^^^^^^^^^^^^^^^^

Check that the file is below the maximum height requirement (checked in
pixels)

.. code:: php

    <?php
    public $validate = array(
        'photo' => array(
            'rule' => array('isBelowMaxHeight', 150),
            'message' => 'File is above the maximum height'
        )
    );
    ?>

If the argument ``$requireUpload`` is passed, we can skip this check
when a file is not uploaded:

.. code:: php

    <?php
    public $validate = array(
        'photo' => array(
            'rule' => array('isBelowMaxHeight', 150, false),
            'message' => 'File is above the maximum height'
        )
    );
    ?>

In the above, the variable ``$requireUpload`` has a value of false. By
default, ``requireUpload`` is set to true.

isAboveMinWidth
^^^^^^^^^^^^^^^

Check that the file is above the minimum width requirement (checked in
pixels)

.. code:: php

    <?php
    public $validate = array(
        'photo' => array(
            'rule' => array('isAboveMinWidth', 150),
            'message' => 'File is below the minimum width'
        )
    );
    ?>

If the argument ``$requireUpload`` is passed, we can skip this check
when a file is not uploaded:

.. code:: php

    <?php
    public $validate = array(
        'photo' => array(
            'rule' => array('isAboveMinWidth', 150, false),
            'message' => 'File is below the minimum width'
        )
    );
    ?>

In the above, the variable ``$requireUpload`` has a value of false. By
default, ``requireUpload`` is set to true.

isBelowMaxWidth
^^^^^^^^^^^^^^^

Check that the file is below the maximum width requirement (checked in
pixels)

.. code:: php

    <?php
    public $validate = array(
        'photo' => array(
            'rule' => array('isBelowMaxWidth', 150),
            'message' => 'File is above the maximum width'
        )
    );
    ?>

If the argument ``$requireUpload`` is passed, we can skip this check
when a file is not uploaded:

.. code:: php

    <?php
    public $validate = array(
        'photo' => array(
            'rule' => array('isBelowMaxWidth', 150, false),
            'message' => 'File is above the maximum width'
        )
    );
    ?>

In the above, the variable ``$requireUpload`` has a value of false. By
default, ``requireUpload`` is set to true.
