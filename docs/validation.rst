Validation
----------

By default, no validation rules are loaded or attached to the table. You must
explicitly load the validation provider(s) and attach each rule if needed.

Installation
^^^^^^^^^^^^

This plugin allows you to only load the validation rules that cover you needs.
At this point there are 3 validation providers:
    
    - UploadValidation (validation rules useful for any upload)
    - ImageValidation (validation rules specifically for images)
    - DefaultValidation (loads all of the above)
    
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
    
        $validator->add('file', 'filePhpUploadSize', [
            'rule' => 'nameOfTheRule', 
            'message' => 'yourErrorMessage', 
            'provider' => 'upload' 
        ]);
    
    ?>
    

UploadValidation
^^^^^^^^^^^^^^^^

**isUnderPhpSizeLimit**

Check that the file does not exceed the max file size specified by PHP

**isUnderFormSizeLimit**

Check that the file does not exceed the max file size specified in the
HTML Form

**isCompletedUpload**

Check that the file was completely uploaded

**isFileUpload**

Check that a file was uploaded

**isSuccessfulWrite**

Check that the file was successfully written to the server

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
^^^^^^^^^^^^^^^

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
