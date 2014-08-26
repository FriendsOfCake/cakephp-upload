Using a polymorphic attachment model for file storage
-----------------------------------------------------

In some cases you will want to store multiple file uploads for multiple
models, but will not want to use multiple tables because your database
is normalized. For example, we might have a ``Post`` model that can have
many images for a gallery, and a ``Message`` model that has many videos.
In this case, we would use an ``Attachment`` model:

Post hasMany Attachment

We could use the following database schema for the ``Attachment`` model:

.. code:: sql

    CREATE table attachments (
        `id` int(10) unsigned NOT NULL auto_increment,
        `model` varchar(20) NOT NULL,
        `foreign_key` int(11) NOT NULL,
        `name` varchar(32) NOT NULL,
        `attachment` varchar(255) NOT NULL,
        `dir` varchar(255) DEFAULT NULL,
        `type` varchar(255) DEFAULT NULL,
        `size` int(11) DEFAULT 0,
        `active` tinyint(1) DEFAULT 1,
        PRIMARY KEY (`id`)
    );

Our attachment records would thus be able to have a name and be
activated or deactivated on the fly. The schema is simply an example,
and such functionality would need to be implemented within your
application.

Once the ``attachments`` table has been created, we would create the
following model:

.. code:: php

    <?php
    class Attachment extends AppModel {
        public $actsAs = array(
            'Upload.Upload' => array(
                'attachment' => array(
                    'thumbnailSizes' => array(
                        'xvga' => '1024x768',
                        'vga' => '640x480',
                        'thumb' => '80x80',
                    ),
                ),
            ),
        );

        public $belongsTo = array(
            'Post' => array(
                'className' => 'Post',
                'foreignKey' => 'foreign_key',
            ),
            'Message' => array(
                'className' => 'Message',
                'foreignKey' => 'foreign_key',
            ),
        );
    }
    ?>

We would also need to create a valid inverse relationship in the
``Post`` model:

.. code:: php

    <?php
    class Post extends AppModel {
        public $hasMany = array(
            'Image' => array(
                'className' => 'Attachment',
                'foreignKey' => 'foreign_key',
                'conditions' => array(
                    'Image.model' => 'Post',
                ),
            ),
        );
    }
    ?>

The key thing to note here is the ``Post`` model has some conditions on
the relationship to the ``Attachment`` model, where the ``Image.model``
has to be ``Post``. Remember to set the ``model`` field to ``Post``, or
whatever model it is you'd like to attach it to, otherwise you may get
incorrect relationship results when performing find queries.

We would also need a similar relationship in our ``Message`` model:

.. code:: php

    <?php
    class Message extends AppModel {
        public $hasMany = array(
            'Video' => array(
                'className' => 'Attachment',
                'foreignKey' => 'foreign_key',
                'conditions' => array(
                    'Video.model' => 'Message',
                ),
            ),
        );
    }
    ?>

Now that we have our models setup, we should create the proper actions
in our controllers. To keep this short, we shall only document the Post
model:

.. code:: php

    <?php
    class PostsController extends AppController {
        /* the rest of your controller here */
        public function add() {
            if ($this->request->is('post')) {
                try {
                    $this->Post->createWithAttachments($this->request->data);
                    $this->Session->setFlash(__('The message has been saved'));
                } catch (Exception $e) {
                    $this->Session->setFlash($e->getMessage());
                }
            }
        }
    }
    ?>

In the above example, we are calling our custom
``createWithAttachments`` method on the ``Post`` model. This will allow
us to unify the Post creation logic together in one place. That method
is outlined below:

.. code:: php

    <?php
    class Post extends AppModel {
        /* the rest of your model here */

        public function createWithAttachments($data) {
            // Sanitize your images before adding them
            $images = array();
            if (!empty($data['Image'][0])) {
                foreach ($data['Image'] as $i => $image) {
                    if (is_array($data['Image'][$i])) {
                        // Force setting the `model` field to this model
                        $image['model'] = 'Post';

                        // Unset the foreign_key if the user tries to specify it
                        if (isset($image['foreign_key'])) {
                            unset($image['foreign_key']);
                        }

                        $images[] = $image;
                    }
                }
            }
            $data['Image'] = $images;

            // Try to save the data using Model::saveAll()
            $this->create();
            if ($this->saveAll($data)) {
                return true;
            }

            // Throw an exception for the controller
            throw new Exception(__("This post could not be saved. Please try again"));
        }
    }
    ?>

The above model method will:

-  Ensure we only try to save valid images
-  Force the foreign\_key to be unspecified. This will allow saveAll to
   properly associate it
-  Force the model field to ``Post``

Now that this is set, we just need a view for our controller. A sample
view for ``View/Posts/add.ctp`` is as follows (fields not necessary for
the example are omitted):

.. code:: php

    <?php
        echo $this->Form->create('Post', array('type' => 'file'));
        echo $this->Form->input('Image.0.attachment', array('type' => 'file', 'label' => 'Image'));
        echo $this->Form->input('Image.0.model', array('type' => 'hidden', 'value' => 'Post'));
        echo $this->Form->end(__('Add'));
    ?>

The one important thing you'll notice is that I am not referring to the
``Attachment`` model as ``Attachment``, but rather as ``Image``; when I
initially specified the ``$hasMany`` relationship between an
``Attachment`` and a ``Post``, I aliased ``Attachment`` to ``Image``.
This is necessary for cases where many of your Polymorphic models may be
related to each other, as a type of *hint* to the CakePHP ORM to
properly reference model data.

I'm also using ``Model.{n}.field`` notation, which would allow you to
add multiple attachment records to the Post. This is necessary for
``$hasMany`` relationships, which we are using for this example.

Once you have all the above in place, you'll have a working Polymorphic
upload!

Please note that this is not the only way to represent file uploads, but
it is documented here for reference.
