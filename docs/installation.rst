Installation
------------

Using `Composer <http://getcomposer.org/>`__
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

`View on
Packagist <https://packagist.org/packages/josegonzalez/cakephp-upload>`__,
and copy the json snippet for the latest version into your project's
``composer.json``. Eg, v. 1.1.1 would look like this:

.. code:: json

    {
        "require": {
            "josegonzalez/cakephp-upload": "1.1.1"
        }
    }

This plugin has the type ``cakephp-plugin`` set in it's own
``composer.json``, composer knows to install it inside your ``/Plugins``
directory, rather than in the usual vendors file. It is recommended that
you add ``/Plugins/Upload`` to your .gitignore file. (Why? `read
this <http://getcomposer.org/doc/faqs/should-i-commit-the-dependencies-in-my-vendor-directory.md>`__.)

Manual
~~~~~~

-  Download this:
   http://github.com/josegonzalez/cakephp-upload/zipball/master
-  Unzip that download.
-  Copy the resulting folder to ``app/Plugin``
-  Rename the folder you just copied to ``Upload``

GIT Submodule
~~~~~~~~~~~~~

In your *app directory* type:

.. code:: bash

    git submodule add -b master git://github.com/josegonzalez/cakephp-upload.git Plugin/Upload
    git submodule init
    git submodule update

GIT Clone
~~~~~~~~~

In your ``Plugin`` directory type:

.. code:: bash

    git clone -b master git://github.com/josegonzalez/cakephp-upload.git Upload

Imagick Support
---------------

To enable `Imagick <http://www.imagemagick.org/>`__ support, you need to
have Imagick installed:

.. code:: bash

    # Debian systems
    sudo apt-get install php-imagick

    # OS X Homebrew
    brew tap homebrew/dupes
    brew tap josegonzalez/homebrew-php
    brew install php54-imagick

    # From pecl
    pecl install imagick

If you cannot install Imagick, instead configure the plugin with
``'thumbnailMethod'  => 'php'`` in the files options.

Enable plugin
-------------

You need to enable the plugin your ``app/Config/bootstrap.php`` file:

.. code:: php

    <?php
    CakePlugin::load('Upload');

If you are already using ``CakePlugin::loadAll();``, then this is not
necessary.
