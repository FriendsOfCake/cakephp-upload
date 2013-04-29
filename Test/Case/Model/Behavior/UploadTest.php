<?php
App::uses('Upload.Upload', 'Model/Behavior');
App::uses('Folder', 'Utility');

class TestUpload extends CakeTestModel {
	public $useTable = 'uploads';
	public $actsAs = array(
		'Upload.Upload' => array(
			'photo' => array(
				'thumbnailMethod' => '_bad_thumbnail_method_',
				'pathMethod' => '_bad_path_method_',
			)
		)
	);
}

class TestUploadTwo extends CakeTestModel {
	public $useTable = 'uploads';
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
					'thumb' => '80h'
				)
			)
		)
	);
}

class UploadBehaviorTest extends CakeTestCase {

	public $fixtures = array('plugin.upload.upload');
	public $TestUpload = null;
	public $MockUpload = null;
	public $data = array();
	public $currentTestMethod;

	function startTest($method) {
		$this->TestUpload = ClassRegistry::init('TestUpload');
		$this->TestUploadTwo = ClassRegistry::init('TestUploadTwo');
		$this->currentTestMethod = $method;
		$this->data['test_ok'] = array(
			'photo' => array(
				'name'  => 'Photo.png',
				'tmp_name'  => 'Photo.png',
				'dir'   => '/tmp/php/file.tmp',
				'type'  => 'image/png',
				'size'  => 8192,
				'error' => UPLOAD_ERR_OK,
			)
		);
		$this->data['test_update'] = array(
			'id' => 1,
			'photo' => array(
				'name'  => 'NewPhoto.png',
				'tmp_name'  => 'PhotoTmp.png',
				'dir'   => '/tmp/php/file.tmp',
				'type'  => 'image/png',
				'size'  => 8192,
				'error' => UPLOAD_ERR_OK,
			)
		);
		$this->data['test_update_other_field'] = array(
			'id' => 1,
			'other_field' => 'test',
			'photo' => array()
		);
		$this->data['test_remove'] = array(
			'photo' => array(
				'remove' => true,
			)
		);
	}

	function mockUpload($methods = array()) {
		if (!is_array($methods)) {
			$methods = (array) $methods;
		}
		if (empty($methods)) {
			$methods = array('handleUploadedFile', 'unlink', '_getMimeType', '_createThumbnails');
		}
		$this->MockUpload = $this->getMock('UploadBehavior', $methods);


		$this->MockUpload->setup($this->TestUpload, $this->TestUpload->actsAs['Upload.Upload']);
		$this->TestUpload->Behaviors->set('Upload', $this->MockUpload);

		$this->MockUpload->setup($this->TestUploadTwo, $this->TestUploadTwo->actsAs['Upload.Upload']);
		$this->TestUploadTwo->Behaviors->set('Upload', $this->MockUpload);
	}

	function endTest($method) {
		$folder = new Folder(TMP);
		$folder->delete(ROOT . DS . APP_DIR . DS . 'webroot' . DS . 'files' . DS . 'test_upload');
		$folder->delete(ROOT . DS . APP_DIR . DS . 'tmp' . DS . 'tests' . DS . 'path');
		Classregistry::flush();
		unset($this->TestUpload);
		unset($this->TestUploadTwo);
	}

	function testSetup() {
		$this->mockUpload(array('handleUploadedFile', 'unlink'));
		$this->assertEqual('_resizeImagick', $this->MockUpload->settings['TestUpload']['photo']['thumbnailMethod']);
		$this->assertEqual('_getPathPrimaryKey', $this->MockUpload->settings['TestUpload']['photo']['pathMethod']);
	}

	function testUploadSettings() {
		$this->mockUpload(array('handleUploadedFile', 'unlink'));
		$this->assertEqual('_resizeImagick', $this->MockUpload->settings['TestUpload']['photo']['thumbnailMethod']);
		$this->assertEqual('_getPathPrimaryKey', $this->MockUpload->settings['TestUpload']['photo']['pathMethod']);

		$this->TestUpload->uploadSettings('photo', 'thumbnailMethod', '_resizePhp');
		$this->assertEqual('_resizePhp', $this->MockUpload->settings['TestUpload']['photo']['thumbnailMethod']);
		$this->assertEqual('_getPathPrimaryKey', $this->MockUpload->settings['TestUpload']['photo']['pathMethod']);

		$this->TestUpload->uploadSettings('photo', array(
			'thumbnailMethod' => '_resizeImagick',
			'pathMethod' => '_getPathFlat',
		));
		$this->assertEqual('_resizeImagick', $this->MockUpload->settings['TestUpload']['photo']['thumbnailMethod']);
		$this->assertEqual('_getPathFlat', $this->MockUpload->settings['TestUpload']['photo']['pathMethod']);

		$this->TestUpload->uploadSettings('photo', array('pathMethod', 'thumbnailQuality'), array('_getPathPrimaryKey', 100));
		$this->assertEqual('_resizeImagick', $this->MockUpload->settings['TestUpload']['photo']['thumbnailMethod']);
		$this->assertEqual('_getPathPrimaryKey', $this->MockUpload->settings['TestUpload']['photo']['pathMethod']);
		$this->assertEqual(100, $this->MockUpload->settings['TestUpload']['photo']['thumbnailQuality']);
	}

	function testFileSize() {
		$this->mockUpload();
		$this->MockUpload->expects($this->once())->method('handleUploadedFile')->will($this->returnValue(true));
		$result = $this->TestUpload->save($this->data['test_ok']);
		$this->assertInternalType('array', $result);
		$newRecord = $this->TestUpload->findById($this->TestUpload->id);
		$this->assertEqual($this->data['test_ok']['photo']['size'], $newRecord['TestUpload']['size']);
	}

	function testSimpleUpload() {
		$this->mockUpload();
		$this->MockUpload->expects($this->once())->method('handleUploadedFile')->will($this->returnValue(true));
		$this->MockUpload->expects($this->never())->method('unlink');
		$this->MockUpload->expects($this->once())->method('handleUploadedFile')->with(
			$this->TestUpload->alias,
			'photo',
			$this->data['test_ok']['photo']['tmp_name'],
			$this->MockUpload->settings['TestUpload']['photo']['path'] . 2 . DS . $this->data['test_ok']['photo']['name']
		);
		$result = $this->TestUpload->save($this->data['test_ok']);
		$this->assertInternalType('array', $result);
		$newRecord = $this->TestUpload->findById($this->TestUpload->id);
		$expectedRecord = array(
			'TestUpload' => array(
				'id' => 2,
				'photo' => 'Photo.png',
				'dir' => 2,
				'type' => 'image/png',
				'size' => 8192,
				'other_field' => null
			)
		);

		$this->assertEqual($expectedRecord, $newRecord);
	}

	/**
	 * Tests Upload::save creates a new Upload record including
	 * an upload of an PNG image file using the Upload.Upload behavior
	 * with the default path and pathMethod (primaryKey)
	 */
	public function testSaveSuccessPngDefaultPathAndPathMethod() {
		$this->mockUpload();
		$next_id = (1+$this->TestUploadTwo->field('id', array(), array('TestUploadTwo.id' => 'DESC')));
		$destination_dir = APP . 'webroot' . DS . 'files' . DS . 'test_upload_two' . DS . 'photo' . DS . $next_id . DS;

		$Upload = array(
			'TestUploadTwo' => array(
				'photo' => array(
					'name' => 'image-png.png',
					'type' => 'image/png',
					'tmp_name' => 'image-png-tmp.png',
					'error' => UPLOAD_ERR_OK,
					'size' => 8123,
				)
			)
		);

		$this->MockUpload->expects($this->never())
			->method('unlink');

		$this->MockUpload->expects($this->once())
			->method('handleUploadedFile')
			->with(
					$this->equalTo('TestUploadTwo'),
					$this->equalTo('photo'),
					$this->equalTo('image-png-tmp.png'),
					$this->equalTo($destination_dir . 'image-png.png')
			)
			->will($this->returnValue(true));

		$this->MockUpload->expects($this->once())
			->method('_createThumbnails')
			->with(
					$this->isInstanceOf('TestUploadTwo'),
					$this->equalTo('photo'),
					$this->equalTo($destination_dir),
					$this->equalTo($destination_dir)
			)
			->will($this->returnValue(true));

		$this->assertTrue(false !== $this->TestUploadTwo->save($Upload));
		$this->assertSame(array(), array_keys($this->TestUploadTwo->validationErrors));

		$this->assertSame('image-png.png', $this->TestUploadTwo->field('photo', array('TestUploadTwo.id' => $next_id)));
		$this->assertSame('image/png', $this->TestUploadTwo->field('type', array('TestUploadTwo.id' => $next_id)));
		$this->assertSame((string)$next_id, $this->TestUploadTwo->field('dir', array('TestUploadTwo.id' => $next_id)));
	}

	function testDeleteOnUpdate() {
		$this->TestUpload->actsAs['Upload.Upload']['photo']['deleteOnUpdate'] = true;
		$this->mockUpload();
		$this->MockUpload->expects($this->once())->method('handleUploadedFile')->will($this->returnValue(true));
		$this->MockUpload->expects($this->once())->method('unlink')->will($this->returnValue(true));

		$existingRecord = $this->TestUpload->findById($this->data['test_update']['id']);
		$this->MockUpload->expects($this->once())->method('unlink')->with(
			$this->MockUpload->settings['TestUpload']['photo']['path'] . $existingRecord['TestUpload']['dir'] . DS . $existingRecord['TestUpload']['photo']
		);
		$this->MockUpload->expects($this->once())->method('handleUploadedFile')->with(
			$this->TestUpload->alias,
			'photo',
			$this->data['test_update']['photo']['tmp_name'],
			$this->MockUpload->settings['TestUpload']['photo']['path'] . $this->data['test_update']['id'] . DS . $this->data['test_update']['photo']['name']
		);
		$result = $this->TestUpload->save($this->data['test_update']);
		$this->assertInternalType('array', $result);
	}

	function testDeleteOnUpdateWithoutNewUpload() {
		$this->TestUpload->actsAs['Upload.Upload']['photo']['deleteOnUpdate'] = true;
		$this->mockUpload();
		$this->MockUpload->expects($this->never())->method('unlink');
		$this->MockUpload->expects($this->never())->method('handleUploadedFile');
		$result = $this->TestUpload->save($this->data['test_update_other_field']);
		$this->assertInternalType('array', $result);
		$newRecord = $this->TestUpload->findById($this->TestUpload->id);
		$this->assertEqual($this->data['test_update_other_field']['other_field'], $newRecord['TestUpload']['other_field']);
	}

	function testUpdateWithoutNewUpload() {
		$this->mockUpload();
		$this->MockUpload->expects($this->never())->method('unlink');
		$this->MockUpload->expects($this->never())->method('handleUploadedFile');
		$result = $this->TestUpload->save($this->data['test_update_other_field']);
		$this->assertInternalType('array', $result);
		$newRecord = $this->TestUpload->findById($this->TestUpload->id);
		$this->assertEqual($this->data['test_update_other_field']['other_field'], $newRecord['TestUpload']['other_field']);
	}

	function testUnlinkFileOnDelete() {
		$this->mockUpload();
		$this->MockUpload->expects($this->once())->method('unlink')->will($this->returnValue(true));
		$existingRecord = $this->TestUpload->findById($this->data['test_update']['id']);
		$this->MockUpload->expects($this->once())->method('unlink')->with(
			$this->MockUpload->settings['TestUpload']['photo']['path'] . $existingRecord['TestUpload']['dir'] . DS . $existingRecord['TestUpload']['photo']
		);
		$result = $this->TestUpload->delete($this->data['test_update']['id']);
		$this->assertTrue($result);
		$this->assertEmpty($this->TestUpload->findById($this->data['test_update']['id']));
	}

	function testDeleteFileOnTrueRemoveSave() {
		$this->mockUpload();
		$this->MockUpload->expects($this->once())->method('unlink')->will($this->returnValue(true));

		$data = array(
			'id' => 1,
			'photo' => array(
				'remove' => true
			)
		);

		$existingRecord = $this->TestUpload->findById($data['id']);
		$this->MockUpload->expects($this->once())->method('unlink')->with(
			$this->MockUpload->settings['TestUpload']['photo']['path'] . $existingRecord['TestUpload']['dir'] . DS . $existingRecord['TestUpload']['photo']
		);
		$result = $this->TestUpload->save($data);
		$this->assertInternalType('array', $result);
	}
	
	function testKeepFileOnFalseRemoveSave() {
		$this->mockUpload();
		$this->MockUpload->expects($this->never())->method('unlink');

		$data = array(
			'id' => 1,
			'photo' => array(
				'remove' => false
			)
		);

		$existingRecord = $this->TestUpload->findById($data['id']);
		$result = $this->TestUpload->save($data);
		$this->assertInternalType('array', $result);
	}
	
	function testKeepFileOnNullRemoveSave() {
		$this->mockUpload();
		$this->MockUpload->expects($this->never())->method('unlink');

		$data = array(
			'id' => 1,
			'photo' => array(
				'remove' => null
			)
		);

		$existingRecord = $this->TestUpload->findById($data['id']);
		$result = $this->TestUpload->save($data);
		$this->assertInternalType('array', $result);
	}

	/**
	 * @expectedException UploadException
	 */
	function testMoveFileExecption() {
		$this->mockUpload(array('handleUploadedFile'));
		$this->MockUpload->expects($this->once())->method('handleUploadedFile')->will($this->returnValue(false));
		$result = $this->TestUpload->save($this->data['test_ok']);
	}

	function testIsUnderPhpSizeLimit() {
		$this->TestUpload->validate = array(
			'photo' => array(
				'isUnderPhpSizeLimit' => array(
					'rule' => 'isUnderPhpSizeLimit',
					'message' => 'isUnderPhpSizeLimit'
				),
			)
		);

		$data = array(
			'photo' => array(
				'tmp_name'  => 'Photo.png',
				'dir'   => '/tmp/php/file.tmp',
				'type'  => 'image/png',
				'size'  => 8192,
				'error' => UPLOAD_ERR_INI_SIZE,
			)
		);
		$this->TestUpload->set($data);
		$this->assertFalse($this->TestUpload->validates());
		$this->assertEqual(1, count($this->TestUpload->validationErrors));
		$this->assertEqual('isUnderPhpSizeLimit', current($this->TestUpload->validationErrors['photo']));

		$this->TestUpload->set($this->data['test_ok']);
		$this->assertTrue($this->TestUpload->validates());
		$this->assertEqual(0, count($this->TestUpload->validationErrors));

		$this->TestUpload->set($this->data['test_remove']);
		$this->assertTrue($this->TestUpload->validates());
		$this->assertEqual(0, count($this->TestUpload->validationErrors));
	}

	function testIsUnderFormSizeLimit() {
		$this->TestUpload->validate = array(
			'photo' => array(
				'isUnderFormSizeLimit' => array(
					'rule' => 'isUnderFormSizeLimit',
					'message' => 'isUnderFormSizeLimit'
				),
			)
		);

		$data = array(
			'photo' => array(
				'tmp_name'  => 'Photo.png',
				'dir'   => '/tmp/php/file.tmp',
				'type'  => 'image/png',
				'size'  => 8192,
				'error' => UPLOAD_ERR_FORM_SIZE,
			)
		);
		$this->TestUpload->set($data);
		$this->assertFalse($this->TestUpload->validates());
		$this->assertEqual(1, count($this->TestUpload->validationErrors));
		$this->assertEqual('isUnderFormSizeLimit', current($this->TestUpload->validationErrors['photo']));

		$this->TestUpload->set($this->data['test_ok']);
		$this->assertTrue($this->TestUpload->validates());
		$this->assertEqual(0, count($this->TestUpload->validationErrors));

		$this->TestUpload->set($this->data['test_remove']);
		$this->assertTrue($this->TestUpload->validates());
		$this->assertEqual(0, count($this->TestUpload->validationErrors));
	}

	function testIsCompletedUpload() {
		$this->TestUpload->validate = array(
			'photo' => array(
				'isCompletedUpload' => array(
					'rule' => 'isCompletedUpload',
					'message' => 'isCompletedUpload'
				),
			)
		);

		$data = array(
			'photo' => array(
				'tmp_name'  => 'Photo.png',
				'dir'   => '/tmp/php/file.tmp',
				'type'  => 'image/png',
				'size'  => 8192,
				'error' => UPLOAD_ERR_PARTIAL,
			)
		);
		$this->TestUpload->set($data);
		$this->assertFalse($this->TestUpload->validates());
		$this->assertEqual(1, count($this->TestUpload->validationErrors));
		$this->assertEqual('isCompletedUpload', current($this->TestUpload->validationErrors['photo']));

		$this->TestUpload->set($this->data['test_ok']);
		$this->assertTrue($this->TestUpload->validates());
		$this->assertEqual(0, count($this->TestUpload->validationErrors));

		$this->TestUpload->set($this->data['test_remove']);
		$this->assertTrue($this->TestUpload->validates());
		$this->assertEqual(0, count($this->TestUpload->validationErrors));
	}

	function testIsFileUpload() {
		$this->TestUpload->validate = array(
			'photo' => array(
				'isFileUpload' => array(
					'rule' => 'isFileUpload',
					'message' => 'isFileUpload'
				),
			)
		);

		$data = array(
			'photo' => array(
				'tmp_name'  => 'Photo.png',
				'dir'   => '/tmp/php/file.tmp',
				'type'  => 'image/png',
				'size'  => 8192,
				'error' => UPLOAD_ERR_NO_FILE,
			)
		);
		$this->TestUpload->set($data);
		$this->assertFalse($this->TestUpload->validates());
		$this->assertEqual(1, count($this->TestUpload->validationErrors));
		$this->assertEqual('isFileUpload', current($this->TestUpload->validationErrors['photo']));

		$this->TestUpload->set($this->data['test_ok']);
		$this->assertTrue($this->TestUpload->validates());
		$this->assertEqual(0, count($this->TestUpload->validationErrors));

		$this->TestUpload->set($this->data['test_remove']);
		$this->assertTrue($this->TestUpload->validates());
		$this->assertEqual(0, count($this->TestUpload->validationErrors));
	}

	function testTempDirExists() {
		$this->TestUpload->validate = array(
			'photo' => array(
				'tempDirExists' => array(
					'rule' => 'tempDirExists',
					'message' => 'tempDirExists'
				),
			)
		);

		$data = array(
			'photo' => array(
				'tmp_name'  => 'Photo.png',
				'dir'   => '/tmp/php/file.tmp',
				'type'  => 'image/png',
				'size'  => 8192,
				'error' => UPLOAD_ERR_NO_TMP_DIR,
			)
		);
		$this->TestUpload->set($data);
		$this->assertFalse($this->TestUpload->validates());
		$this->assertEqual(1, count($this->TestUpload->validationErrors));
		$this->assertEqual('tempDirExists', current($this->TestUpload->validationErrors['photo']));

		$this->TestUpload->set($this->data['test_ok']);
		$this->assertTrue($this->TestUpload->validates());
		$this->assertEqual(0, count($this->TestUpload->validationErrors));

		$this->TestUpload->set($this->data['test_remove']);
		$this->assertTrue($this->TestUpload->validates());
		$this->assertEqual(0, count($this->TestUpload->validationErrors));
	}

	function testIsSuccessfulWrite() {
		$this->TestUpload->validate = array(
			'photo' => array(
				'isSuccessfulWrite' => array(
					'rule' => 'isSuccessfulWrite',
					'message' => 'isSuccessfulWrite'
				),
			)
		);

		$data = array(
			'photo' => array(
				'tmp_name'  => 'Photo.png',
				'dir'   => '/tmp/php/file.tmp',
				'type'  => 'image/png',
				'size'  => 8192,
				'error' => UPLOAD_ERR_CANT_WRITE,
			)
		);
		$this->TestUpload->set($data);
		$this->assertFalse($this->TestUpload->validates());
		$this->assertEqual(1, count($this->TestUpload->validationErrors));
		$this->assertEqual('isSuccessfulWrite', current($this->TestUpload->validationErrors['photo']));

		$this->TestUpload->set($this->data['test_ok']);
		$this->assertTrue($this->TestUpload->validates());
		$this->assertEqual(0, count($this->TestUpload->validationErrors));

		$this->TestUpload->set($this->data['test_remove']);
		$this->assertTrue($this->TestUpload->validates());
		$this->assertEqual(0, count($this->TestUpload->validationErrors));
	}

	function testNoPhpExtensionErrors() {
		$this->TestUpload->validate = array(
			'photo' => array(
				'noPhpExtensionErrors' => array(
					'rule' => 'noPhpExtensionErrors',
					'message' => 'noPhpExtensionErrors'
				),
			)
		);

		$data = array(
			'photo' => array(
				'tmp_name'  => 'Photo.png',
				'dir'   => '/tmp/php/file.tmp',
				'type'  => 'image/png',
				'size'  => 8192,
				'error' => UPLOAD_ERR_EXTENSION,
			)
		);
		$this->TestUpload->set($data);
		$this->assertFalse($this->TestUpload->validates());
		$this->assertEqual(1, count($this->TestUpload->validationErrors));
		$this->assertEqual('noPhpExtensionErrors', current($this->TestUpload->validationErrors['photo']));

		$this->TestUpload->set($this->data['test_ok']);
		$this->assertTrue($this->TestUpload->validates());
		$this->assertEqual(0, count($this->TestUpload->validationErrors));

		$this->TestUpload->set($this->data['test_remove']);
		$this->assertTrue($this->TestUpload->validates());
		$this->assertEqual(0, count($this->TestUpload->validationErrors));
	}

	function testIsValidMimeType() {
		$this->TestUpload->Behaviors->detach('Upload.Upload');
		$this->TestUpload->Behaviors->attach('Upload.Upload', array(
			'photo' => array(
				'mimetypes' => array('image/bmp', 'image/jpeg')
			)
		));

		$this->TestUpload->validate = array(
			'photo' => array(
				'isValidMimeType' => array(
					'rule' => 'isValidMimeType',
					'message' => 'isValidMimeType'
				),
			)
		);

		$this->TestUpload->set($this->data['test_ok']);
		$this->assertFalse($this->TestUpload->validates());
		$this->assertEqual(1, count($this->TestUpload->validationErrors));
		$this->assertEqual('isValidMimeType', current($this->TestUpload->validationErrors['photo']));

		$this->TestUpload->Behaviors->detach('Upload.Upload');
		$this->TestUpload->Behaviors->attach('Upload.Upload', array(
			'photo' => array(
				'mimetypes' => array('image/png', 'image/jpeg')
			)
		));

		$this->TestUpload->set($this->data['test_ok']);
		$this->assertTrue($this->TestUpload->validates());
		$this->assertEqual(0, count($this->TestUpload->validationErrors));

		$this->TestUpload->set($this->data['test_remove']);
		$this->assertTrue($this->TestUpload->validates());
		$this->assertEqual(0, count($this->TestUpload->validationErrors));

		$this->TestUpload->validate = array(
			'photo' => array(
				'isValidMimeType' => array(
					'rule' => array('isValidMimeType', 'image/png'),
					'message' => 'isValidMimeType',
				),
			)
		);

		$this->TestUpload->set($this->data['test_ok']);
		$this->assertTrue($this->TestUpload->validates());
		$this->assertEqual(0, count($this->TestUpload->validationErrors));
	}

	function testIsValidExtension() {
		$this->TestUpload->Behaviors->detach('Upload.Upload');
		$this->TestUpload->Behaviors->attach('Upload.Upload', array(
			'photo' => array(
				'extensions' => array('jpeg', 'bmp')
			)
		));

		$this->TestUpload->validate = array(
			'photo' => array(
				'isValidExtension' => array(
					'rule' => 'isValidExtension',
					'message' => 'isValidExtension'
				),
			)
		);

		$this->TestUpload->set($this->data['test_ok']);
		$this->assertFalse($this->TestUpload->validates());
		$this->assertEqual(1, count($this->TestUpload->validationErrors));
		$this->assertEqual('isValidExtension', current($this->TestUpload->validationErrors['photo']));

		$data = $this->data['test_ok'];
		$data['photo']['name'] = 'Photo.bmp';
		$this->TestUpload->set($data);
		$this->assertTrue($this->TestUpload->validates());
		$this->assertEqual(0, count($this->TestUpload->validationErrors));

		$this->TestUpload->Behaviors->detach('Upload.Upload');
		$this->TestUpload->Behaviors->attach('Upload.Upload', array(
			'photo'
		));

		$this->TestUpload->validate['photo']['isValidExtension']['rule'] = array('isValidExtension', 'jpg');
		$this->TestUpload->set($this->data['test_ok']);
		$this->assertFalse($this->TestUpload->validates());
		$this->assertEqual(1, count($this->TestUpload->validationErrors));
		$this->assertEqual('isValidExtension', current($this->TestUpload->validationErrors['photo']));

		$this->TestUpload->validate['photo']['isValidExtension']['rule'] = array('isValidExtension', array('jpg'));
		$this->TestUpload->set($this->data['test_ok']);
		$this->assertFalse($this->TestUpload->validates());
		$this->assertEqual(1, count($this->TestUpload->validationErrors));
		$this->assertEqual('isValidExtension', current($this->TestUpload->validationErrors['photo']));

		$this->TestUpload->validate['photo']['isValidExtension']['rule'] = array('isValidExtension', array('jpg', 'bmp'));
		$this->TestUpload->set($this->data['test_ok']);
		$this->assertFalse($this->TestUpload->validates());
		$this->assertEqual(1, count($this->TestUpload->validationErrors));
		$this->assertEqual('isValidExtension', current($this->TestUpload->validationErrors['photo']));

		$this->TestUpload->validate['photo']['isValidExtension']['rule'] = array('isValidExtension', array('jpg', 'bmp', 'png'));
		$this->TestUpload->set($this->data['test_ok']);
		$this->assertTrue($this->TestUpload->validates());
		$this->assertEqual(0, count($this->TestUpload->validationErrors));

		$this->TestUpload->validate = array(
			'photo' => array(
				'isFileUpload' => array(
					'rule' => 'isFileUpload',
					'message' => 'isFileUpload'
				),
				'isValidExtension' => array(
					'rule' => array('isValidExtension', array('jpg')),
					'message' => 'isValidExtension'
				),
			)
		);

		$this->TestUpload->set($this->data['test_ok']);
		$this->assertFalse($this->TestUpload->validates());
		$this->assertEqual(1, count($this->TestUpload->validationErrors));
		$this->assertEqual('isValidExtension', current($this->TestUpload->validationErrors['photo']));

		$data['photo']['name'] = 'Photo.jpg';
		$this->TestUpload->set($this->data['test_ok']);
		$this->assertFalse($this->TestUpload->validates());
		$this->assertEqual(1, count($this->TestUpload->validationErrors));
		$this->assertEqual('isValidExtension', current($this->TestUpload->validationErrors['photo']));

		$this->TestUpload->set($this->data['test_remove']);
		$this->assertTrue($this->TestUpload->validates());
		$this->assertEqual(0, count($this->TestUpload->validationErrors));
	}

	function testIsWritable() {
		$this->TestUpload->validate = array(
			'photo' => array(
				'isWritable' => array(
					'rule' => 'isWritable',
					'message' => 'isWritable'
				),
			)
		);

		$this->TestUpload->set($this->data['test_ok']);
		$this->assertFalse($this->TestUpload->validates());

		$this->assertEqual(1, count($this->TestUpload->validationErrors));
		$this->assertEqual('isWritable', current($this->TestUpload->validationErrors['photo']));

		$this->TestUpload->Behaviors->detach('Upload.Upload');
		$this->TestUpload->Behaviors->attach('Upload.Upload', array(
			'photo' => array(
				'path' => TMP
			)
		));

		$data = array(
			'photo' => array(
				'tmp_name'  => 'Photo.bmp',
				'dir'   => '/tmp/php/file.tmp',
				'type'  => 'image/bmp',
				'size'  => 8192,
				'error' => UPLOAD_ERR_OK,
			)
		);
		$this->TestUpload->set($data);
		$this->assertTrue($this->TestUpload->validates());
		$this->assertEqual(0, count($this->TestUpload->validationErrors));

		$this->TestUpload->set($this->data['test_remove']);
		$this->assertTrue($this->TestUpload->validates());
		$this->assertEqual(0, count($this->TestUpload->validationErrors));
	}

	function testIsValidDir() {
		$this->TestUpload->validate = array(
			'photo' => array(
				'isValidDir' => array(
					'rule' => 'isValidDir',
					'message' => 'isValidDir'
				),
			)
		);

		$this->TestUpload->set($this->data['test_ok']);
		$this->assertFalse($this->TestUpload->validates());

		$this->assertEqual(1, count($this->TestUpload->validationErrors));
		$this->assertEqual('isValidDir', current($this->TestUpload->validationErrors['photo']));

		$this->TestUpload->Behaviors->detach('Upload.Upload');
		$this->TestUpload->Behaviors->attach('Upload.Upload', array(
			'photo' => array(
				'path' => TMP
			)
		));

		$data = array(
			'photo' => array(
				'tmp_name'  => 'Photo.bmp',
				'dir'   => '/tmp/php/file.tmp',
				'type'  => 'image/bmp',
				'size'  => 8192,
				'error' => UPLOAD_ERR_OK,
			)
		);
		$this->TestUpload->set($data);
		$this->assertTrue($this->TestUpload->validates());
		$this->assertEqual(0, count($this->TestUpload->validationErrors));

		$this->TestUpload->set($this->data['test_remove']);
		$this->assertTrue($this->TestUpload->validates());
		$this->assertEqual(0, count($this->TestUpload->validationErrors));
	}

	function testIsImage() {
		$this->TestUpload->Behaviors->detach('Upload.Upload');
		$this->TestUpload->Behaviors->attach('Upload.Upload', array(
			'photo' => array(
				'mimetypes' => array('image/bmp', 'image/jpeg')
			)
		));

		$result = $this->TestUpload->Behaviors->Upload->_isImage($this->TestUpload, 'image/bmp');
		$this->assertTrue($result);

		$result = $this->TestUpload->Behaviors->Upload->_isImage($this->TestUpload, 'image/jpeg');
		$this->assertTrue($result);

		$result = $this->TestUpload->Behaviors->Upload->_isImage($this->TestUpload, 'application/zip');
		$this->assertFalse($result);
	}

	function testIsMedia() {
		$this->TestUpload->Behaviors->detach('Upload.Upload');
		$this->TestUpload->Behaviors->attach('Upload.Upload', array(
			'pdf_file' => array(
				'mimetypes' => array('application/pdf', 'application/postscript')
			)
		));

		$result = $this->TestUpload->Behaviors->Upload->_isMedia($this->TestUpload, 'application/pdf');
		$this->assertTrue($result);

		$result = $this->TestUpload->Behaviors->Upload->_isMedia($this->TestUpload, 'application/postscript');
		$this->assertTrue($result);

		$result = $this->TestUpload->Behaviors->Upload->_isMedia($this->TestUpload, 'application/zip');
		$this->assertFalse($result);

		$result = $this->TestUpload->Behaviors->Upload->_isMedia($this->TestUpload, 'image/jpeg');
		$this->assertFalse($result);
	}

	function testGetPathFlat() {
		$basePath = 'tests' . DS . 'path' . DS . 'flat' . DS;
		$result = $this->TestUpload->Behaviors->Upload->_getPathFlat($this->TestUpload, 'photo', TMP . $basePath);

		$this->assertInternalType('string', $result);
		$this->assertEqual(0, strlen($result));
	}

	function testGetPathPrimaryKey() {
		$this->TestUpload->id = 5;
		$basePath = 'tests' . DS . 'path' . DS . 'primaryKey' . DS;
		$result = $this->TestUpload->Behaviors->Upload->_getPathPrimaryKey($this->TestUpload, 'photo', TMP . $basePath);

		$this->assertInternalType('integer', $result);
		$this->assertEqual(1, strlen($result));
		$this->assertEqual($result, $this->TestUpload->id);
		$this->assertTrue(is_dir(TMP . $basePath . $result));
	}

	function testGetPathRandom() {
		$basePath = 'tests' . DS . 'path' . DS . 'random' . DS;
		$result = $this->TestUpload->Behaviors->Upload->_getPathRandom($this->TestUpload, 'photo', TMP . $basePath);

		$this->assertInternalType('string', $result);
		$this->assertEqual(8, strlen($result));
		$this->assertTrue(is_dir(TMP . $basePath . $result));
	}

	function testReplacePath() {
		$result = $this->TestUpload->Behaviors->Upload->_path($this->TestUpload, 'photo', array(
			'path' => 'webroot{DS}files/{model}\\{field}{DS}',
		));

		$this->assertInternalType('string', $result);
		$this->assertEqual(WWW_ROOT . 'files' . DIRECTORY_SEPARATOR . 'test_upload' . DIRECTORY_SEPARATOR . 'photo' . DIRECTORY_SEPARATOR, $result);

		$result = $this->TestUpload->Behaviors->Upload->_path($this->TestUpload, 'photo', array(
			'path' => 'webroot{DS}files//{size}/{model}\\{field}{DS}{geometry}///',
		));

		$this->assertInternalType('string', $result);
		$this->assertEqual(WWW_ROOT . 'files' . DIRECTORY_SEPARATOR . '{size}' . DIRECTORY_SEPARATOR . 'test_upload' . DIRECTORY_SEPARATOR . 'photo' . DIRECTORY_SEPARATOR . '{geometry}' . DIRECTORY_SEPARATOR, $result);


		$result = $this->TestUpload->Behaviors->Upload->_path($this->TestUpload, 'photo', array(
			'isThumbnail' => false,
			'path' => 'webroot{DS}files//{size}/{model}\\\\{field}{DS}{geometry}///',
		));

		$this->assertInternalType('string', $result);
		$this->assertEqual(WWW_ROOT . 'files' . DIRECTORY_SEPARATOR . 'test_upload' . DIRECTORY_SEPARATOR . 'photo' . DIRECTORY_SEPARATOR, $result);
	}

	function testPrepareFilesForDeletion() {
		$this->TestUpload->actsAs['Upload.Upload'] = array(
			'photo' => array(
				'thumbnailSizes' => array(
					'xvga' => '1024x768',
					'vga' => '640x480',
					'thumb' => '80x80'
				),
				'fields' => array(
					'dir' => 'dir'
				)
			)
		);
		$this->mockUpload();
		$this->MockUpload->expects($this->once())->method('_getMimeType')->will($this->returnValue('image/png'));

		$result = $this->TestUpload->Behaviors->Upload->_prepareFilesForDeletion(
			$this->TestUpload, 'photo',
			array('TestUpload' => array('id' => 1, 'dir' => '1', 'photo' => 'Photo.png')),
			$this->TestUpload->Behaviors->Upload->settings['TestUpload']['photo']
		);

		$this->assertInternalType('array', $result);
		$this->assertEqual(1,count($result));
		$this->assertEqual(4, count($result['TestUpload']));
	}

	function testPrepareFilesForDeletionWithThumbnailType() {
		$this->TestUpload->actsAs['Upload.Upload'] = array(
			'photo' => array(
				'thumbnailSizes' => array(
					'xvga' => '1024x768',
					'vga' => '640x480',
					'thumb' => '80x80'
				),
				'fields' => array(
					'dir' => 'dir'
				),
				'thumbnailType' => 'jpg'
			)
		);
		$this->mockUpload();
		$this->MockUpload->expects($this->once())->method('_getMimeType')->will($this->returnValue('image/png'));

		$result = $this->TestUpload->Behaviors->Upload->_prepareFilesForDeletion(
			$this->TestUpload, 'photo',
			array('TestUpload' => array('id' => 1, 'dir' => '1', 'photo' => 'Photo.png')),
			$this->TestUpload->Behaviors->Upload->settings['TestUpload']['photo']
		);

		$this->assertInternalType('array', $result);
		$this->assertEqual(1,count($result));
		$this->assertEqual(4, count($result['TestUpload']));
	}

	function testPrepareFilesForDeletionWithMediaFileAndFalseThumbnailType() {
		$this->TestUpload->actsAs['Upload.Upload'] = array(
			'photo' => array(
				'thumbnailSizes' => array(
					'xvga' => '1024x768',
					'vga' => '640x480',
					'thumb' => '80x80'
				),
				'fields' => array(
					'dir' => 'dir'
				),
				'thumbnailType' => false
			)
		);
		$this->mockUpload();
		$this->MockUpload->expects($this->once())->method('_getMimeType')->will($this->returnValue('application/pdf'));

		$result = $this->TestUpload->Behaviors->Upload->_prepareFilesForDeletion(
			$this->TestUpload, 'photo',
			array('TestUpload' => array('id' => 1, 'dir' => '1', 'photo' => 'Photo.pdf')),
			$this->TestUpload->Behaviors->Upload->settings['TestUpload']['photo']
		);

		$this->assertInternalType('array', $result);
		$this->assertEqual(1,count($result));
		$this->assertEqual(4, count($result['TestUpload']));
	}

	function testPrepareFilesForDeletionWithMediaFile() {
		$this->TestUpload->actsAs['Upload.Upload'] = array(
			'photo' => array(
				'thumbnailSizes' => array(
					'xvga' => '1024x768',
					'vga' => '640x480',
					'thumb' => '80x80'
				),
				'fields' => array(
					'dir' => 'dir'
				)
			)
		);
		$this->mockUpload();
		$this->MockUpload->expects($this->once())->method('_getMimeType')->will($this->returnValue('application/pdf'));

		$result = $this->TestUpload->Behaviors->Upload->_prepareFilesForDeletion(
			$this->TestUpload, 'photo',
			array('TestUpload' => array('id' => 1, 'dir' => '1', 'photo' => 'Photo.pdf')),
			$this->TestUpload->Behaviors->Upload->settings['TestUpload']['photo']
		);

		$this->assertInternalType('array', $result);
		$this->assertEqual(1,count($result));
		$this->assertEqual(4, count($result['TestUpload']));
	}

}
