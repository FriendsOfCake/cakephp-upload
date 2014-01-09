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

	public function startTest($method) {
		$this->TestUpload = ClassRegistry::init('TestUpload');
		$this->TestUploadTwo = ClassRegistry::init('TestUploadTwo');
		$this->currentTestMethod = $method;
		$this->data['test_ok'] = array(
			'photo' => array(
				'name' => 'Photo.png',
				'tmp_name' => 'Photo.png',
				'dir' => '/tmp/php/file.tmp',
				'type' => 'image/png',
				'size' => 8192,
				'error' => UPLOAD_ERR_OK,
			)
		);
		$this->data['test_ok_bmp'] = array(
			'photo' => array(
				'name' => 'Photo.bmp',
				'tmp_name' => 'Photo.bmp',
				'dir' => '/tmp/php/file.tmp',
				'type' => 'image/bmp',
				'size' => 8192,
				'error' => UPLOAD_ERR_OK,
			)
		);
		$this->data['test_update'] = array(
			'id' => 1,
			'photo' => array(
				'name' => 'NewPhoto.png',
				'tmp_name' => 'PhotoTmp.png',
				'dir' => '/tmp/php/file.tmp',
				'type' => 'image/png',
				'size' => 8192,
				'error' => UPLOAD_ERR_OK,
			)
		);
		$this->data['test_update_other_field'] = array(
			'id' => 1,
			'other_field' => 'test',
			'photo' => array()
		);
		$this->data['test_update_other_field_without_photo_set'] = array(
			'id' => 1,
			'other_field' => 'test',
		);
		$this->data['test_remove'] = array(
			'photo' => array(
				'remove' => true,
			)
		);
	}

	public function mockUpload($methods = array()) {
		if (!is_array($methods)) {
			$methods = (array)$methods;
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

	public function protectedMethodCall($obj, $name, array $args) {
		$class = new \ReflectionClass($obj);
		$method = $class->getMethod($name);
		$method->setAccessible(true);
		return $method->invokeArgs($obj, $args);
	}

	public function endTest($method) {
		$folder = new Folder(TMP);
		$folder->delete(ROOT . DS . APP_DIR . DS . 'webroot' . DS . 'files' . DS . 'test_upload');
		$folder->delete(ROOT . DS . APP_DIR . DS . 'tmp' . DS . 'tests' . DS . 'path');
		Classregistry::flush();
		unset($this->TestUpload);
		unset($this->TestUploadTwo);
	}

	public function testSetup() {
		$this->mockUpload(array('handleUploadedFile', 'unlink'));
		$this->assertEqual('_resizeImagick', $this->MockUpload->settings['TestUpload']['photo']['thumbnailMethod']);
		$this->assertEqual('_getPathPrimaryKey', $this->MockUpload->settings['TestUpload']['photo']['pathMethod']);
	}

	public function testUploadSettings() {
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

	public function testFileSize() {
		$this->mockUpload();
		$this->MockUpload->expects($this->once())->method('handleUploadedFile')->will($this->returnValue(true));
		$result = $this->TestUpload->save($this->data['test_ok']);
		$this->assertInternalType('array', $result);
		$newRecord = $this->TestUpload->findById($this->TestUpload->id);
		$this->assertEqual($this->data['test_ok']['photo']['size'], $newRecord['TestUpload']['size']);
	}

	public function testSimpleUpload() {
		$this->mockUpload();
		$this->MockUpload->expects($this->once())->method('handleUploadedFile')->will($this->returnValue(true));
		$this->MockUpload->expects($this->never())->method('unlink');
		$this->MockUpload->expects($this->once())->method('handleUploadedFile')->with(
			$this->TestUpload->alias,
			'photo',
			$this->data['test_ok']['photo']['tmp_name'],
			$this->MockUpload->settings['TestUpload']['photo']['path'] . 3 . DS . $this->data['test_ok']['photo']['name']
		);
		$result = $this->TestUpload->save($this->data['test_ok']);
		$this->assertInternalType('array', $result);
		$newRecord = $this->TestUpload->findById($this->TestUpload->id);
		$expectedRecord = array(
			'TestUpload' => array(
				'id' => 3,
				'photo' => 'Photo.png',
				'dir' => 3,
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
		$nextId = (1 + $this->TestUploadTwo->field('id', array(), array('TestUploadTwo.id' => 'DESC')));
		$destinationDir = APP . 'webroot' . DS . 'files' . DS . 'test_upload_two' . DS . 'photo' . DS . $nextId . DS;

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
					$this->equalTo($destinationDir . 'image-png.png')
			)
			->will($this->returnValue(true));

		$this->MockUpload->expects($this->once())
			->method('_createThumbnails')
			->with(
					$this->isInstanceOf('TestUploadTwo'),
					$this->equalTo('photo'),
					$this->equalTo($destinationDir),
					$this->equalTo($destinationDir)
			)
			->will($this->returnValue(true));

		$this->assertTrue(false !== $this->TestUploadTwo->save($Upload));
		$this->assertSame(array(), array_keys($this->TestUploadTwo->validationErrors));

		$this->assertSame('image-png.png', $this->TestUploadTwo->field('photo', array('TestUploadTwo.id' => $nextId)));
		$this->assertSame('image/png', $this->TestUploadTwo->field('type', array('TestUploadTwo.id' => $nextId)));
		$this->assertSame((string)$nextId, $this->TestUploadTwo->field('dir', array('TestUploadTwo.id' => $nextId)));
	}

	public function testSaveDirEscaped() {
		$this->TestUpload->actsAs['Upload.Upload']['photo']['pathMethod'] = 'random';
		$this->mockUpload(array('handleUploadedFile', 'unlink', '_getMimeType', '_createThumbnails', '_getPathRandom'));

		$DS = '\\';
		$randomPath = '01' . $DS . '02' . $DS . '03';

		$this->MockUpload->expects($this->once())->method('handleUploadedFile')->will($this->returnValue(true));
		$this->MockUpload->expects($this->once())->method('_getPathRandom')->will($this->returnValue($randomPath));

		$result = $this->TestUpload->save($this->data['test_ok']);
		$this->assertInternalType('array', $result);
		$newRecord = $this->TestUpload->findById($this->TestUpload->id);

		$this->assertSame($randomPath, $newRecord['TestUpload']['dir']);
	}

	public function testDeleteOnUpdate() {
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

	public function testDeleteOnUpdateWithoutNewUpload() {
		$this->TestUpload->actsAs['Upload.Upload']['photo']['deleteOnUpdate'] = true;
		$this->mockUpload();
		$this->MockUpload->expects($this->never())->method('unlink');
		$this->MockUpload->expects($this->never())->method('handleUploadedFile');
		$result = $this->TestUpload->save($this->data['test_update_other_field']);
		$this->assertInternalType('array', $result);
		$newRecord = $this->TestUpload->findById($this->TestUpload->id);
		$this->assertEqual($this->data['test_update_other_field']['other_field'], $newRecord['TestUpload']['other_field']);
	}

	public function testUpdateWithoutNewUpload() {
		$this->mockUpload();
		$this->MockUpload->expects($this->never())->method('unlink');
		$this->MockUpload->expects($this->never())->method('handleUploadedFile');
		$result = $this->TestUpload->save($this->data['test_update_other_field']);
		$this->assertInternalType('array', $result);
		$newRecord = $this->TestUpload->findById($this->TestUpload->id);
		$this->assertEqual($this->data['test_update_other_field']['other_field'], $newRecord['TestUpload']['other_field']);
	}

	public function testUpdateWithoutNewUploadWithoutFieldSet() {
		$this->mockUpload();
		$this->MockUpload->expects($this->never())->method('unlink');
		$this->MockUpload->expects($this->never())->method('handleUploadedFile');
		$result = $this->TestUpload->save($this->data['test_update_other_field_without_photo_set']);
		$this->assertInternalType('array', $result);
		$newRecord = $this->TestUpload->findById($this->TestUpload->id);
		$this->assertEqual($this->data['test_update_other_field_without_photo_set']['other_field'], $newRecord['TestUpload']['other_field']);
	}

	public function testRuntimeConfigIsResetBetweenSaves() {
		$this->mockUpload();
		$this->MockUpload->expects($this->never())->method('unlink');

		// We'll save the record twice using the same model instance,
		// but handleUploadedFile should only be called once, on the first save.
		$this->MockUpload->expects($this->once())->method('handleUploadedFile')->will($this->returnValue(true));
		$this->TestUpload->save($this->data['test_update']);
		$runtime = $this->TestUpload->Behaviors->Upload->runtime;
		$this->assertInternalType('array', $runtime['TestUpload']['photo']);

		$this->TestUpload->read(null, $this->data['test_update']['id']);
		$this->TestUpload->save(); // intentionally don't pass an uploaded file in save data
		$runtime = $this->TestUpload->Behaviors->Upload->runtime;
		$this->assertFalse(isset($runtime['TestUpload']['photo']));
	}

	public function testUnlinkFileOnDelete() {
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

	public function testDeleteFileOnTrueRemoveSave() {
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

	public function testKeepFileOnFalseRemoveSave() {
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

	public function testKeepFileOnNullRemoveSave() {
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
	public function testMoveFileExecption() {
		$this->mockUpload(array('handleUploadedFile'));
		$this->MockUpload->expects($this->once())->method('handleUploadedFile')->will($this->returnValue(false));
		$result = $this->TestUpload->save($this->data['test_ok']);
	}

/**
 * @dataProvider providerTestValidationRules
 */
	public function testValidationRules($rule, $data, $record = array()) {
		$this->TestUpload->validate = array(
			'photo' => array(
				$rule => array(
					'rule' => $rule,
					'message' => $rule
				),
			)
		);

		$data = array_merge(array(
			'photo' => array_merge(array(
				'tmp_name' => 'Photo.png',
				'dir' => '/tmp/php/file.tmp',
				'type' => 'image/png',
				'size' => 8192,
			), $data
		), $record));

		$this->__testInvalidValidationRule($rule, $data);
		$this->__testOkAndRemoveCasesValidate();
	}

	public function providerTestValidationRules() {
		return array(
			array('isUnderPhpSizeLimit', array('error' => UPLOAD_ERR_INI_SIZE)),
			array('isUnderFormSizeLimit', array('error' => UPLOAD_ERR_FORM_SIZE)),
			array('isCompletedUpload', array('error' => UPLOAD_ERR_PARTIAL)),
			array('isFileUpload', array('error' => UPLOAD_ERR_NO_FILE)),
			array('isFileUploadOrHasExistingValue', array('error' => UPLOAD_ERR_NO_FILE), array('id' => 2)),
			array('isFileUploadOrHasExistingValue', array('error' => UPLOAD_ERR_NO_FILE), array('id' => '')),
			array('isFileUploadOrHasExistingValue', array('error' => UPLOAD_ERR_NO_FILE)),
		);
	}

/**
 * @dataProvider providerTestIgnorableValidationRules
 */
	public function testIgnorableValidationRules($rule, $data, $record = array()) {
		$this->TestUpload->validate = array(
			'photo' => array(
				$rule => array(
					'rule' => $rule,
					'message' => $rule
				),
			)
		);

		$data = array_merge(array(
			'photo' => array_merge(array(
				'tmp_name' => 'Photo.png',
				'dir' => '/tmp/php/file.tmp',
				'type' => 'image/png',
				'size' => 8192,
			), $data
		), $record));
		$this->__testInvalidValidationRule($rule, $data);

		$this->TestUpload->validate = array('photo' => array(
			$rule => array('rule' => array($rule, false),
		)));
		$data = array('photo' => array('error' => UPLOAD_ERR_NO_FILE));
		$this->__testSkipValidationRule($rule, $data);
		$this->__testOkAndRemoveCasesValidate();
	}

	public function providerTestIgnorableValidationRules() {
		return array(
			array('tempDirExists', array('error' => UPLOAD_ERR_NO_TMP_DIR)),
			array('isSuccessfulWrite', array('error' => UPLOAD_ERR_CANT_WRITE)),
			array('noPhpExtensionErrors', array('error' => UPLOAD_ERR_EXTENSION)),
		);
	}

/**
 * @dataProvider providerTestChangeConfigurationValidationRules
 */
	public function testChangeConfigurationValidationRules($rule) {
		$this->TestUpload->validate = array(
			'photo' => array(
				$rule => array(
					'rule' => $rule,
					'message' => $rule
				),
			)
		);

		$this->__testInvalidValidationRule($rule, $this->data['test_ok']);

		$this->TestUpload->Behaviors->detach('Upload.Upload');
		$this->TestUpload->Behaviors->attach('Upload.Upload', array(
			'photo' => array(
				'path' => TMP
			)
		));

		$this->__testOkCaseValidates();
		$this->__testRemoveCaseValidates();
	}

	public function providerTestChangeConfigurationValidationRules() {
		return array(
			array('isWritable'),
			array('isValidDir'),
		);
	}

/**
 * This simulates the case where we are uploading no file
 * to an existing record, which DOES have an existing value.
 */
	public function testIsFileUploadOrHasExistingValueEditingWithExistingValue() {
		$this->TestUpload->validate = array(
			'photo' => array(
				'isFileUploadOrHasExistingValue' => array(
					'rule' => 'isFileUploadOrHasExistingValue',
					'message' => 'isFileUploadOrHasExistingValue'
				),
			)
		);

		$data = array(
			'id' => 1, // Fixture record #1 has an existing value.
			'photo' => array(
				'tmp_name' => 'Photo.png',
				'dir' => '/tmp/php/file.tmp',
				'type' => 'image/png',
				'size' => 8192,
				'error' => UPLOAD_ERR_NO_FILE,
			)
		);
		$this->TestUpload->set($data);
		$this->assertTrue($this->TestUpload->validates());
		$this->assertEqual(0, count($this->TestUpload->validationErrors));

		$this->__testOkAndRemoveCasesValidate();
	}

	public function testIsValidMimeType() {
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

		$this->__testInvalidValidationRule('isValidMimeType', $this->data['test_ok']);

		$this->TestUpload->Behaviors->detach('Upload.Upload');
		$this->TestUpload->Behaviors->attach('Upload.Upload', array(
			'photo' => array(
				'mimetypes' => array('image/png', 'image/jpeg')
			)
		));

		$this->__testOkAndRemoveCasesValidate();

		$this->TestUpload->validate = array(
			'photo' => array(
				'isValidMimeType' => array(
					'rule' => array('isValidMimeType', 'image/png'),
					'message' => 'isValidMimeType',
				),
			)
		);

		$this->__testOkCaseValidates();

		$this->TestUpload->validate = array('photo' => array(
			'isValidMimeType' => array('rule' => array('isValidMimeType', 'image/png', false),
		)));
		$data = array('photo' => array('error' => UPLOAD_ERR_NO_FILE));
		$this->__testSkipValidationRule('isValidMimeType', $data);
	}

	public function testIsValidExtension() {
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

		$this->__testInvalidValidationRule('isValidExtension', $this->data['test_ok']);

		$this->TestUpload->set($this->data['test_ok_bmp']);
		$this->assertTrue($this->TestUpload->validates());
		$this->assertEqual(0, count($this->TestUpload->validationErrors));

		$this->TestUpload->Behaviors->detach('Upload.Upload');
		$this->TestUpload->Behaviors->attach('Upload.Upload', array(
			'photo'
		));

		$this->TestUpload->validate['photo']['isValidExtension']['rule'] = array('isValidExtension', 'jpg');
		$this->__testInvalidValidationRule('isValidExtension', $this->data['test_ok']);

		$this->TestUpload->validate['photo']['isValidExtension']['rule'] = array('isValidExtension', array('jpg'));
		$this->__testInvalidValidationRule('isValidExtension', $this->data['test_ok']);

		$this->TestUpload->validate['photo']['isValidExtension']['rule'] = array('isValidExtension', array('jpg', 'bmp'));
		$this->__testInvalidValidationRule('isValidExtension', $this->data['test_ok']);

		$this->TestUpload->validate['photo']['isValidExtension']['rule'] = array('isValidExtension', array('jpg', 'bmp', 'png'));
		$this->__testOkCaseValidates();

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

		$this->__testInvalidValidationRule('isValidExtension', $this->data['test_ok']);

		$data = $this->data['test_ok'];
		$data['photo']['name'] = 'Photo.jpeg';
		$this->__testInvalidValidationRule('isValidExtension', $data);

		$this->__testRemoveCaseValidates();

		$this->TestUpload->validate = array('photo' => array(
			'isValidExtension' => array('rule' => array('isValidExtension', array('jpeg', 'bmp'), false),
		)));
		$data = array('photo' => array('error' => UPLOAD_ERR_NO_FILE));
		$this->__testSkipValidationRule('isValidExtension', $data);
	}

	public function testIsImage() {
		$this->TestUpload->Behaviors->detach('Upload.Upload');
		$this->TestUpload->Behaviors->attach('Upload.Upload', array(
			'photo' => array(
				'mimetypes' => array('image/bmp', 'image/jpeg')
			)
		));

		$result = $this->protectedMethodCall($this->TestUpload->Behaviors->Upload, '_isImage', array(
			$this->TestUpload, 'image/bmp'
		));
		$this->assertTrue($result);

		$result = $this->protectedMethodCall($this->TestUpload->Behaviors->Upload, '_isImage', array(
			$this->TestUpload, 'image/jpeg'
		));
		$this->assertTrue($result);

		$result = $this->protectedMethodCall($this->TestUpload->Behaviors->Upload, '_isImage', array(
			$this->TestUpload, 'application/zip'
		));
		$this->assertFalse($result);
	}

	public function testIsMedia() {
		$this->TestUpload->Behaviors->detach('Upload.Upload');
		$this->TestUpload->Behaviors->attach('Upload.Upload', array(
			'pdf_file' => array(
				'mimetypes' => array('application/pdf', 'application/postscript')
			)
		));

		$result = $this->protectedMethodCall($this->TestUpload->Behaviors->Upload, '_isMedia', array(
			$this->TestUpload, 'application/pdf'
		));
		$this->assertTrue($result);

		$result = $this->protectedMethodCall($this->TestUpload->Behaviors->Upload, '_isMedia', array(
			$this->TestUpload, 'application/postscript'
		));
		$this->assertTrue($result);

		$result = $this->protectedMethodCall($this->TestUpload->Behaviors->Upload, '_isMedia', array(
			$this->TestUpload, 'application/zip'
		));
		$this->assertFalse($result);

		$result = $this->protectedMethodCall($this->TestUpload->Behaviors->Upload, '_isMedia', array(
			$this->TestUpload, 'image/jpeg'
		));
		$this->assertFalse($result);
	}

	public function testGetPathFlat() {
		$basePath = 'tests' . DS . 'path' . DS . 'flat' . DS;
		$result = $this->protectedMethodCall($this->TestUpload->Behaviors->Upload, '_getPathFlat', array(
			$this->TestUpload, 'photo', TMP . $basePath
		));

		$this->assertInternalType('string', $result);
		$this->assertEqual(0, strlen($result));
	}

	public function testGetPathPrimaryKey() {
		$this->TestUpload->id = 5;
		$basePath = 'tests' . DS . 'path' . DS . 'primaryKey' . DS;
		$result = $this->protectedMethodCall($this->TestUpload->Behaviors->Upload, '_getPathPrimaryKey', array(
			$this->TestUpload, 'photo', TMP . $basePath
		));

		$this->assertInternalType('integer', $result);
		$this->assertEqual(1, strlen($result));
		$this->assertEqual($result, $this->TestUpload->id);
		$this->assertTrue(is_dir(TMP . $basePath . $result));
	}

	public function testGetPathRandom() {
		$basePath = 'tests' . DS . 'path' . DS . 'random' . DS;
		$result = $this->protectedMethodCall($this->TestUpload->Behaviors->Upload, '_getPathRandom', array(
			$this->TestUpload, 'photo', TMP . $basePath
		));

		$this->assertInternalType('string', $result);
		$this->assertEqual(8, strlen($result));
		$this->assertTrue(is_dir(TMP . $basePath . $result));
	}

	public function testGetPathRandomCombined() {
		$this->TestUpload->id = 5;
		$basePath = 'tests' . DS . 'path' . DS . 'random' . DS;
		$result = $this->protectedMethodCall($this->TestUpload->Behaviors->Upload, '_getPathRandomCombined', array(
			$this->TestUpload, 'photo', TMP . $basePath
		));

		$this->assertInternalType('string', $result);
		$this->assertEqual(9 + strlen($this->TestUpload->id), strlen($result));
		$this->assertEqual(substr($result, 0, strlen($this->TestUpload->id)), $this->TestUpload->id);
		$this->assertTrue(is_dir(TMP . $basePath . $result));
	}

	public function testReplacePath() {
		$result = $this->protectedMethodCall($this->TestUpload->Behaviors->Upload, '_path', array(
			$this->TestUpload, 'photo', array('path' => 'webroot{DS}files/{model}\\{field}{DS}')
		));

		$this->assertInternalType('string', $result);
		$this->assertEqual(WWW_ROOT . 'files' . DIRECTORY_SEPARATOR . 'test_upload' . DIRECTORY_SEPARATOR . 'photo' . DIRECTORY_SEPARATOR, $result);

		$result = $this->protectedMethodCall($this->TestUpload->Behaviors->Upload, '_path', array(
			$this->TestUpload, 'photo', array('path' => 'webroot{DS}files//{size}/{model}\\{field}{DS}{geometry}///')
		));

		$this->assertInternalType('string', $result);
		$this->assertEqual(WWW_ROOT . 'files' . DIRECTORY_SEPARATOR . '{size}' . DIRECTORY_SEPARATOR . 'test_upload' . DIRECTORY_SEPARATOR . 'photo' . DIRECTORY_SEPARATOR . '{geometry}' . DIRECTORY_SEPARATOR, $result);

		$result = $this->protectedMethodCall($this->TestUpload->Behaviors->Upload, '_path', array(
			$this->TestUpload, 'photo', array('isThumbnail' => false, 'path' => 'webroot{DS}files//{size}/{model}\\\\{field}{DS}{geometry}///')
		));

		$this->assertInternalType('string', $result);
		$this->assertEqual(WWW_ROOT . 'files' . DIRECTORY_SEPARATOR . 'test_upload' . DIRECTORY_SEPARATOR . 'photo' . DIRECTORY_SEPARATOR, $result);
	}

	public function testPrepareFilesForDeletion() {
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

		$result = $this->protectedMethodCall($this->TestUpload->Behaviors->Upload, '_prepareFilesForDeletion', array(
			$this->TestUpload, 'photo',
			array('TestUpload' => array('id' => 1, 'dir' => '1', 'photo' => 'Photo.png')),
			$this->TestUpload->Behaviors->Upload->settings['TestUpload']['photo']
		));

		$this->assertInternalType('array', $result);
		$this->assertEqual(1, count($result));
		$this->assertEqual(4, count($result['TestUpload']));
	}

	public function testPrepareFilesForDeletionWithoutDirDataFieldWithPrimaryKeyPathMethod() {
		$this->TestUpload->actsAs['Upload.Upload'] = array(
			'photo' => array(
				'pathMethod' => 'primaryKey',
				'thumbnailSizes' => array(
					'xvga' => '1024x768',
					'vga' => '640x480',
					'thumb' => '80x80'
				),
				'fields' => array(
					'dir' => false
				)
			)
		);
		$this->mockUpload();
		$this->MockUpload->expects($this->once())->method('_getMimeType')->will($this->returnValue('image/png'));

		$result = $this->protectedMethodCall($this->TestUpload->Behaviors->Upload, '_prepareFilesForDeletion', array(
			$this->TestUpload, 'photo',
			array('TestUpload' => array('id' => 1, 'photo' => 'Photo.png')),
			$this->TestUpload->Behaviors->Upload->settings['TestUpload']['photo']
		));
		$this->assertInternalType('array', $result);
		$this->assertEqual(1, count($result));
		$this->assertEqual(4, count($result['TestUpload']));

		$basePath	= $this->TestUpload->Behaviors->Upload->settings['TestUpload']['photo']['path'];
		$primaryKey = 1;
		$this->assertEqual($result['TestUpload'][0], sprintf('%s%d/Photo.png', $basePath, $primaryKey));
		$this->assertEqual($result['TestUpload'][1], sprintf('%s%d/xvga_Photo.png', $basePath, $primaryKey));
		$this->assertEqual($result['TestUpload'][2], sprintf('%s%d/vga_Photo.png', $basePath, $primaryKey));
		$this->assertEqual($result['TestUpload'][3], sprintf('%s%d/thumb_Photo.png', $basePath, $primaryKey));
	}

	public function testPrepareFilesForDeletionWithoutDirDataFieldWithFlagPathMethod() {
		$this->TestUpload->actsAs['Upload.Upload'] = array(
			'photo' => array(
				'pathMethod' => 'flat',
				'thumbnailSizes' => array(
					'xvga' => '1024x768',
					'vga' => '640x480',
					'thumb' => '80x80'
				),
				'fields' => array(
					'dir' => false
				)
			)
		);
		$this->mockUpload();
		$this->MockUpload->expects($this->once())->method('_getMimeType')->will($this->returnValue('image/png'));

		$result = $this->protectedMethodCall($this->TestUpload->Behaviors->Upload, '_prepareFilesForDeletion', array(
			$this->TestUpload, 'photo',
			array('TestUpload' => array('id' => 1, 'photo' => 'Photo.png')),
			$this->TestUpload->Behaviors->Upload->settings['TestUpload']['photo']
		));

		$this->assertInternalType('array', $result);
		$this->assertEqual(1, count($result));
		$this->assertEqual(4, count($result['TestUpload']));

		$basePath	= $this->TestUpload->Behaviors->Upload->settings['TestUpload']['photo']['path'];
		$primaryKey = 1;
		$this->assertEqual($result['TestUpload'][0], sprintf('%sPhoto.png', $basePath));
		$this->assertEqual($result['TestUpload'][1], sprintf('%sxvga_Photo.png', $basePath));
		$this->assertEqual($result['TestUpload'][2], sprintf('%svga_Photo.png', $basePath));
		$this->assertEqual($result['TestUpload'][3], sprintf('%sthumb_Photo.png', $basePath));
	}

	public function testPrepareFilesForDeletionWithThumbnailType() {
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

		$result = $this->protectedMethodCall($this->TestUpload->Behaviors->Upload, '_prepareFilesForDeletion', array(
			$this->TestUpload, 'photo',
			array('TestUpload' => array('id' => 1, 'dir' => '1', 'photo' => 'Photo.png')),
			$this->TestUpload->Behaviors->Upload->settings['TestUpload']['photo']
		));

		$this->assertInternalType('array', $result);
		$this->assertEqual(1, count($result));
		$this->assertEqual(4, count($result['TestUpload']));
	}

	public function testPrepareFilesForDeletionWithMediaFileAndFalseThumbnailType() {
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

		$result = $this->protectedMethodCall($this->TestUpload->Behaviors->Upload, '_prepareFilesForDeletion', array(
			$this->TestUpload, 'photo',
			array('TestUpload' => array('id' => 1, 'dir' => '1', 'photo' => 'Photo.pdf')),
			$this->TestUpload->Behaviors->Upload->settings['TestUpload']['photo']
		));

		$this->assertInternalType('array', $result);
		$this->assertEqual(1, count($result));
		$this->assertEqual(4, count($result['TestUpload']));
	}

	public function testPrepareFilesForDeletionWithMediaFile() {
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

		$result = $this->protectedMethodCall($this->TestUpload->Behaviors->Upload, '_prepareFilesForDeletion', array(
			$this->TestUpload, 'photo',
			array('TestUpload' => array('id' => 1, 'dir' => '1', 'photo' => 'Photo.pdf')),
			$this->TestUpload->Behaviors->Upload->settings['TestUpload']['photo']
		));

		$this->assertInternalType('array', $result);
		$this->assertEqual(1, count($result));
		$this->assertEqual(4, count($result['TestUpload']));
	}

	private function __testOkAndRemoveCasesValidate() {
		$this->__testOkCaseValidates();
		$this->__testRemoveCaseValidates();
	}

	private function __testValidates($data) {
		$this->TestUpload->set($data);
		$this->assertTrue($this->TestUpload->validates());
		$this->assertEqual(0, count($this->TestUpload->validationErrors));
	}

	private function __testOkCaseValidates() {
		$this->__testValidates($this->data['test_ok']);
	}

	private function __testRemoveCaseValidates() {
		$this->TestUpload->set($this->data['test_remove']);
		$this->assertTrue($this->TestUpload->validates());
		$this->assertEqual(0, count($this->TestUpload->validationErrors));
	}

	private function __testSkipValidationRule($ruleName, $data) {
		$this->TestUpload->set($data);
		$this->assertTrue($this->TestUpload->validates());
		$this->assertEqual(0, count($this->TestUpload->validationErrors));
	}

	private function __testInvalidValidationRule($ruleName, $data) {
		$this->TestUpload->set($data);
		$this->assertFalse($this->TestUpload->validates());
		$this->assertEqual(1, count($this->TestUpload->validationErrors));
		$this->assertEqual($ruleName, current($this->TestUpload->validationErrors['photo']));
	}

}
