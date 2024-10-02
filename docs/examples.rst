Examples
========

Basic example
-------------

    Note: You may want to define the Upload behavior *before* the core
    Translate Behavior as they have been known to conflict with each
    other.

.. code-block:: sql

    CREATE table users (
        id int(10) unsigned NOT NULL auto_increment,
        username varchar(20) NOT NULL,
        photo varchar(255)
    );

.. code-block:: php

    <?php
    /*
       In the present example, these changes would be made in:
       src/Model/Table/UsersTable.php
    */
    declare(strict_types=1);

    namespace App\Model\Table;
    use Cake\ORM\Table;

    class UsersTable extends Table
    {
        public function initialize(array $config): void
        {
            $this->setTable('users');
            $this->setDisplayField('username');
            $this->setPrimaryKey('id');

            $this->addBehavior('Josegonzalez/Upload.Upload', [
                // You can configure as many upload fields as possible,
                // where the pattern is `field` => `config`
                //
                // Keep in mind that while this plugin does not have any limits in terms of
                // number of files uploaded per request, you should keep this down in order
                // to decrease the ability of your users to block other requests.
                'photo' => [],
            ]);
        }
    }
    ?>

.. code-block:: php

    <?php
    /*
       In the present example, these changes would be made in:
       templates/Users/add.php
       templates/Users/edit.php
    */
    ?>
    <?= $this->Form->create($user, ['type' => 'file']); ?>
        <?= $this->Form->control('username'); ?>
        <?= $this->Form->control('photo', ['type' => 'file']); ?>
    <?= $this->Form->end(); ?>

    Note: If you used *bake* to generate MVC structure after creating
    the users table, you will need to remove the default scalar validation
    for the photos field.

.. code-block:: php

    public function validationDefault(Validator $validator): void
    {
        $validator
            ->integer('id')
            ->allowEmptyString('id', 'create');

        $validator
            ->scalar('username')
            ->allowEmptyString('username');

        $validator
            // remove ->scalar('photo')
            ->allowEmptyFile('photo');

        return $validator;
    }

Deleting files
--------------

Using the setup from the previous example, uploaded files can only be deleted as long as the path is configured to use
static tokens. As soon as dynamic tokens are incorporated, like for example ``{day}``, the generated path will change
over time, and files cannot be deleted anymore at a later point.

In order to prevent such situations, a field must be added to store the directory of the file as follows:

.. code-block:: sql

    CREATE table users (
        `id` int(10) unsigned NOT NULL auto_increment,
        `username` varchar(20) NOT NULL,
        `photo` varchar(255) DEFAULT NULL,
        `photo_dir` varchar(255) DEFAULT NULL,
        PRIMARY KEY (`id`)
    );

.. code-block:: php

    <?php
    /*
       In the present example, these changes would be made in:
       src/Model/Table/UsersTable.php
    */
    declare(strict_types=1);

    namespace App\Model\Table;
    use Cake\ORM\Table;

    class UsersTable extends Table
    {
        public function initialize(array $config): void
        {
            $this->setTable('users');
            $this->setDisplayField('username');
            $this->setPrimaryKey('id');

            $this->addBehavior('Josegonzalez/Upload.Upload', [
                'photo' => [
                    'fields' => [
                        // if these fields or their defaults exist
                        // the values will be set.
                        'dir' => 'photo_dir', // defaults to `dir`
                        'size' => 'photo_size', // defaults to `size`
                        'type' => 'photo_type', // defaults to `type`
                    ],
                ],
            ]);
        }
    }
    ?>

.. code-block:: php

    <?php
    /*
       In the present example, these changes would be made in:
       templates/Users/add.php
       templates/Users/edit.php
    */
    ?>

    <?= $this->Form->create($user, ['type' => 'file']); ?>
        <?= $this->Form->control('username'); ?>
        <?= $this->Form->control('photo', ['type' => 'file']); ?>
    <?= $this->Form->end(); ?>

Using such a setup, the behavior will use the stored path value instead of generating the path dynamically when deleting
files.

Advanced example
----------------

In this example we'll cover:
- custom database fields
- a nameCallback which makes the filename lowercase only
- a custom transformer where we generate a thumbnail of the uploaded image
- delete the related files when the database record gets deleted
- a deleteCallback to ensure the generated thumbnail gets removed together with the original

This example uses the Imagine library. It can be installed through composer:

.. code-block:: bash

    composer require imagine/imagine

.. code-block:: sql

    CREATE table users (
        id int(10) unsigned NOT NULL auto_increment,
        username varchar(20) NOT NULL,
        photo varchar(255),
        photo_dir varchar(255),
        photo_size int(11),
        photo_type varchar(255)
    );

.. code-block:: php

    <?php
    /*
       In the present example, these changes would be made in:
       src/Model/Table/UsersTable.php
    */
    declare(strict_types=1);

    namespace App\Model\Table;
    use Cake\ORM\Table;

    class UsersTable extends Table
    {
        public function initialize(array $config): void
        {
            $this->setTable('users');
            $this->setDisplayField('username');
            $this->setPrimaryKey('id');

            $this->addBehavior('Josegonzalez/Upload.Upload', [
                'photo' => [
                    'fields' => [
                        'dir' => 'photo_dir',
                        'size' => 'photo_size',
                        'type' => 'photo_type',
                    ],
                    'nameCallback' => function ($table, $entity, $data, $field, $settings) {
                        return strtolower($data->getClientFilename());
                    },
                    'transformer' => function ($table, $entity, $data, $field, $settings, $filename) {
                        $extension = pathinfo($filename, PATHINFO_EXTENSION);

                        // Store the thumbnail in a temporary file
                        $tmp = tempnam(sys_get_temp_dir(), 'upload') . '.' . $extension;

                        // Use the Imagine library to DO THE THING
                        $size = new \Imagine\Image\Box(40, 40);
                        $mode = \Imagine\Image\ImageInterface::THUMBNAIL_INSET;
                        $imagine = new \Imagine\Gd\Imagine();

                        // Save that modified file to our temp file
                        $imagine->open($data->getStream()->getMetadata('uri'))
                            ->thumbnail($size, $mode)
                            ->save($tmp);

                        // Now return the original *and* the thumbnail
                        return [
                            $data->getStream()->getMetadata('uri') => $filename,
                            $tmp => 'thumbnail-' . $filename,
                        ];
                    },
                    'deleteCallback' => function ($path, $entity, $field, $settings) {
                        // When deleting the entity, both the original and the thumbnail will be removed
                        // when keepFilesOnDelete is set to false
                        return [
                            $path . $entity->{$field},
                            $path . 'thumbnail-' . $entity->{$field},
                        ];
                    },
                    'keepFilesOnDelete' => false,
                ]
            ]);
        }
    }
    ?>

.. code-block:: php

    <?php
    /*
       In the present example, these changes would be made in:
       templates/Users/add.php
       templates/Users/edit.php
    */
    ?>
    <?= $this->Form->create($user, ['type' => 'file']); ?>
        <?= $this->Form->control('username'); ?>
        <?= $this->Form->control('photo', ['type' => 'file']); ?>
    <?= $this->Form->end(); ?>

Displaying links to files in your view
--------------------------------------

Once your files have been uploaded you can link to them using the ``HtmlHelper``
by specifying the path and using the file information from the database.

This example uses the `default behaviour configuration <configuration.html>`__ using the model ``Example``.

.. code-block:: php

    <?php
    /*
       In the present example, variations on these changes would be made in:
       templates/Users/view.php
       templates/Users/index.php
    */

    // assuming an entity that has the following
    // data that was set from your controller to your view
    $entity = new Entity([
        'photo' => 'imageFile.jpg',
        'photo_dir' => '7',
    ]);
    $this->set('entity', $entity);

    // You could use the following to create a link to
    // the image (with default settings in place of course)
    echo $this->Html->link('../files/example/image/' . $entity->photo_dir . '/' . $entity->photo);
    ?>

For Windows systems you'll have to build a workaround as Windows systems use backslashes as directory separator which isn't useable in URLs.

.. code-block:: php

    <?php
    /*
       In the present example, variations on these changes would be made in:
       templates/Users/view.php
       templates/Users/index.php
    */

    // assuming an entity that has the following
    // data that was set from your controller to your view
    $entity = new Entity([
        'photo' => 'imageFile.jpg',
        'photo_dir' => '7',
    ]);
    $this->set('entity', $entity);

    // You could use the following to create a link to
    // the image (with default settings in place of course)
    echo $this->Html->link('../files/example/image/' . str_replace('\', '/', $entity->photo_dir) . '/' . $entity->photo);
    ?>

You can optionally create a custom helper to handle url generation, or contain that within your entity. As it is impossible to detect what the actual url for a file should be, such functionality will *never* be made available via this plugin.
