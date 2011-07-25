<?php
App::import('Behavior', 'Upload.Upload');

class TestUpload extends CakeTestModel {
	var $useTable = 'uploads';
	var $actsAs = array(
		'Upload.Upload' => array(
			'photo'
		)
	);
}


class UploadBehaviorTest extends CakeTestCase {

	var $fixtures = array('plugin.upload.upload');
	var $TestUpload = null;
	var $data = array();

	function startTest() {
		$this->TestUpload = ClassRegistry::init('TestUpload');
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
	}

	function endTest() {
		Classregistry::flush();
		unset($this->TestUpload);
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
		$this->assertEqual('isUnderPhpSizeLimit', current($this->TestUpload->validationErrors));

		$this->TestUpload->set($this->data['test_ok']);
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
		$this->assertEqual('isUnderFormSizeLimit', current($this->TestUpload->validationErrors));

		$this->TestUpload->set($this->data['test_ok']);
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
		$this->assertEqual('isCompletedUpload', current($this->TestUpload->validationErrors));

		$this->TestUpload->set($this->data['test_ok']);
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
		$this->assertEqual('isFileUpload', current($this->TestUpload->validationErrors));

		$this->TestUpload->set($this->data['test_ok']);
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
		$this->assertEqual('tempDirExists', current($this->TestUpload->validationErrors));

		$this->TestUpload->set($this->data['test_ok']);
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
		$this->assertEqual('isSuccessfulWrite', current($this->TestUpload->validationErrors));

		$this->TestUpload->set($this->data['test_ok']);
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
		$this->assertEqual('noPhpExtensionErrors', current($this->TestUpload->validationErrors));

		$this->TestUpload->set($this->data['test_ok']);
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
		$this->assertEqual('isValidMimeType', current($this->TestUpload->validationErrors));

		$this->TestUpload->Behaviors->detach('Upload.Upload');
		$this->TestUpload->Behaviors->attach('Upload.Upload', array(
			'photo' => array(
				'mimetypes' => array('image/png', 'image/jpeg')
			)
		));

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
		$this->assertEqual('isValidExtension', current($this->TestUpload->validationErrors));

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
		$this->assertEqual('isValidExtension', current($this->TestUpload->validationErrors));

		$this->TestUpload->validate['photo']['isValidExtension']['rule'] = array('isValidExtension', array('jpg'));
		$this->TestUpload->set($this->data['test_ok']);
		$this->assertFalse($this->TestUpload->validates());
		$this->assertEqual(1, count($this->TestUpload->validationErrors));
		$this->assertEqual('isValidExtension', current($this->TestUpload->validationErrors));

		$this->TestUpload->validate['photo']['isValidExtension']['rule'] = array('isValidExtension', array('jpg', 'bmp'));
		$this->TestUpload->set($this->data['test_ok']);
		$this->assertFalse($this->TestUpload->validates());
		$this->assertEqual(1, count($this->TestUpload->validationErrors));
		$this->assertEqual('isValidExtension', current($this->TestUpload->validationErrors));

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
		$this->assertEqual('isValidExtension', current($this->TestUpload->validationErrors));

		$data['photo']['name'] = 'Photo.jpg';
		$this->TestUpload->set($this->data['test_ok']);
		$this->assertFalse($this->TestUpload->validates());
		$this->assertEqual(1, count($this->TestUpload->validationErrors));
		$this->assertEqual('isValidExtension', current($this->TestUpload->validationErrors));
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
		$this->assertEqual('isWritable', current($this->TestUpload->validationErrors));

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
		$this->assertEqual('isValidDir', current($this->TestUpload->validationErrors));

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

	function testGetPathRandom() {
		$result = $this->TestUpload->Behaviors->Upload->_getPathRandom('string', 'tmp' . DIRECTORY_SEPARATOR . 'cache');

		$this->assertIsA($result, 'String');
		$this->assertEqual(8, strlen($result));
		$this->assertTrue(is_dir(TMP . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . $result));
	}

	function testReplacePath() {
		$result = $this->TestUpload->Behaviors->Upload->_path($this->TestUpload, 'photo', 'webroot{DS}files/{model}\\{field}{DS}');

		$this->assertIsA($result, 'String');
		$this->assertEqual('webroot' . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'test_upload' . DIRECTORY_SEPARATOR . 'photo' . DIRECTORY_SEPARATOR, $result);
	}

	function testPrepareFilesForDeletion() {
		$this->TestUpload->Behaviors->detach('Upload.Upload');
		$this->TestUpload->Behaviors->attach('Upload.Upload', array(
			'photo' => array(
				'thumbsizes' => array(
					'xvga' => '1024x768',
					'vga' => '640x480',
					'thumb' => '80x80'
				),
				'fields' => array(
					'dir' => 'dir'
				)
			)
		));

		$result = $this->TestUpload->Behaviors->Upload->_prepareFilesForDeletion(
			$this->TestUpload, 'photo',
			array('TestUpload' => array('dir' => '1/', 'photo' => 'Photo.png')),
			$this->TestUpload->Behaviors->Upload->settings['TestUpload']['photo']
		);

		$this->assertIsA($result, 'Array');
		$this->assertEqual(1,count($result));
		$this->assertEqual(4, count($result['TestUpload']));
	}

}