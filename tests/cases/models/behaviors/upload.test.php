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
	var $model = null;

	function startTest() {
		$this->model = ClassRegistry::init('TestUpload');
	}

	function endTest() {
		Classregistry::flush();
		unset($this->model);
	}

	function testIsUnderPhpSizeLimit() {
		$this->model->validate = array(
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
		$this->model->set($data);
		$this->assertFalse($this->model->validates());

		$this->assertEqual(count($this->model->validationErrors), 1);
		$this->assertEqual(current($this->model->validationErrors), 'isUnderPhpSizeLimit');

		$data = array(
			'photo' => array(
				'tmp_name'  => 'Photo.png',
				'dir'   => '/tmp/php/file.tmp',
				'type'  => 'image/png',
				'size'  => 8192,
				'error' => UPLOAD_ERR_OK,
			)
		);
		$this->model->set($data);
		$this->assertTrue($this->model->validates());
		$this->assertEqual(count($this->model->validationErrors), 0);
	}

	function testIsUnderFormSizeLimit() {
		$this->model->validate = array(
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
		$this->model->set($data);
		$this->assertFalse($this->model->validates());

		$this->assertEqual(count($this->model->validationErrors), 1);
		$this->assertEqual(current($this->model->validationErrors), 'isUnderFormSizeLimit');

		$data = array(
			'photo' => array(
				'tmp_name'  => 'Photo.png',
				'dir'   => '/tmp/php/file.tmp',
				'type'  => 'image/png',
				'size'  => 8192,
				'error' => UPLOAD_ERR_OK,
			)
		);
		$this->model->set($data);
		$this->assertTrue($this->model->validates());
		$this->assertEqual(count($this->model->validationErrors), 0);
	}

	function testIsCompletedUpload() {
		$this->model->validate = array(
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
		$this->model->set($data);
		$this->assertFalse($this->model->validates());

		$this->assertEqual(count($this->model->validationErrors), 1);
		$this->assertEqual(current($this->model->validationErrors), 'isCompletedUpload');

		$data = array(
			'photo' => array(
				'tmp_name'  => 'Photo.png',
				'dir'   => '/tmp/php/file.tmp',
				'type'  => 'image/png',
				'size'  => 8192,
				'error' => UPLOAD_ERR_OK,
			)
		);
		$this->model->set($data);
		$this->assertTrue($this->model->validates());
		$this->assertEqual(count($this->model->validationErrors), 0);
	}

	function testIsFileUpload() {
		$this->model->validate = array(
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
		$this->model->set($data);
		$this->assertFalse($this->model->validates());

		$this->assertEqual(count($this->model->validationErrors), 1);
		$this->assertEqual(current($this->model->validationErrors), 'isFileUpload');

		$data = array(
			'photo' => array(
				'tmp_name'  => 'Photo.png',
				'dir'   => '/tmp/php/file.tmp',
				'type'  => 'image/png',
				'size'  => 8192,
				'error' => UPLOAD_ERR_OK,
			)
		);
		$this->model->set($data);
		$this->assertTrue($this->model->validates());
		$this->assertEqual(count($this->model->validationErrors), 0);
	}

	function testTempDirExists() {
		$this->model->validate = array(
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
		$this->model->set($data);
		$this->assertFalse($this->model->validates());

		$this->assertEqual(count($this->model->validationErrors), 1);
		$this->assertEqual(current($this->model->validationErrors), 'tempDirExists');

		$data = array(
			'photo' => array(
				'tmp_name'  => 'Photo.png',
				'dir'   => '/tmp/php/file.tmp',
				'type'  => 'image/png',
				'size'  => 8192,
				'error' => UPLOAD_ERR_OK,
			)
		);
		$this->model->set($data);
		$this->assertTrue($this->model->validates());
		$this->assertEqual(count($this->model->validationErrors), 0);
	}

	function testIsSuccessfulWrite() {
		$this->model->validate = array(
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
		$this->model->set($data);
		$this->assertFalse($this->model->validates());

		$this->assertEqual(count($this->model->validationErrors), 1);
		$this->assertEqual(current($this->model->validationErrors), 'isSuccessfulWrite');

		$data = array(
			'photo' => array(
				'tmp_name'  => 'Photo.png',
				'dir'   => '/tmp/php/file.tmp',
				'type'  => 'image/png',
				'size'  => 8192,
				'error' => UPLOAD_ERR_OK,
			)
		);
		$this->model->set($data);
		$this->assertTrue($this->model->validates());
		$this->assertEqual(count($this->model->validationErrors), 0);
	}

	function testNoPhpExtensionErrors() {
		$this->model->validate = array(
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
		$this->model->set($data);
		$this->assertFalse($this->model->validates());

		$this->assertEqual(count($this->model->validationErrors), 1);
		$this->assertEqual(current($this->model->validationErrors), 'noPhpExtensionErrors');

		$data = array(
			'photo' => array(
				'tmp_name'  => 'Photo.png',
				'dir'   => '/tmp/php/file.tmp',
				'type'  => 'image/png',
				'size'  => 8192,
				'error' => UPLOAD_ERR_OK,
			)
		);
		$this->model->set($data);
		$this->assertTrue($this->model->validates());
		$this->assertEqual(count($this->model->validationErrors), 0);
	}

}