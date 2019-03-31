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

If you install your plugins via composer or bake you shouldn’t need to configure class autoloading for your plugins.

Prior to 3.6.0, you should use ``Plugin::load()``:

You need to enable the plugin your ``config/bootstrap.php`` file:

.. code:: php

    <?php
    Plugin::load('Josegonzalez/Upload');

If you are already using ``Plugin::loadAll();``, then this is not
necessary.
