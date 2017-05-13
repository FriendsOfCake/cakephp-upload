Validation
==========

By default, no validation rules are loaded or attached to the table. You must
explicitly load the validation provider(s) and attach each rule if needed.

Installation
------------

This plugin allows you to only load the validation rules that cover your needs.
At this point there are 3 validation providers:

- ``UploadValidation``: validation rules useful for any upload
- ``ImageValidation``: validation rules specifically for images
- ``DefaultValidation``: loads all of the above

Since by default, no validation rules are loaded, you should start with that:

.. code:: php

    <?php

        $validator->provider('upload', \Josegonzalez\Upload\Validation\UploadValidation::class);
        // OR
        $validator->provider('upload', \Josegonzalez\Upload\Validation\ImageValidation::class);
        // OR
        $validator->provider('upload', \Josegonzalez\Upload\Validation\DefaultValidation::class);

    ?>

Afterwards, you can use its rules like:

.. code:: php

    <?php

        $validator->add('file', 'customName', [
            'rule' => 'nameOfTheRule',
            'message' => 'yourErrorMessage',
            'provider' => 'upload'
        ]);

    ?>

It might come in handy to only use a validation rule when there actually is an uploaded file:

.. code:: php

    <?php

        $validator->add('file', 'customName', [
            'rule' => 'nameOfTheRule',
            'message' => 'yourErrorMessage',
            'provider' => 'upload',
            'on' => function($context) {
                return !empty($context['data']['file']) && $context['data']['file']['error'] == UPLOAD_ERR_OK;
            }
        ]);

    ?>

More information on conditional validation can be found `here <http://book.cakephp.org/3.0/en/core-libraries/validation.html#conditional-validation>`__.

UploadValidation
----------------

**isUnderPhpSizeLimit**

Check that the file does not exceed the max file size specified by PHP

.. code:: php

    <?php

        $validator->add('file', 'fileUnderPhpSizeLimit', [
            'rule' => 'isUnderPhpSizeLimit',
            'message' => 'This file is too large',
            'provider' => 'upload'
        ]);

    ?>

**isUnderFormSizeLimit**

Check that the file does not exceed the max file size specified in the
HTML Form

.. code:: php

    <?php

        $validator->add('file', 'fileUnderFormSizeLimit', [
            'rule' => 'isUnderFormSizeLimit',
            'message' => 'This file is too large',
            'provider' => 'upload'
        ]);

    ?>

**isCompletedUpload**

Check that the file was completely uploaded

.. code:: php

    <?php

        $validator->add('file', 'fileCompletedUpload', [
            'rule' => 'isCompletedUpload',
            'message' => 'This file could not be uploaded completely',
            'provider' => 'upload'
        ]);

    ?>

**isFileUpload**

Check that a file was uploaded

.. code:: php

    <?php

        $validator->add('file', 'fileFileUpload', [
            'rule' => 'isFileUpload',
            'message' => 'There was no file found to upload',
            'provider' => 'upload'
        ]);

    ?>

**isSuccessfulWrite**

Check that the file was successfully written to the server

.. code:: php

    <?php

        $validator->add('file', 'fileSuccessfulWrite', [
            'rule' => 'isSuccessfulWrite',
            'message' => 'This upload failed',
            'provider' => 'upload'
        ]);

    ?>

**isBelowMaxSize**

Check that the file is below the maximum file upload size (checked in
bytes)

.. code:: php

    <?php

        $validator->add('file', 'fileBelowMaxSize', [
            'rule' => ['isBelowMaxSize', 1024],
            'message' => 'This file is too large',
            'provider' => 'upload'
        ]);

    ?>

**isAboveMinSize**

Check that the file is above the minimum file upload size (checked in
bytes)

.. code:: php

    <?php

        $validator->add('file', 'fileAboveMinSize', [
            'rule' => ['isAboveMinSize', 1024],
            'message' => 'This file is too small',
            'provider' => 'upload'
        ]);

    ?>

ImageValidation
---------------

**isAboveMinHeight**

Check that the file is above the minimum height requirement (checked in
pixels)

.. code:: php

    <?php

        $validator->add('file', 'fileAboveMinHeight', [
            'rule' => ['isAboveMinHeight', 200],
            'message' => 'This image should at least be 200px high',
            'provider' => 'upload'
        ]);

    ?>

**isBelowMaxHeight**

Check that the file is below the maximum height requirement (checked in
pixels)

.. code:: php

    <?php

        $validator->add('file', 'fileBelowMaxHeight', [
            'rule' => ['isBelowMaxHeight', 200],
            'message' => 'This image should not be higher than 200px',
            'provider' => 'upload'
        ]);

    ?>

**isAboveMinWidth**

Check that the file is above the minimum width requirement (checked in
pixels)

.. code:: php

    <?php

        $validator->add('file', 'fileAboveMinWidth', [
            'rule' => ['isAboveMinWidth', 200],
            'message' => 'This image should at least be 200px wide',
            'provider' => 'upload'
        ]);

    ?>

**isBelowMaxWidth**

Check that the file is below the maximum width requirement (checked in
pixels)

.. code:: php

    <?php

        $validator->add('file', 'fileBelowMaxWidth', [
            'rule' => ['isBelowMaxWidth', 200],
            'message' => 'This image should not be wider than 200px',
            'provider' => 'upload'
        ]);

    ?>
