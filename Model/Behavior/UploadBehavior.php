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
 * @link          http://github.com/josegonzalez/cakephp-upload
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
App::uses('Folder', 'Utility');
App::uses('UploadException', 'Upload.Lib/Error/Exception');
App::uses('HttpSocket', 'Network/Http');
class UploadBehavior extends ModelBehavior {

	public $defaults = array(
		'rootDir' => null,
		'pathMethod' => 'primaryKey',
		'path' => '{ROOT}webroot{DS}files{DS}{model}{DS}{field}{DS}',
		'fields' => array('dir' => 'dir', 'type' => 'type', 'size' => 'size'),
		'mimetypes' => array(),
		'extensions' => array(),
		'maxSize' => 2097152,
		'minSize' => 8,
		'maxHeight' => 0,
		'minHeight' => 0,
		'maxWidth' => 0,
		'minWidth' => 0,
		'thumbnails' => true,
		'thumbnailMethod' => 'imagick',
		'thumbnailName' => null,
		'thumbnailPath' => null,
		'thumbnailPrefixStyle' => true,
		'thumbnailQuality' => 75,
		'thumbnailSizes' => array(),
		'thumbnailType' => false,
		'deleteOnUpdate' => false,
		'mediaThumbnailType' => 'png',
		'saveDir' => true,
		'deleteFolderOnDelete' => false,
		'mode' => 0777,
	);

	protected $_imageMimetypes = array(
		'image/bmp',
		'image/gif',
		'image/jpeg',
		'image/pjpeg',
		'image/png',
		'image/vnd.microsoft.icon',
		'image/x-icon',
	);

	protected $_mediaMimetypes = array(
		'application/pdf',
		'application/postscript',
	);

	protected $_pathMethods = array('flat', 'primaryKey', 'random', 'randomCombined');

	protected $_resizeMethods = array('imagick', 'php');

	private $__filesToRemove = array();

	private $__foldersToRemove = array();

	protected $_removingOnly = array();

/**
 * Runtime configuration for this behavior
 *
 * @var array
 **/
	public $runtime;

/**
 * Initiate Upload behavior
 *
 * @param object $model instance of model
 * @param array $config array of configuration settings.
 * @return void
 */
	public function setup(Model $model, $config = array()) {
		if (isset($this->settings[$model->alias])) {
			return;
		}

		$this->settings[$model->alias] = array();

		foreach ($config as $field => $options) {
			$this->_setupField($model, $field, $options);
		}
	}

/**
 * Setup a particular upload field
 *
 * @param Model $model Model instance
 * @param string $field Name of field being modified
 * @param array $options array of configuration settings for a field
 * @return void
 */
	protected function _setupField(Model $model, $field, $options) {
		if (is_int($field)) {
			$field = $options;
			$options = array();
		}

		$this->defaults['rootDir'] = ROOT . DS . APP_DIR . DS;
		if (!isset($this->settings[$model->alias][$field])) {
			$options = array_merge($this->defaults, (array)$options);

			// HACK: Remove me in next major version
			if (!empty($options['thumbsizes'])) {
				$options['thumbnailSizes'] = $options['thumbsizes'];
			}

			if (!empty($options['prefixStyle'])) {
				$options['thumbnailPrefixStyle'] = $options['prefixStyle'];
			}
			// ENDHACK

			$options['fields'] += $this->defaults['fields'];
			if ($options['rootDir'] === null) {
				$options['rootDir'] = $this->defaults['rootDir'];
			}

			if ($options['thumbnailName'] === null) {
				if ($options['thumbnailPrefixStyle']) {
					$options['thumbnailName'] = '{size}_{filename}';
				} else {
					$options['thumbnailName'] = '{filename}_{size}';
				}
			}

			if ($options['thumbnailPath'] === null) {
				$options['thumbnailPath'] = Folder::slashTerm($this->_path($model, $field, array(
					'isThumbnail' => true,
					'path' => $options['path'],
					'rootDir' => $options['rootDir']
				)));
			} else {
				$options['thumbnailPath'] = Folder::slashTerm($this->_path($model, $field, array(
					'isThumbnail' => true,
					'path' => $options['thumbnailPath'],
					'rootDir' => $options['rootDir']
				)));
			}

			$options['path'] = Folder::slashTerm($this->_path($model, $field, array(
				'isThumbnail' => false,
				'path' => $options['path'],
				'rootDir' => $options['rootDir']
			)));

			if (!in_array($options['thumbnailMethod'], $this->_resizeMethods)) {
				$options['thumbnailMethod'] = 'imagick';
			}
			if (!in_array($options['pathMethod'], $this->_pathMethods)) {
				$options['pathMethod'] = 'primaryKey';
			}
			$options['pathMethod'] = '_getPath' . Inflector::camelize($options['pathMethod']);
			$options['thumbnailMethod'] = '_resize' . Inflector::camelize($options['thumbnailMethod']);
			$this->settings[$model->alias][$field] = $options;
		}
	}

/**
 * Convenience method for configuring UploadBehavior settings
 *
 * @param Model $model Model instance
 * @param string $field Name of field being modified
 * @param mixed $one A string or an array of data.
 * @param mixed $two Value in case $one is a string (which then works as the key).
 *   Unused if $one is an associative array, otherwise serves as the values to $one's keys.
 * @return void
 */
	public function uploadSettings(Model $model, $field, $one, $two = null) {
		if (empty($this->settings[$model->alias][$field])) {
			$this->_setupField($model, $field, array());
		}

		$data = array();

		if (is_array($one)) {
			if (is_array($two)) {
				$data = array_combine($one, $two);
			} else {
				$data = $one;
			}
		} else {
			$data = array($one => $two);
		}
		$this->settings[$model->alias][$field] = $data + $this->settings[$model->alias][$field];
	}

/**
 * Before save method. Called before all saves
 *
 * Handles setup of file uploads
 *
 * @param Model $model Model instance
 * @param array $options
 * @return boolean
 */
	public function beforeSave(Model $model, $options = array()) {
		$this->_removingOnly = array();
		foreach ($this->settings[$model->alias] as $field => $options) {
			if (!isset($model->data[$model->alias][$field]) || !is_array($model->data[$model->alias][$field])) {
				// it may have previously been set by a prior save using this same instance
				unset($this->runtime[$model->alias][$field]);
				continue;
			}

			$this->runtime[$model->alias][$field] = $model->data[$model->alias][$field];

			$removing = !empty($model->data[$model->alias][$field]['remove']);
			if ($removing || ($this->settings[$model->alias][$field]['deleteOnUpdate']
			&& isset($model->data[$model->alias][$field]['name'])
			&& strlen($model->data[$model->alias][$field]['name']))) {
				// We're updating the file, remove old versions
				if (!empty($model->id)) {
					$data = $model->find('first', array(
						'conditions' => array("{$model->alias}.{$model->primaryKey}" => $model->id),
						'contain' => false,
						'recursive' => -1,
					));
					$this->_prepareFilesForDeletion($model, $field, $data, $options);
				}

				if ($removing) {
					$model->data[$model->alias] = array(
						$field => null,
						$options['fields']['type'] => null,
						$options['fields']['size'] => null,
						$options['fields']['dir'] => null,
					);

					$this->_removingOnly[$field] = true;
					continue;
				} else {
					$model->data[$model->alias][$field] = array(
						$field => null,
						$options['fields']['type'] => null,
						$options['fields']['size'] => null,
					);
				}
			} elseif (!isset($model->data[$model->alias][$field]['name']) || !strlen($model->data[$model->alias][$field]['name'])) {
				// if field is empty, don't delete/nullify existing file
				unset($model->data[$model->alias][$field]);
				continue;
			}

			$model->data[$model->alias] = array_merge($model->data[$model->alias], array(
				$field => $this->runtime[$model->alias][$field]['name'],
				$options['fields']['type'] => $this->runtime[$model->alias][$field]['type'],
				$options['fields']['size'] => $this->runtime[$model->alias][$field]['size']
			));
		}
		return true;
	}

/**
 * Transform Model.field value like as PHP upload array (name, tmp_name)
 * for UploadBehavior plugin processing.
 *
 * @param Model $model Model instance
 * @param array $options
 * @return boolean
 */
	public function beforeValidate(Model $model, $options = array()) {
		foreach ($this->settings[$model->alias] as $field => $options) {
			if (!empty($model->data[$model->alias][$field]) && $this->_isURI($model->data[$model->alias][$field])) {
				$uri = $model->data[$model->alias][$field];
				if (!$this->_grab($model, $field, $uri)) {
					$model->invalidate($field, __d('upload', 'File was not downloaded.', true));
					return false;
				}
			}
		}
		return true;
	}

/**
 * After save method. Called after all saves
 *
 * Handles moving file uploads
 *
 * @param Model $model Model instance
 * @param boolean $created
 * @param array $options
 * @return boolean
 * @throws UploadException
 */
	public function afterSave(Model $model, $created, $options = array()) {
		$temp = array($model->alias => array());

		foreach ($this->settings[$model->alias] as $field => $options) {
			if (!in_array($field, array_keys($model->data[$model->alias]))) {
				continue;
			}

			if (empty($this->runtime[$model->alias][$field])) {
				continue;
			}

			if (isset($this->_removingOnly[$field])) {
				continue;
			}

			$tempPath = $this->_getPath($model, $field);

			$path = $this->settings[$model->alias][$field]['path'];
			$thumbnailPath = $this->settings[$model->alias][$field]['thumbnailPath'];

			if (!empty($tempPath)) {
				$path .= $tempPath . DS;
				$thumbnailPath .= $tempPath . DS;
			}
			$tmp = $this->runtime[$model->alias][$field]['tmp_name'];
			$filePath = $path . $model->data[$model->alias][$field];
			if (!$this->handleUploadedFile($model->alias, $field, $tmp, $filePath)) {
				CakeLog::error(sprintf('Model %s, Field %s: Unable to move the uploaded file to %s', $model->alias, $field, $filePath));
				$model->invalidate($field, sprintf('Unable to move the uploaded file to %s', $filePath));
				$db = $model->getDataSource();
				$db->rollback();
				throw new UploadException('Unable to upload file');
			}

			$this->_createThumbnails($model, $field, $path, $thumbnailPath);
			if ($model->hasField($options['fields']['dir'])) {
				if ($created && $options['pathMethod'] == '_getPathFlat') {
				} elseif ($options['saveDir']) {
					$db = $model->getDataSource();
					$temp[$model->alias][$options['fields']['dir']] = $db->value($tempPath, 'string');
				}
			}
		}

		if (!empty($temp[$model->alias])) {
			$model->updateAll($temp[$model->alias], array(
				$model->alias . '.' . $model->primaryKey => $model->id
			));
		}

		if (empty($this->__filesToRemove[$model->alias])) {
			return true;
		}
		foreach ($this->__filesToRemove[$model->alias] as $i => $file) {
			$result[] = $this->unlink($file);
			unset($this->__filesToRemove[$model->alias][$i]);
		}
		return $result;
	}

	public function handleUploadedFile($modelAlias, $field, $tmp, $filePath) {
		if (is_uploaded_file($tmp)) {
			return move_uploaded_file($tmp, $filePath);
		} else {
			return rename($tmp, $filePath);
		}
	}

	public function unlink($file) {
		if (file_exists($file)) {
			return unlink($file);
		}
		return true;
	}

	public function deleteFolder(Model $model, $path) {
		if (!isset($this->__foldersToRemove[$model->alias])) {
			return false;
		}

		$folders = $this->__foldersToRemove[$model->alias];
		foreach ($folders as $folder) {
			$dir = $path . $folder;
			$it = new RecursiveDirectoryIterator($dir);
			$files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
			foreach ($files as $file) {
				if ($file->getFilename() === '.' || $file->getFilename() === '..') {
					continue;
				}

				if ($file->isDir()) {
					rmdir($file->getRealPath());
				} else {
					unlink($file->getRealPath());
				}
			}
			rmdir($dir);
		}

		return true;
	}

	public function beforeDelete(Model $model, $cascade = true) {
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

	public function afterDelete(Model $model) {
		$result = array();
		if (!empty($this->__filesToRemove[$model->alias])) {
			foreach ($this->__filesToRemove[$model->alias] as $i => $file) {
				$result[] = $this->unlink($file);
				unset($this->__filesToRemove[$model->alias][$i]);
			}
		}

		foreach ($this->settings[$model->alias] as $field => $options) {
			if ($options['deleteFolderOnDelete'] == true) {
				$this->deleteFolder($model, $options['path']);
				return true;
			}
		}
		return $result;
	}

/**
 * Verify that the uploaded file has been moved to the
 * destination successfully. This rule is special that it
 * is invalidated in afterSave(). Therefore it is possible
 * for save() to return true and this rule to fail.
 *
 * @param Object $model
 * @return boolean Always true
 */
	public function moveUploadedFile(Model $model) {
		return true;
	}
/**
 * Check that the file does not exceed the max
 * file size specified by PHP
 *
 * @param Object $model
 * @param mixed $check Value to check
 * @return boolean Success
 */
	public function isUnderPhpSizeLimit(Model $model, $check) {
		$field = $this->_getField($check);

		if (!empty($check[$field]['remove'])) {
			return true;
		}

		return $check[$field]['error'] !== UPLOAD_ERR_INI_SIZE;
	}

/**
 * Check that the file does not exceed the max
 * file size specified in the HTML Form
 *
 * @param Object $model
 * @param mixed $check Value to check
 * @return boolean Success
 */
	public function isUnderFormSizeLimit(Model $model, $check) {
		$field = $this->_getField($check);

		if (!empty($check[$field]['remove'])) {
			return true;
		}

		return $check[$field]['error'] !== UPLOAD_ERR_FORM_SIZE;
	}

/**
 * Check that the file was completely uploaded
 *
 * @param Object $model
 * @param mixed $check Value to check
 * @return boolean Success
 */
	public function isCompletedUpload(Model $model, $check) {
		$field = $this->_getField($check);

		if (!empty($check[$field]['remove'])) {
			return true;
		}

		return $check[$field]['error'] !== UPLOAD_ERR_PARTIAL;
	}

/**
 * Check that a file was uploaded
 *
 * @param Object $model
 * @param mixed $check Value to check
 * @return boolean Success
 */
	public function isFileUpload(Model $model, $check) {
		$field = $this->_getField($check);

		if (!empty($check[$field]['remove'])) {
			return true;
		}

		return $check[$field]['error'] !== UPLOAD_ERR_NO_FILE;
	}

/**
 * Check that either a file was uploaded,
 * or the existing value in the database is not blank.
 *
 * @param Object $model
 * @param mixed $check Value to check
 * @return boolean Success
 */
	public function isFileUploadOrHasExistingValue(Model $model, $check) {
		if (!$this->isFileUpload($model, $check)) {
			$pkey = $model->primaryKey;
			if (!empty($model->data[$model->alias][$pkey])) {
				$field = $this->_getField($check);
				$fieldValue = $model->field($field, array($pkey => $model->data[$model->alias][$pkey]));
				return !empty($fieldValue);
			}

			return false;
		}
		return true;
	}

/**
 * Check that the PHP temporary directory is missing
 *
 * @param Object $model
 * @param mixed $check Value to check
 * @return boolean Success
 */
	public function tempDirExists(Model $model, $check, $requireUpload = true) {
		$field = $this->_getField($check);

		if (!empty($check[$field]['remove'])) {
			return true;
		}

		// Allow circumvention of this rule if uploads is not required
		if (!$requireUpload && $check[$field]['error'] === UPLOAD_ERR_NO_FILE) {
			return true;
		}

		return $check[$field]['error'] !== UPLOAD_ERR_NO_TMP_DIR;
	}

/**
 * Check that the file was successfully written to the server
 *
 * @param Object $model
 * @param mixed $check Value to check
 * @return boolean Success
 */
	public function isSuccessfulWrite(Model $model, $check, $requireUpload = true) {
		$field = $this->_getField($check);

		if (!empty($check[$field]['remove'])) {
			return true;
		}

		// Allow circumvention of this rule if uploads is not required
		if (!$requireUpload && $check[$field]['error'] === UPLOAD_ERR_NO_FILE) {
			return true;
		}

		return $check[$field]['error'] !== UPLOAD_ERR_CANT_WRITE;
	}

/**
 * Check that a PHP extension did not cause an error
 *
 * @param Object $model
 * @param mixed $check Value to check
 * @return boolean Success
 */
	public function noPhpExtensionErrors(Model $model, $check, $requireUpload = true) {
		$field = $this->_getField($check);

		if (!empty($check[$field]['remove'])) {
			return true;
		}

		// Allow circumvention of this rule if uploads is not required
		if (!$requireUpload && $check[$field]['error'] === UPLOAD_ERR_NO_FILE) {
			return true;
		}

		return $check[$field]['error'] !== UPLOAD_ERR_EXTENSION;
	}

/**
 * Check that the file is of a valid mimetype
 *
 * @param Object $model
 * @param mixed $check Value to check
 * @param array $mimetypes file mimetypes to allow
 * @return boolean Success
 */
	public function isValidMimeType(Model $model, $check, $mimetypes = array(), $requireUpload = true) {
		$field = $this->_getField($check);

		if (!empty($check[$field]['remove'])) {
			return true;
		}

		// Allow circumvention of this rule if uploads is not required
		if (!$requireUpload && $check[$field]['error'] === UPLOAD_ERR_NO_FILE) {
			return true;
		}

		// Non-file uploads also mean the mimetype is invalid
		if (!isset($check[$field]['type']) || !strlen($check[$field]['type'])) {
			return false;
		}

		// Sometimes the user passes in a string instead of an array
		if (is_string($mimetypes)) {
			$mimetypes = array($mimetypes);
		}

		foreach ($mimetypes as $key => $value) {
			if (!is_int($key)) {
				$mimetypes = $this->settings[$model->alias][$field]['mimetypes'];
				break;
			}
		}

		if (empty($mimetypes)) {
			$mimetypes = $this->settings[$model->alias][$field]['mimetypes'];
		}

		return in_array($check[$field]['type'], $mimetypes);
	}

/**
 * Check that the upload directory is writable
 *
 *
 * @param Object $model
 * @param mixed $check Value to check
 * @param string $path Full upload path
 * @return boolean Success
 */
	public function isWritable(Model $model, $check, $requireUpload = true) {
		$field = $this->_getField($check);

		if (!empty($check[$field]['remove'])) {
			return true;
		}

		// Allow circumvention of this rule if uploads is not required
		if (!$requireUpload && $check[$field]['error'] === UPLOAD_ERR_NO_FILE) {
			return true;
		}

		return is_writable($this->settings[$model->alias][$field]['path']);
	}

/**
 * Check that the upload directory exists
 *
 * @param Object $model
 * @param mixed $check Value to check
 * @param string $path Full upload path
 * @return boolean Success
 */
	public function isValidDir(Model $model, $check, $requireUpload = true) {
		$field = $this->_getField($check);

		if (!empty($check[$field]['remove'])) {
			return true;
		}

		// Allow circumvention of this rule if uploads is not required
		if (!$requireUpload && $check[$field]['error'] === UPLOAD_ERR_NO_FILE) {
			return true;
		}

		return is_dir($this->settings[$model->alias][$field]['path']);
	}

/**
 * Check that the file is below the maximum file upload size
 *
 * @param Object $model
 * @param mixed $check Value to check
 * @param int $size Maximum file size
 * @return boolean Success
 */
	public function isBelowMaxSize(Model $model, $check, $size = null, $requireUpload = true) {
		$field = $this->_getField($check);

		if (!empty($check[$field]['remove'])) {
			return true;
		}

		// Allow circumvention of this rule if uploads is not required
		if (!$requireUpload && $check[$field]['error'] === UPLOAD_ERR_NO_FILE) {
			return true;
		}

		// Non-file uploads also mean the size is too small
		if (!isset($check[$field]['size']) || !strlen($check[$field]['size'])) {
			return false;
		}

		if (!$size) {
			$size = $this->settings[$model->alias][$field]['maxSize'];
		}

		return $check[$field]['size'] <= $size;
	}

/**
 * Check that the file is above the minimum file upload size
 *
 * @param Object $model
 * @param mixed $check Value to check
 * @param int $size Minimum file size
 * @return boolean Success
 */
	public function isAboveMinSize(Model $model, $check, $size = null, $requireUpload = true) {
		$field = $this->_getField($check);

		if (!empty($check[$field]['remove'])) {
			return true;
		}

		// Allow circumvention of this rule if uploads is not required
		if (!$requireUpload && $check[$field]['error'] === UPLOAD_ERR_NO_FILE) {
			return true;
		}

		// Non-file uploads also mean the size is too small
		if (!isset($check[$field]['size']) || !strlen($check[$field]['size'])) {
			return false;
		}

		if (!$size) {
			$size = $this->settings[$model->alias][$field]['minSize'];
		}

		return $check[$field]['size'] >= $size;
	}

/**
 * Check that the file has a valid extension
 *
 * @param Object $model
 * @param mixed $check Value to check
 * @param array $extensions file extenstions to allow
 * @return boolean Success
 */
	public function isValidExtension(Model $model, $check, $extensions = array(), $requireUpload = true) {
		$field = $this->_getField($check);

		if (!empty($check[$field]['remove'])) {
			return true;
		}

		// Allow circumvention of this rule if uploads is not required
		if (!$requireUpload && $check[$field]['error'] === UPLOAD_ERR_NO_FILE) {
			return true;
		}

		// Non-file uploads also mean the extension is invalid
		if (!isset($check[$field]['name']) || !strlen($check[$field]['name'])) {
			return false;
		}

		// Sometimes the user passes in a string instead of an array
		if (is_string($extensions)) {
			$extensions = array($extensions);
		}

		// Sometimes a user does not specify any extensions in the validation rule
		foreach ($extensions as $key => $value) {
			if (!is_int($key)) {
				$extensions = $this->settings[$model->alias][$field]['extensions'];
				break;
			}
		}

		if (empty($extensions)) {
			$extensions = $this->settings[$model->alias][$field]['extensions'];
		}

		$pathInfo = $this->_pathinfo($check[$field]['name']);

		$extensions = array_map('strtolower', $extensions);
		return in_array(strtolower($pathInfo['extension']), $extensions);
	}

/**
 * Check that the file is above the minimum height requirement
 *
 * @param Object $model
 * @param mixed $check Value to check
 * @param int $height Height of Image
 * @return boolean Success
 */
	public function isAboveMinHeight(Model $model, $check, $height = null, $requireUpload = true) {
		$field = $this->_getField($check);

		if (!empty($check[$field]['remove'])) {
			return true;
		}

		// Allow circumvention of this rule if uploads is not required
		if (!$requireUpload && $check[$field]['error'] === UPLOAD_ERR_NO_FILE) {
			return true;
		}

		// Non-file uploads also mean the height is too big
		if (!isset($check[$field]['tmp_name']) || !strlen($check[$field]['tmp_name'])) {
			return false;
		}

		if (!$height) {
			$height = $this->settings[$model->alias][$field]['minHeight'];
		}

		list($imgWidth, $imgHeight) = getimagesize($check[$field]['tmp_name']);
		return $height > 0 && $imgHeight >= $height;
	}

/**
 * Check that the file is below the maximum height requirement
 *
 * @param Object $model
 * @param mixed $check Value to check
 * @param int $height Height of Image
 * @return boolean Success
 */
	public function isBelowMaxHeight(Model $model, $check, $height = null, $requireUpload = true) {
		$field = $this->_getField($check);

		if (!empty($check[$field]['remove'])) {
			return true;
		}

		// Allow circumvention of this rule if uploads is not required
		if (!$requireUpload && $check[$field]['error'] === UPLOAD_ERR_NO_FILE) {
			return true;
		}

		// Non-file uploads also mean the height is too big
		if (!isset($check[$field]['tmp_name']) || !strlen($check[$field]['tmp_name'])) {
			return false;
		}

		if (!$height) {
			$height = $this->settings[$model->alias][$field]['maxHeight'];
		}

		list($imgWidth, $imgHeight) = getimagesize($check[$field]['tmp_name']);
		return $height > 0 && $imgHeight <= $height;
	}

/**
 * Check that the file is above the minimum width requirement
 *
 * @param Object $model
 * @param mixed $check Value to check
 * @param int $width Width of Image
 * @return boolean Success
 */
	public function isAboveMinWidth(Model $model, $check, $width = null, $requireUpload = true) {
		$field = $this->_getField($check);

		if (!empty($check[$field]['remove'])) {
			return true;
		}

		// Allow circumvention of this rule if uploads is not required
		if (!$requireUpload && $check[$field]['error'] === UPLOAD_ERR_NO_FILE) {
			return true;
		}

		// Non-file uploads also mean the height is too big
		if (!isset($check[$field]['tmp_name']) || !strlen($check[$field]['tmp_name'])) {
			return false;
		}

		if (!$width) {
			$width = $this->settings[$model->alias][$field]['minWidth'];
		}

		list($imgWidth, $imgHeight) = getimagesize($check[$field]['tmp_name']);
		return $width > 0 && $imgWidth >= $width;
	}

/**
 * Check that the file is below the maximum width requirement
 *
 * @param Object $model
 * @param mixed $check Value to check
 * @param int $width Width of Image
 * @return boolean Success
 */
	public function isBelowMaxWidth(Model $model, $check, $width = null, $requireUpload = true) {
		$field = $this->_getField($check);

		if (!empty($check[$field]['remove'])) {
			return true;
		}

		// Allow circumvention of this rule if uploads is not required
		if (!$requireUpload && $check[$field]['error'] === UPLOAD_ERR_NO_FILE) {
			return true;
		}

		// Non-file uploads also mean the height is too big
		if (!isset($check[$field]['tmp_name']) || !strlen($check[$field]['tmp_name'])) {
			return false;
		}

		if (!$width) {
			$width = $this->settings[$model->alias][$field]['maxWidth'];
		}

		list($imgWidth, $imgHeight) = getimagesize($check[$field]['tmp_name']);
		return $width > 0 && $imgWidth <= $width;
	}

	protected function _resizeImagick(Model $model, $field, $path, $size, $geometry, $thumbnailPath) {
		$srcFile = $path . $model->data[$model->alias][$field];
		$pathInfo = $this->_pathinfo($srcFile);
		$thumbnailType = $imageFormat = $this->settings[$model->alias][$field]['thumbnailType'];

		$isMedia = $this->_isMedia($model, $this->runtime[$model->alias][$field]['type']);
		$image = new imagick();

		if ($isMedia) {
			$image->setResolution(300, 300);
			$srcFile = $srcFile . '[0]';
		}

		$image->readImage($srcFile);
		$this->_exifRotateImagick($image);
		$height = $image->getImageHeight();
		$width = $image->getImageWidth();

		if (preg_match('/^\\[[\\d]+x[\\d]+\\]$/', $geometry)) {
			// resize with banding
			list($destW, $destH) = explode('x', substr($geometry, 1, strlen($geometry) - 2));
			$image->thumbnailImage($destW, $destH, true);
			$imageGeometry = $image->getImageGeometry();
			$x = ($imageGeometry['width'] - $destW) / 2;
			$y = ($imageGeometry['height'] - $destH) / 2;
			$image->setGravity(Imagick::GRAVITY_CENTER);
			$image->extentImage($destW, $destH, $x, $y);
		} elseif (preg_match('/^[\\d]+x[\\d]+$/', $geometry)) {
			// cropped resize (best fit)
			list($destW, $destH) = explode('x', $geometry);
			$image->cropThumbnailImage($destW, $destH);
		} elseif (preg_match('/^[\\d]+w$/', $geometry)) {
			// calculate heigh according to aspect ratio
			$image->thumbnailImage((int)$geometry, 0);
		} elseif (preg_match('/^[\\d]+h$/', $geometry)) {
			// calculate width according to aspect ratio
			$image->thumbnailImage(0, (int)$geometry);
		} elseif (preg_match('/^[\\d]+l$/', $geometry)) {
			// calculate shortest side according to aspect ratio
			$destW = 0;
			$destH = 0;
			$destW = ($width > $height) ? (int)$geometry : 0;
			$destH = ($width > $height) ? 0 : (int)$geometry;

			$imagickVersion = phpversion('imagick');
			$image->thumbnailImage($destW, $destH, !($imagickVersion[0] == 3));
		} elseif (preg_match('/^[\\d]+mw$/', $geometry)) {
			if ((int)$geometry < $width) {
				$image->thumbnailImage((int)$geometry, 0);
			}
		} elseif (preg_match('/^[\\d]+mh$/', $geometry)) {
			if ((int)$geometry < $height) {
				$image->thumbnailImage(0, (int)$geometry);
			}
		} elseif (preg_match('/^[\\d]+ml$/', $geometry)) {
			// calculate shortest side according to aspect ratio
			$destW = 0;
			$destH = 0;
			$destW = ($width > $height) ? (int)$geometry : 0;
			$destH = ($width > $height) ? 0 : (int)$geometry;

			if ($destH < $height && $destW < $width) {
				$imagickVersion = phpversion('imagick');
				$image->thumbnailImage($destW, $destH, !($imagickVersion[0] == 3));
			}
		}

		if ($isMedia) {
			$thumbnailType = $imageFormat = $this->settings[$model->alias][$field]['mediaThumbnailType'];
		}

		if (!$thumbnailType || !is_string($thumbnailType)) {
			try {
				$thumbnailType = $imageFormat = $image->getImageFormat();
				// Fix file casing
				while (true) {
					$ext = false;
					$pieces = explode('.', $srcFile);
					if (count($pieces) > 1) {
						$ext = end($pieces);
					}

					if (!$ext || !strlen($ext)) {
						break;
					}

					$low = array(
						'ext' => strtolower($ext),
						'thumbnailType' => strtolower($thumbnailType),
					);

					if ($low['ext'] == 'jpg' && $low['thumbnailType'] == 'jpeg') {
						$thumbnailType = $ext;
						break;
					}

					if ($low['ext'] == $low['thumbnailType']) {
						$thumbnailType = $ext;
					}

					break;
				}
			} catch (Exception $e) {$this->log($e->getMessage(), 'upload');
				$thumbnailType = $imageFormat = 'png';
			}
		}

		$fileName = str_replace(
			array('{size}', '{geometry}', '{filename}', '{primaryKey}'),
			array($size, $geometry, $pathInfo['filename'], $model->id),
			$this->settings[$model->alias][$field]['thumbnailName']
		);

		$destFile = "{$thumbnailPath}{$fileName}.{$thumbnailType}";

		$image->setImageCompressionQuality($this->settings[$model->alias][$field]['thumbnailQuality']);
		$image->setImageFormat($imageFormat);
		if (!$image->writeImage($destFile)) {
			return false;
		}

		$image->clear();
		$image->destroy();
		return true;
	}

	protected function _resizePhp(Model $model, $field, $path, $size, $geometry, $thumbnailPath) {
		$srcFile = $path . $model->data[$model->alias][$field];
		$pathInfo = $this->_pathinfo($srcFile);
		$thumbnailType = $this->settings[$model->alias][$field]['thumbnailType'];

		if (!$thumbnailType || !is_string($thumbnailType)) {
			$thumbnailType = $pathInfo['extension'];
		}

		if (!$thumbnailType) {
			$thumbnailType = 'png';
		}

		$fileName = str_replace(
			array('{size}', '{geometry}', '{filename}', '{primaryKey}'),
			array($size, $geometry, $pathInfo['filename'], $model->id),
			$this->settings[$model->alias][$field]['thumbnailName']
		);

		$destFile = "{$thumbnailPath}{$fileName}.{$thumbnailType}";

		copy($srcFile, $destFile);
		$src = null;
		$outputHandler = null;

		$supportsThumbnailQuality = false;
		$adjustedThumbnailQuality = $this->settings[$model->alias][$field]['thumbnailQuality'];
		switch (strtolower($thumbnailType)) {
			case 'gif':
				$outputHandler = 'imagegif';
				break;
			case 'jpg':
			case 'jpeg':
				$outputHandler = 'imagejpeg';
				$supportsThumbnailQuality = true;
				break;
			case 'png':
				$outputHandler = 'imagepng';
				$supportsThumbnailQuality = true;
				// convert 0 (lowest) - 100 (highest) thumbnailQuality, to 0 (highest) - 9 (lowest) quality (see http://php.net/manual/en/function.imagepng.php)
				$adjustedThumbnailQuality = intval((100 - $this->settings[$model->alias][$field]['thumbnailQuality']) / 100 * 9);
				break;
			default:
				return false;
		}

		$src = $this->_createImageResource($destFile, $pathInfo);
		if ($src) {

			$srcW = imagesx($src);
			$srcH = imagesy($src);

			// determine destination dimensions and resize mode from provided geometry
			if (preg_match('/^\\[[\\d]+x[\\d]+\\]$/', $geometry)) {
				// resize with banding
				list($destW, $destH) = explode('x', substr($geometry, 1, strlen($geometry) - 2));
				$resizeMode = 'band';
			} elseif (preg_match('/^[\\d]+x[\\d]+$/', $geometry)) {
				// cropped resize (best fit)
				list($destW, $destH) = explode('x', $geometry);
				$resizeMode = 'best';
			} elseif (preg_match('/^[\\d]+w$/', $geometry)) {
				// calculate heigh according to aspect ratio
				$destW = (int)$geometry;
				$resizeMode = false;
			} elseif (preg_match('/^[\\d]+h$/', $geometry)) {
				// calculate width according to aspect ratio
				$destH = (int)$geometry;
				$resizeMode = false;
			} elseif (preg_match('/^[\\d]+l$/', $geometry)) {
				// calculate shortest side according to aspect ratio
				if ($srcW > $srcH) {
					$destW = (int)$geometry;
				} else {
					$destH = (int)$geometry;
				}
				$resizeMode = false;
			} elseif (preg_match('/^[\\d]+mw$/', $geometry)) {
				// calculate heigh according to aspect ratio
				if ((int)$geometry < $srcW) {
					$destW = (int)$geometry;
				} else {
					$destW = $srcW;
				}
				$resizeMode = false;
			} elseif (preg_match('/^[\\d]+mh$/', $geometry)) {
				// calculate width according to aspect ratio
				if ((int)$geometry < $srcH) {
					$destH = (int)$geometry;
				} else {
					$destH = $srcH;
				}
				$resizeMode = false;
			} elseif (preg_match('/^[\\d]+ml$/', $geometry)) {
				// calculate shortest side according to aspect ratio
				if ($srcW > $srcH) {
					if ((int)$geometry < $srcW) {
						$destW = (int)$geometry;
					} else {
						$destW = $srcW;
					}
				} else {
					if ((int)$geometry < $srcH) {
						$destH = (int)$geometry;
					} else {
						$destH = $srcH;
					}
				}
				$resizeMode = false;
			}

			if (!isset($destW)) {
				$destW = ($destH / $srcH) * $srcW;
			}

			if (!isset($destH)) {
				$destH = ($destW / $srcW) * $srcH;
			}

			// determine resize dimensions from appropriate resize mode and ratio
			if ($resizeMode == 'best') {
				// "best fit" mode
				if ($srcW > $srcH) {
					if ($srcH / $destH > $srcW / $destW) {
						$ratio = $destW / $srcW;
					} else {
						$ratio = $destH / $srcH;
					}
				} else {
					if ($srcH / $destH < $srcW / $destW) {
						$ratio = $destH / $srcH;
					} else {
						$ratio = $destW / $srcW;
					}
				}
				$resizeW = $srcW * $ratio;
				$resizeH = $srcH * $ratio;
			} elseif ($resizeMode == 'band') {
				// "banding" mode
				if ($srcW > $srcH) {
					$ratio = $destW / $srcW;
				} else {
					$ratio = $destH / $srcH;
				}
				$resizeW = $srcW * $ratio;
				$resizeH = $srcH * $ratio;
			} else {
				// no resize ratio
				$resizeW = $destW;
				$resizeH = $destH;
			}

			$img = imagecreatetruecolor($destW, $destH);
			imagealphablending($img, false);
			imagesavealpha($img, true);
			imagefill($img, 0, 0, imagecolorallocate($img, 255, 255, 255));
			imagecopyresampled($img, $src, ($destW - $resizeW) / 2, ($destH - $resizeH) / 2, 0, 0, $resizeW, $resizeH, $srcW, $srcH);

			if ($supportsThumbnailQuality) {
				$outputHandler($img, $destFile, $adjustedThumbnailQuality);
			} else {
				$outputHandler($img, $destFile);
			}

			return true;
		}
		return false;
	}

	protected function _createImageResource($filename, $pathInfo) {
		switch (strtolower($pathInfo['extension'])) {
			case 'gif':
				$src = imagecreatefromgif($filename);
				break;
			case 'jpg':
			case 'jpeg':
				$src = $this->_imagecreatefromjpegexif($filename);
				break;
			case 'png':
				$src = imagecreatefrompng($filename);
				break;
			default:
				return false;
		}

		return $src;
	}

/**
 * Same as imagecreatefromjpeg, but honouring the file's Exif data.
 * See http://www.php.net/manual/en/function.imagecreatefromjpeg.php#112902
 */
	protected function _imagecreatefromjpegexif($filename) {
		$image = imagecreatefromjpeg($filename);
		$exif = false;
		if (function_exists('exif_read_data')) {
			$exif = exif_read_data($filename);
		}

		if ($image && $exif && isset($exif['Orientation'])) {
			$ort = $exif['Orientation'];
		} else {
			return $image;
		}

		$trans = $this->_exifOrientationTransformations($ort);

		if ($trans['flip_vert']) {
			$image = $this->_flipImage($image, 'vert');
		}

		if ($trans['flip_horz']) {
			$image = $this->_flipImage($image, 'horz');
		}

		if ($trans['rotate_clockwise']) {
			$image = imagerotate($image, -1 * $trans['rotate_clockwise'], 0);
		}

		return $image;
	}

/**
 * Determine what transformations need to be applied to an image,
 * in order to maintain it's orientation and get rid of it's Exif Orientation data
 * http://www.impulseadventure.com/photo/exif-orientation.html
 * @param  int $orientation The exif orientation of the image
 * @return array of transformations - array keys are:
 * 'flip_vert' - true if the image needs to be flipped vertically
 * 'flip_horz' - true if the image needs to be flipped horizontally
 * 'rotate_clockwise' - number of degrees image needs to be rotated, clockwise
 */
	protected function _exifOrientationTransformations($orientation) {
		$trans = array(
			'flip_vert' => false,
			'flip_horz' => false,
			'rotate_clockwise' => 0,
		);

		switch($orientation) {
			case 1:
				break;

			case 2:
				$trans['flip_horz'] = true;
				break;

			case 3:
				$trans['rotate_clockwise'] = 180;
				break;

			case 4:
				$trans['flip_vert'] = true;
				break;

			case 5:
				$trans['flip_vert'] = true;
				$trans['rotate_clockwise'] = 90;
				break;

			case 6:
				$trans['rotate_clockwise'] = 90;
				break;

			case 7:
				$trans['flip_horz'] = true;
				$trans['rotate_clockwise'] = 90;
				break;

			case 8:
				$trans['rotate_clockwise'] = -90;
				break;
		}

		return $trans;
	}

/**
 * Flip an image object. Code from http://www.roscripts.com/snippets/show/55
 * @param  resource $img An image resource, such as one returned by imagecreatefromjpeg()
 * @param  string $type 'horz' or 'vert'
 * @return resource The flipped image
 */
	protected function _flipImage($img, $type) {
		$width = imagesx($img);
		$height = imagesy($img);
		$dest = imagecreatetruecolor($width, $height);
		switch($type){
			case 'vert':
				for ($i = 0; $i < $height; $i++) {
					imagecopy($dest, $img, 0, ($height - $i - 1), 0, $i, $width, 1);
				}
				break;
			case 'horz':
				for ($i = 0; $i < $width; $i++) {
					imagecopy($dest, $img, ($width - $i - 1), 0, $i, 0, 1, $height);
				}
				break;
		}
		return $dest;
	}

/**
 * rotate an imagick object based on it's exif data.
 * @param  imagick $image an instance of imagick
 */
	protected function _exifRotateImagick($image) {
		$orientation = $image->getImageOrientation();
		$trans = $this->_exifOrientationTransformations($orientation);

		if ($trans['flip_vert']) {
			$image->flopImage();
		}

		if ($trans['flip_horz']) {
			$image->flipImage();
		}

		if ($trans['rotate_clockwise']) {
			$image->rotateimage("#000", $trans['rotate_clockwise']);
		}

		$image->setImageOrientation(imagick::ORIENTATION_TOPLEFT);
	}

	protected function _getPath(Model $model, $field) {
		$path = $this->settings[$model->alias][$field]['path'];
		$pathMethod = $this->settings[$model->alias][$field]['pathMethod'];

		if (method_exists($this, $pathMethod)) {
			return $this->$pathMethod($model, $field, $path);
		}

		return $this->_getPathPrimaryKey($model, $field, $path);
	}

	protected function _getPathFlat(Model $model, $field, $path) {
		$destDir = $path;
		$this->_mkPath($model, $field, $destDir);
		return '';
	}

	protected function _getPathPrimaryKey(Model $model, $field, $path) {
		$destDir = $path . $model->id . DIRECTORY_SEPARATOR;
		$this->_mkPath($model, $field, $destDir);
		return $model->id;
	}

	protected function _getPathRandom(Model $model, $field, $path) {
		$endPath = null;
		$decrement = 0;
		$string = crc32($field . microtime());

		for ($i = 0; $i < 3; $i++) {
			$decrement = $decrement - 2;
			$endPath .= sprintf("%02d" . DIRECTORY_SEPARATOR, substr('000000' . $string, $decrement, 2));
		}

		$destDir = $path . $endPath;
		$this->_mkPath($model, $field, $destDir);

		return substr($endPath, 0, -1);
	}

	protected function _getPathRandomCombined(Model $model, $field, $path) {
		$endPath = $model->id . DIRECTORY_SEPARATOR;
		$decrement = 0;
		$string = crc32($field . microtime() . $model->id);

		for ($i = 0; $i < 3; $i++) {
			$decrement = $decrement - 2;
			$endPath .= sprintf("%02d" . DIRECTORY_SEPARATOR, substr('000000' . $string, $decrement, 2));
		}

		$destDir = $path . $endPath;
		$this->_mkPath($model, $field, $destDir);

		return substr($endPath, 0, -1);
	}

/**
 * Download remote file into PHP's TMP dir
 */
	protected function _grab(Model $model, $field, $uri) {
		$socket = new HttpSocket(array(
			'ssl_verify_host' => false
		));
		$file = $socket->get($uri, array(), array('redirect' => true));
		$headers = $socket->response['header'];
		$fileName = basename($socket->request['uri']['path']);
		$tmpFile = sys_get_temp_dir() . '/' . $fileName;

		if ($socket->response['status']['code'] != 200) {
			return false;
		}

		if (isset($model->data[$model->alias]['file_name_override'])) {
			$fileName = $model->data[$model->alias]['file_name_override'] . '.' . pathinfo($socket->request['uri']['path'], PATHINFO_EXTENSION);
		}

		$model->data[$model->alias][$field] = array(
			'name' => $fileName,
			'type' => $headers['Content-Type'],
			'tmp_name' => $tmpFile,
			'error' => 1,
			'size' => (isset($headers['content-length']) ? $headers['Content-Length'] : 0),
		);

		$file = file_put_contents($tmpFile, $socket->response['body']);
		if (!$file) {
			return false;
		}

		$model->data[$model->alias][$field]['error'] = 0;
		return true;
	}

	protected function _mkPath(Model $model, $field, $destDir) {
		if (!file_exists($destDir)) {
			mkdir($destDir, $this->settings[$model->alias][$field]['mode'], true);
			chmod($destDir, $this->settings[$model->alias][$field]['mode']);
		}
		return true;
	}

/**
 * Returns a path based on settings configuration
 *
 * @return string
 **/
	protected function _path(Model $model, $fieldName, $options = array()) {
		$defaults = array(
			'isThumbnail' => true,
			'path' => '{ROOT}webroot{DS}files{DS}{model}{DS}{field}{DS}',
			'rootDir' => $this->defaults['rootDir'],
		);

		$options = array_merge($defaults, $options);

		foreach ($options as $key => $value) {
			if ($value === null) {
				$options[$key] = $defaults[$key];
			}
		}

		if (!$options['isThumbnail']) {
			$options['path'] = str_replace(array('{size}', '{geometry}'), '', $options['path']);
		}

		$replacements = array(
			'{ROOT}'	=> $options['rootDir'],
			'{primaryKey}'	=> $model->id,
			'{model}'	=> Inflector::underscore($model->alias),
			'{field}'	=> $fieldName,
			'{time}'	=> time(),
			'{microtime}'	=> microtime(),
			'{DS}'		=> DIRECTORY_SEPARATOR,
			'//'		=> DIRECTORY_SEPARATOR,
			'/'			=> DIRECTORY_SEPARATOR,
			'\\'		=> DIRECTORY_SEPARATOR,
		);

		$newPath = Folder::slashTerm(str_replace(
			array_keys($replacements),
			array_values($replacements),
			$options['path']
		));

		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			if (!preg_match('/^([a-zA-Z]:\\\|\\\\)/', $newPath)) {
				$newPath = $options['rootDir'] . $newPath;
			}
		} elseif ($newPath[0] !== DIRECTORY_SEPARATOR) {
			$newPath = $options['rootDir'] . $newPath;
		}

		$pastPath = $newPath;
		while (true) {
			$pastPath = $newPath;
			$newPath = str_replace(array(
				'//',
				'\\',
				DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR
			), DIRECTORY_SEPARATOR, $newPath);
			if ($pastPath == $newPath) {
				break;
			}
		}

		return $newPath;
	}

	protected function _pathThumbnail(Model $model, $field, $params = array()) {
		return str_replace(
			array('{size}', '{geometry}'),
			array($params['size'], $params['geometry']),
			$params['thumbnailPath']
		);
	}

/**
 * Creates thumbnails for images
 *
 * @param Model $model
 * @param string $field
 * @param string $path
 * @param string $thumbnailPath
 * @return void
 * @throws Exception
 */
	protected function _createThumbnails(Model $model, $field, $path, $thumbnailPath) {
		$isImage = $this->_isImage($model, $this->runtime[$model->alias][$field]['type']);
		$isMedia = $this->_isMedia($model, $this->runtime[$model->alias][$field]['type']);
		$createThumbnails = $this->settings[$model->alias][$field]['thumbnails'];
		$hasThumbnails = !empty($this->settings[$model->alias][$field]['thumbnailSizes']);

		if (($isImage || $isMedia) && $createThumbnails && $hasThumbnails) {
			$method = $this->settings[$model->alias][$field]['thumbnailMethod'];

			foreach ($this->settings[$model->alias][$field]['thumbnailSizes'] as $size => $geometry) {
				$thumbnailPathSized = $this->_pathThumbnail($model, $field, compact(
					'geometry', 'size', 'thumbnailPath'
				));
				$this->_mkPath($model, $field, $thumbnailPathSized);

				$valid = false;
				if (method_exists($model, $method)) {
					$valid = $model->$method($model, $field, $path, $size, $geometry, $thumbnailPathSized);
				} elseif (method_exists($this, $method)) {
					$valid = $this->$method($model, $field, $path, $size, $geometry, $thumbnailPathSized);
				} else {
					CakeLog::error(sprintf('Model %s, Field %s: Invalid thumbnailMethod %s', $model->alias, $field, $method));
					$db = $model->getDataSource();
					$db->rollback();
					throw new Exception("Invalid thumbnailMethod %s", $method);
				}

				if (!$valid) {
					$model->invalidate($field, 'resizeFail');
				}
			}
		}
	}

	protected function _isImage(Model $model, $mimetype) {
		return in_array($mimetype, $this->_imageMimetypes);
	}

	protected function _isURI($string) {
		return (filter_var($string, FILTER_VALIDATE_URL) ? true : false);
	}

	protected function _isMedia(Model $model, $mimetype) {
		return in_array($mimetype, $this->_mediaMimetypes);
	}

	protected function _getMimeType($filePath) {
		if (class_exists('finfo')) {
			$finfo = new finfo(defined('FILEINFO_MIME_TYPE') ? FILEINFO_MIME_TYPE : FILEINFO_MIME);
			return $finfo->file($filePath);
		}

		if (function_exists('exif_imagetype') && function_exists('image_type_to_mime_type')) {
			$mimetype = image_type_to_mime_type(exif_imagetype($filePath));
			if ($mimetype !== false) {
				return $mimetype;
			}
		}

		if (function_exists('mime_content_type')) {
			return mime_content_type($filePath);
		}

		return 'application/octet-stream';
	}

	protected function _prepareFilesForDeletion(Model $model, $field, $data, $options) {
		if (!strlen($data[$model->alias][$field])) {
			return $this->__filesToRemove;
		}

		if (!empty($options['fields']['dir']) && isset($data[$model->alias][$options['fields']['dir']])) {
			$dir = $data[$model->alias][$options['fields']['dir']];
		} else {
			if (in_array($options['pathMethod'], array('_getPathFlat', '_getPathPrimaryKey'))) {
				$model->id = $data[$model->alias][$model->primaryKey];
				$dir = call_user_func(array($this, '_getPath'), $model, $field);
			} else {
				CakeLog::error(sprintf('Cannot get directory to %s.%s: %s pathMethod is not supported.', $model->alias, $field, $options['pathMethod']));
			}
		}
		$filePathDir = $this->settings[$model->alias][$field]['path'] . (empty($dir) ? '' : $dir . DS);
		$filePath = $filePathDir . $data[$model->alias][$field];
		$pathInfo = $this->_pathinfo($filePath);

		if (!isset($this->__filesToRemove[$model->alias])) {
			$this->__filesToRemove[$model->alias] = array();
		}

		$this->__filesToRemove[$model->alias][] = $filePath;
		$this->__foldersToRemove[$model->alias][] = $dir;

		$createThumbnails = $options['thumbnails'];
		$hasThumbnails = !empty($options['thumbnailSizes']);

		if (!$createThumbnails || !$hasThumbnails) {
			return $this->__filesToRemove;
		}

		$DS = empty($dir) ? '' : DIRECTORY_SEPARATOR;
		$mimeType = $this->_getMimeType($filePath);
		$isMedia = $this->_isMedia($model, $mimeType);
		$isImagickResize = $options['thumbnailMethod'] == 'imagick';
		$thumbnailType = $options['thumbnailType'];

		if ($isImagickResize) {
			if ($isMedia) {
				$thumbnailType = $options['mediaThumbnailType'];
			}

			if (!$thumbnailType || !is_string($thumbnailType)) {
				try {
					$srcFile = $filePath;
					$image = new imagick();
					if ($isMedia) {
						$image->setResolution(300, 300);
						$srcFile = $srcFile . '[0]';
					}

					$image->readImage($srcFile);
					$thumbnailType = $image->getImageFormat();
				} catch (Exception $e) {
					$thumbnailType = 'png';
				}
			}
		} else {
			if (!$thumbnailType || !is_string($thumbnailType)) {
				$thumbnailType = $pathInfo['extension'];
			}

			if (!$thumbnailType) {
				$thumbnailType = 'png';
			}
		}

		foreach ($options['thumbnailSizes'] as $size => $geometry) {
			$fileName = str_replace(
				array('{size}', '{geometry}', '{filename}', '{primaryKey}', '{time}', '{microtime}'),
				array($size, $geometry, $pathInfo['filename'], $model->id, time(), microtime()),
				$options['thumbnailName']
			);

			$thumbnailPath = $options['thumbnailPath'];
			$thumbnailPath = $this->_pathThumbnail($model, $field, compact(
				'geometry', 'size', 'thumbnailPath'
			));

			$thumbnailFilePath = "{$thumbnailPath}{$dir}{$DS}{$fileName}.{$thumbnailType}";
			$this->__filesToRemove[$model->alias][] = $thumbnailFilePath;
		}
		return $this->__filesToRemove;
	}

	protected function _getField($check) {
		$fieldKeys = array_keys($check);
		return array_pop($fieldKeys);
	}

	protected function _pathinfo($filename) {
		$pathInfo = pathinfo($filename);

		if (!isset($pathInfo['extension']) || !strlen($pathInfo['extension'])) {
			$pathInfo['extension'] = '';
		}

		// PHP < 5.2.0 doesn't include 'filename' key in pathinfo. Let's try to fix this.
		if (empty($pathInfo['filename'])) {
			$pathInfo['filename'] = basename($pathInfo['basename'], '.' . $pathInfo['extension']);
		}
		return $pathInfo;
	}

}
