Installation
============

The only officialy supported method of installing this plugin is via composer.

Using `Composer <http://getcomposer.org/>`__
--------------------------------------------

`View on
Packagist <https://packagist.org/packages/josegonzalez/cakephp-upload>`__,
and copy the json snippet for the latest version into your project's
``composer.json``.

.. code::

    composer require josegonzalez/cakephp-upload

Enable plugin
-------------

You need to enable the plugin by adding the below code at ``src/Application.php`` file:

.. code:: php

    <?php
    $this->addPlugin('Josegonzalez/Upload');
    
There is also a handy shell command to enable the plugin. Execute the following line:

.. code:: shell
    
   bin/cake plugin load Josegonzalez/Upload
