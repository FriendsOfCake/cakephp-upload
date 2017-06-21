Examples
========

Basic example
-------------

    Note: You may want to define the Upload behavior *before* the core
    Translate Behavior as they have been known to conflict with each
    other.

.. code:: sql

    CREATE table users (
        id int(10) unsigned NOT NULL auto_increment,
        username varchar(20) NOT NULL,
        photo varchar(255)
    );

.. code:: php

    <?php
    /*
       In the present example, these changes would be made in:
       src/Model/Table/UsersTable.php
    */

    namespace App\Model\Table;
    use Cake\ORM\Table;

    class UsersTable extends Table
    {
        public function initialize(array $config)
        {
            $this->setTable('users');
            $this->setDisplayField('username');
            $this->setPrimaryKey('id');

            // for CakePHP 3.0.x-3.3.x, use the following lines instead of the previous:
            // $this->table('users');
            // $this->displayField('username');
            // $this->primaryKey('id');

            $this->addBehavior('Josegonzalez/Upload.Upload', [
                // You can configure as many upload fields as possible,
                // where the pattern is `field` => `config`
                //
                // Keep in mind that while this plugin does not have any limits in terms of
                // number of files uploaded per request, you should keep this down in order
                // to decrease the ability of your users to block other requests.
                'photo' => []
            ]);
        }
    }
    ?>

.. code:: php

    <?php
    /*
       In the present example, these changes would be made in:
       src/Template/Users/add.ctp
       src/Template/Users/edit.ctp
    */
    ?>
    <?php echo $this->Form->create($user, ['type' => 'file']); ?>
        <?php echo $this->Form->input('username'); ?>
        <?php echo $this->Form->input('photo', ['type' => 'file']); ?>
    <?php echo $this->Form->end(); ?>

Using the above setup, uploaded files cannot be deleted. To do so, a
field must be added to store the directory of the file as follows:

.. code:: sql

    CREATE table users (
        `id` int(10) unsigned NOT NULL auto_increment,
        `username` varchar(20) NOT NULL,
        `photo` varchar(255) DEFAULT NULL,
        `photo_dir` varchar(255) DEFAULT NULL,
        PRIMARY KEY (`id`)
    );

.. code:: php

    <?php
    /*
       In the present example, these changes would be made in:
       src/Model/Table/UsersTable.php
    */

    namespace App\Model\Table;
    use Cake\ORM\Table;

    class UsersTable extends Table
    {
        public function initialize(array $config)
        {
            $this->setTable('users');
            $this->setDisplayField('username');
            $this->setPrimaryKey('id');

            // for CakePHP 3.0.x-3.3.x, use the following lines instead of the previous:
            // $this->table('users');
            // $this->displayField('username');
            // $this->primaryKey('id');

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

.. code:: php

    <?php
    /*
       In the present example, these changes would be made in:
       src/Template/Users/add.ctp
       src/Template/Users/edit.ctp
    */
    ?>

    <?php echo $this->Form->create($user, ['type' => 'file']); ?>
        <?php echo $this->Form->input('username'); ?>
        <?php echo $this->Form->input('photo', ['type' => 'file']); ?>
        <?php echo $this->Form->input('photo_dir', ['type' => 'hidden']); ?>
    <?php echo $this->Form->end(); ?>

Displaying links to files in your view
--------------------------------------

Once your files have been uploaded you can link to them using the ``HtmlHelper`` by specifying the path and using the file information from the database.

This example uses the `default behaviour configuration <configuration.html>`__ using the model ``Example``.

.. code:: php

    <?php
    /*
       In the present example, variations on these changes would be made in:
       src/Template/Users/view.ctp
       src/Template/Users/index.ctp
    */

    // assuming an entity that has the following
    // data that was set from your controller to your view
    $entity = new Entity([
        'photo' => 'imageFile.jpg',
        'photo_dir' => '7'
    ]);
    $this->set('entity', $entity);

    // You could use the following to create a link to
    // the image (with default settings in place of course)
    echo $this->Html->link('../files/example/image/' . $entity->photo_dir . '/' . $entity->photo);
    ?>

You can optionally create a custom helper to handle url generation, or contain that within your entity. As it is impossible to detect what the actual url for a file should be, such functionality will *never* be made available via this plugin.
