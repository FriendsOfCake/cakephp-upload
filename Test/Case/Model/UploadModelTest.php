<?php
App::uses('Upload.Upload', 'Model/Behavior');


class UploadTestModel extends CakeTestModel {

    public $useTable = 'uploads';


    public $name = 'Upload';


	public $actsAs = array(
		'Upload.Upload' => array(
			'photo' => array(
                'fields' => array(
                     'type' => 'type',
                     'dir' => 'dir'
                 ),
                 'mimetypes' => array(
                     'image/png',
                     'image/jpeg',
                     'image/gif'
                 ),
                 'thumbnailSizes' => array(
                     'thumb' => '80h'   // required to trigger generation of thumbnails
                 )
			)
		)
	);
}


class UploadTestCase extends CakeTestCase {


    public $fixtures = array(
        'plugin.upload.upload'
    );


    public $MockUploadBehavior = null;


    public function setUp() {
        parent::setUp();
        $this->Upload = new UploadTestModel();
        $this->MockUploadBehavior = $this->getMock('UploadBehavior', array('handleUploadedFile', 'unlink', '_createThumbnails'));

        $this->MockUploadBehavior->setup($this->Upload, $this->Upload->actsAs['Upload.Upload']);
        $this->Upload->Behaviors->set('Upload', $this->MockUploadBehavior);
    }


    public function tearDown() {
        unset($this->Upload, $this->MockUploadBehavior);
    }


    /**
     * Tests Upload::save creates a new Upload record including
     * an upload of an PNG image file using the Upload.Upload behavior
     * with the default path and pathMethod (primaryKey)
     */
    public function testSaveSuccessPngDefaultPathAndPathMethod() {
        $next_id = (1+$this->Upload->field('id', array(), array('Upload.id' => 'DESC')));
        $destination_dir = APP . 'webroot' . DS . 'files' . DS . 'upload' . DS . 'photo' . DS . $next_id . DS;

        $Upload = array(
            'Upload' => array(
                'photo' => array(
                    'name' => 'image-png.png'
                    , 'type' => 'image/png'
                    , 'tmp_name' => 'image-png-tmp.png'
                    , 'error' => UPLOAD_ERR_OK
                    , 'size' => 8123
                )
            )
        );

        $this->MockUploadBehavior->expects($this->never())
            ->method('unlink');

        $this->MockUploadBehavior->expects($this->once())
            ->method('handleUploadedFile')
            ->with(
                $this->equalTo('Upload')
                , $this->equalTo('photo')
                , $this->equalTo('image-png-tmp.png')
                , $this->equalTo($destination_dir . 'image-png.png')
            )
            ->will($this->returnValue(true));

        $this->MockUploadBehavior->expects($this->once())
            ->method('_createThumbnails')
            ->with(
                $this->isInstanceOf('UploadTestModel')
                , $this->equalTo('photo')
                , $this->equalTo($destination_dir)
                , $this->equalTo($destination_dir)
            )
            ->will($this->returnValue(true));

        $this->assertTrue(false !== $this->Upload->save($Upload));
        $this->assertSame(array(), array_keys($this->Upload->validationErrors));

        // assert file details stored in the database
        $this->assertSame('image-png.png', $this->Upload->field('photo', array('Upload.id' => $next_id)));
        $this->assertSame('image/png', $this->Upload->field('type', array('Upload.id' => $next_id)));
        $this->assertSame((string)$next_id, $this->Upload->field('dir', array('Upload.id' => $next_id)));
    }
}