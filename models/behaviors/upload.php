<?php
/**
 * Upload behavior
 *
 * Enables users to easily add file uploading and necessary validation rules
 *
 * PHP versions 4 and 5
 *
 * Copyright 2010, Jose Diaz-Gonzalez
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2010, Jose Diaz-Gonzalez
 * @package       upload
 * @subpackage    upload.models.behaviors
 * @link          http://github.com/josegonzalez/upload
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

class UploadBehavior extends ModelBehavior {

	var $defaults = array(
		'randomPath'		=> false,
		'path'				=> 'webroot{DS}files{DS}{model}{DS}{field}{DS}',
		'fields'			=> array('dir' => 'dir', 'type' => 'type', 'size' => 'size'),
		'mimetypes'			=> array(),
		'extensions'		=> array(),
		'maxSize'			=> 2097152,
		'minSize'			=> 8,
		'maxHeight'			=> 0,
		'minHeight'			=> 0,
		'maxWidth'			=> 0,
		'minWidth'			=> 0,
		'thumbnails'		=> true,
		'thumbsizes'		=> array(),
		'thumbnailQuality'	=> 75,
	);

	var $_imageMimetypes = array(
		'image/bmp',
		'image/gif',
		'image/jpeg',
		'image/pjpeg',
		'image/png',
		'image/vnd.microsoft.icon',
		'image/x-icon',
	);

	var $__filesToRemove = array();

/**
 * Runtime configuration for this behavior
 *
 * @var array
 **/
	var $runtime;

/**
 * undocumented function
 *
 * @return void
 * @author Jose Diaz-Gonzalez
 **/
	function setup(&$model, $settings = array()) {
		if (isset($this->settings[$model->alias])) return;
		$this->settings[$model->alias] = array();

		foreach ($settings as $field => $options) {
			if (is_int($field)) {
				$field = $options;
				$options = array();
			}

			if (!isset($this->settings[$model->alias][$field])) {
				$options = array_merge($this->defaults, (array) $options);
				$options['path'] = $this->_path($model, $field, $options['path']);
				$this->settings[$model->alias][$field] = $options;
			}
		}
	}

/**
 * undocumented function
 *
 * @return void
 * @author Jose Diaz-Gonzalez
 **/
	function beforeSave(&$model) {
		foreach ($this->settings[$model->alias] as $field => $options) {
			$this->runtime[$model->alias][$field] = $model->data[$model->alias][$field];
			$model->data[$model->alias] = array_merge($model->data[$model->alias], array(
				$field => $this->runtime[$model->alias][$field]['name'],
				$options['fields']['type'] => $this->runtime[$model->alias][$field]['type'],
				$options['fields']['size'] => $this->runtime[$model->alias][$field]['size']
			));
		}
		return true;
	}

	function afterSave(&$model, $created) {
		$temp = array();
		foreach ($this->settings[$model->alias] as $field => $options) {
			if (!in_array($model->data[$model->alias])) continue;

			$temp[$model->alias][$options['fields']['dir']] = $this->_getPath($model, $field);
			$path = APP_PATH . $this->settings[$model->alias][$field]['path'] . $temp[$model->alias][$field]['dir'];
			$tmp = $this->runtime[$model->alias][$field]['tmp_name'];
			$filePath = $path . $model->data[$model->alias][$field];
			if (!@move_uploaded_file($tmp, $filePath)) {
				$model->invalidate($field, 'moveUploadedFile');
			}

			$this->_createThumbnails($model, $field, $path);
		}
		$model->updateAll($temp[$model->alias], array(
			$model->primaryKey => $model->data[$model->alias][$model->primaryKey]
		));
	}

	function beforeDelete(&$model, $cascade) {
		$data = $model->find('first', array(
			'conditions' => array("{$model->alias}.{$model->primaryKey}" => $model->id),
			'contain' => false,
			'recursive' => -1,
		));

		foreach ($this->settings[$model->alias] as $field => $options) {
			$this->_prepareFilesForDeletion($model, $field, $data, $options);
		}
		return true;
	}

	function afterDelete(&$model) {
		foreach ($this->__filesToRemove[$model->alias] as $file) {
			@unlink($file);
		}
	}

/**
 * Check that the file does not exceed the max 
 * file size specified by PHP
 *
 * @param Object $model 
 * @param mixed $check Value to check
 * @return boolean Success
 * @access public
 */
	function isUnderPhpSizeLimit(&$model, $check) {
		$field = array_pop(array_keys($check));
		return $check[$field]['error'] !== UPLOAD_ERR_INI_SIZE;
	}

/**
 * Check that the file does not exceed the max 
 * file size specified in the HTML Form
 *
 * @param Object $model 
 * @param mixed $check Value to check
 * @return boolean Success
 * @access public
 */
	function isUnderFormSizeLimit(&$model, $check) {
		$field = array_pop(array_keys($check));
		return $check[$field]['error'] !== UPLOAD_ERR_FORM_SIZE;
	}

/**
 * Check that the file was completely uploaded
 *
 * @param Object $model
 * @param mixed $check Value to check
 * @return boolean Success
 * @access public
 */
	function isCompletedUpload(&$model, $check) {
		$field = array_pop(array_keys($check));
		return $check[$field]['error'] !== UPLOAD_ERR_PARTIAL;
	}

/**
 * Check that a file was uploaded
 *
 * @param Object $model
 * @param mixed $check Value to check
 * @return boolean Success
 * @access public
 */
	function isFileUpload(&$model, $check) {
		$field = array_pop(array_keys($check));
		return $check[$field]['error'] !== UPLOAD_ERR_NO_FILE;
	}

/**
 * Check that the PHP temporary directory is missing
 *
 * @param Object $model
 * @param mixed $check Value to check
 * @return boolean Success
 * @access public
 */
	function tempDirExists(&$model, $check) {
		$field = array_pop(array_keys($check));
		return $check[$field]['error'] !== UPLOAD_ERR_NO_TMP_DIR;
	}

/**
 * Check that the file was successfully written to the server
 *
 * @param Object $model
 * @param mixed $check Value to check
 * @return boolean Success
 * @access public
 */
	function isSuccessfulWrite(&$model, $check) {
		$field = array_pop(array_keys($check));
		return $check[$field]['error'] !== UPLOAD_ERR_CANT_WRITE;
	}

/**
 * Check that a PHP extension did not cause an error
 *
 * @param Object $model
 * @param mixed $check Value to check
 * @return boolean Success
 * @access public
 */
	function noPhpExtensionErrors(&$model, $check) {
		$field = array_pop(array_keys($check));
		return $check[$field]['error'] !== UPLOAD_ERR_EXTENSION;
	}

/**
 * Check that the file is of a valid mimetype
 *
 * @param Object $model
 * @param mixed $check Value to check
 * @param array $mimetypes file mimetypes to allow
 * @return boolean Success
 * @access public
 */
	function isValidMimeType(&$model, $check, $mimetypes = array()) {
		$field = array_pop(array_keys($check));
		foreach ($mimetypes as $key => $value) {
			if (!is_int($key)) {
				$mimetypes = $this->settings[$model->alias][$field]['mimetypes'];
				break;
			}
		}

		if (empty($mimetypes)) $mimetypes = $this->settings[$model->alias][$field]['mimetypes'];

		return in_array($check[$field]['type'], $mimetypes);
	}

/**
 * Check that the upload directory is writable
 *
 *
 * @param Object $model
 * @param mixed $check Value to check
 * @param string $path Path relative to APP_PATH
 * @return boolean Success
 * @access public
 */
	function isWritable(&$model, $check) {
		$field = array_pop(array_keys($check));

		return is_writable($this->settings[$model->alias][$field]['path']);
	}

/**
 * Check that the upload directory exists
 *
 * @param Object $model
 * @param mixed $check Value to check
 * @param string $path Path relative to APP_PATH
 * @return boolean Success
 * @access public
 */
	function isValidDir(&$model, $check) {
		$field = array_pop(array_keys($check));

		return is_dir($this->settings[$model->alias][$field]['path']);
	}

/**
 * Check that the file is below the maximum file upload size
 *
 * @param Object $model
 * @param mixed $check Value to check
 * @param int $size Maximum file size
 * @return boolean Success
 * @access public
 */
	function isBelowMaxSize(&$model, $check, $size = null) {
		$field = array_pop(array_keys($check));
		if (!$size) $size = $this->settings[$model->alias][$field]['maxSize'];
		return $check[$field]['size'] <= $size;
	}

/**
 * Check that the file is above the minimum file upload size
 *
 * @param Object $model
 * @param mixed $check Value to check
 * @param int $size Minimum file size
 * @return boolean Success
 * @access public
 */
	function isAboveMinSize(&$model, $check, $size = null) {
		$field = array_pop(array_keys($check));
		if (!$size) $size = $this->settings[$model->alias][$field]['minSize'];
		return $check[$field]['size'] >= $size;
	}

/**
 * Check that the file has a valid extension
 *
 * @param Object $model
 * @param mixed $check Value to check
 * @param array $extensions file extenstions to allow
 * @return boolean Success
 * @access public
 */
	function isValidExtension(&$model, $check, $extensions) {
		$field = array_pop(array_keys($check));
		foreach ($extensions as $key => $value) {
			if (!is_int($key)) {
				$extensions = $this->settings[$model->alias][$field]['extensions'];
				break;
			}
		}

		if (empty($extensions)) $extensions = $this->settings[$model->alias][$field]['extensions'];
		$pathinfo = pathinfo($check[$field]['tmp_name']);

		return in_array($pathinfo['extension'], $extensions);
	}

/**
 * Check that the file is above the minimum height requirement
 *
 * @param Object $model 
 * @param mixed $check Value to check
 * @param int $height Height of Image 
 * @return boolean Success
 * @access public
 */
	function isAboveMinHeight(&$model, $check, $height = null) {
		$field = array_pop(array_keys($check));
		if (!$height) $height = $this->settings[$model->alias][$field]['minHeight'];

		return $height < 0 && imagesy($check[$field]['tmp_name']) >= $height;
	}

/**
 * Check that the file is below the maximum height requirement
 *
 * @param Object $model 
 * @param mixed $check Value to check
 * @param int $height Height of Image 
 * @return boolean Success
 * @access public
 */
	function isBelowMaxHeight(&$model, $check, $height = null) {
		$field = array_pop(array_keys($check));
		if (!$height) $height = $this->settings[$model->alias][$field]['maxHeight'];

		return $height < 0 && imagesy($check[$field]['tmp_name']) <= $height;
	}

/**
 * Check that the file is above the minimum width requirement
 *
 * @param Object $model 
 * @param mixed $check Value to check
 * @param int $width Width of Image 
 * @return boolean Success
 * @access public
 */
	function isAboveMinWidth(&$model, $check, $width = null) {
		$field = array_pop(array_keys($check));
		if (!$width) $width = $this->settings[$model->alias][$field]['minWidth'];

		return $width < 0 && imagesx($check[$field]['tmp_name']) >= $width;
	}

/**
 * Check that the file is below the maximum width requirement
 *
 * @param Object $model 
 * @param mixed $check Value to check
 * @param int $width Width of Image 
 * @return boolean Success
 * @access public
 */
	function isBelowMaxWidth(&$model, $check, $width = null) {
		$field = array_pop(array_keys($check));
		if (!$width) $width = $this->settings[$model->alias][$field]['maxWidth'];

		return $width < 0 && imagesx($check[$field]['tmp_name']) <= $width;
	}

	function _resize(&$model, $field, $path, $style, $geometry) {
		$srcFile  = $path . $model->data[$model->alias][$field];
		$destFile = $path . $style . '_' . $model->data[$model->alias][$field];

		$image    = new imagick($srcFile);
		$height   = $image->getImageHeight();
		$width    = $image->getImageWidth();

		if (preg_match('/^\\[[\\d]+x[\\d]+\\]$/', $geometry)) {
			// resize with banding
			list($destW, $destH) = explode('x', substr($geometry, 1, strlen($geometry)-2));
			$image->thumbnailImage($destW, $destH);
		} elseif (preg_match('/^[\\d]+x[\\d]+$/', $geometry)) {
			// cropped resize (best fit)
			list($destW, $destH) = explode('x', $geometry);
			$image->cropThumbnailImage($destW, $destH);
		} elseif (preg_match('/^[\\d]+w$/', $geometry)) {
			// calculate heigh according to aspect ratio
			$image->thumbnailImage((int)$geometry-1, 0);
		} elseif (preg_match('/^[\\d]+h$/', $geometry)) {
			// calculate width according to aspect ratio
			$image->thumbnailImage(0, (int)$geometry-1);
		} elseif (preg_match('/^[\\d]+l$/', $geometry)) {
			// calculate shortest side according to aspect ratio
			$destW = 0;
			$destH = 0;
			$destW = ($width > $height) ? (int)$geometry-1 : 0;
			$destH = ($width > $height) ? 0 : (int)$geometry-1;

			$image->thumbnailImage($destW, $destH, true);
		}

		$this->setImageCompressionQuality($this->settings[$model->alias][$field]['thumbnailQuality']);
		if (!$image->writeImage($destFile)) return false;

		$image->clear();
		$image->destroy();
		return true;
	}

	function _getPath(&$model, $field) {
		$path = $this->settings[$model->alias][$field]['path'];
		if ($this->settings[$model->alias][$field]['randomPath']) {
			return $this->_createRandomPath($model->data[$model->alias][$field], $path);
		}
		$destDir = APP_PATH . $path . $model->data[$model->alias][$model->primaryKey] . DIRECTORY_SEPARATOR;
		if (!file_exists($destDir)) {
			@mkdir($destDir, 0777, true);
			@chmod($destDir, 0777);
		}
		return $model->data[$model->alias][$model->primaryKey] . DIRECTORY_SEPARATOR;
	}

	function _createRandomPath($string, $path) {
		$endPath = null;
		$decrement = 0;
		$string = crc32($string . time());

		for ($i = 0; $i < 3; $i++) {
			$decrement = $decrement - 2;
			$endPath .= sprintf("%02d" . DIRECTORY_SEPARATOR, substr('000000' . $string, $decrement, 2));
		}

		$destDir = APP_PATH . $path . $endPath;
		if (!file_exists($destDir)) {
			@mkdir($destDir, 0777, true);
			@chmod($destDir, 0777);
		}

		return $endPath;
	}

/**
 * Returns a path based on settings configuration
 *
 * @return void
 * @author Jose Diaz-Gonzalez
 **/
	function _path(&$model, $fieldName, $path) {
		$replacements = array(
			'{model}'	=> Inflector::underscore($model->alias),
			'{field}'	=> $fieldName,
			'{DS}'		=> DIRECTORY_SEPARATOR,
			'/'			=> DIRECTORY_SEPARATOR,
			'\\'		=> DIRECTORY_SEPARATOR,
		);
		return str_replace(
			array_keys($replacements),
			array_values($replacements),
			$path
		);
	}

	function _createThumbnails(&$model, $field, $path) {
		if ($this->_isImage($model, $this->runtime[$model->alias][$field]['type'])
		&& $this->settings[$model->alias][$field]['thumbnails']) {
			// Create thumbnails
			foreach ($this->settings[$model->alias][$field]['thumbsizes'] as $style => $geometry) {
				if ($this->_resize($model, $field, $path, $style, $geometry)) {
					$model->invalidate($field, 'resizeFail');
				}
			}
		}
	}

	function _isImage(&$model, $mimetype) {
		return in_array($mimetype, $this->_imageMimetypes);
	}

	function _prepareFilesForDeletion(&$model, $field, $data, $options) {
		$this->__filesToRemove[$model->alias] = array();
		$this->__filesToRemove[$model->alias][] = APP_PATH . $this->settings[$model->alias][$field]['path'] . $data[$model->alias][$options['fields']['dir']] . $data[$model->alias][$field];
		foreach ($options['thumbsizes'] as $style => $geometry) {
			$this->__filesToRemove[$model->alias][] = APP_PATH . $this->settings[$model->alias][$field]['path'] . $data[$model->alias][$options['fields']['dir']] . $style . '_' . $data[$model->alias][$field];
		}
		return $this->__filesToRemove;
	}

}
