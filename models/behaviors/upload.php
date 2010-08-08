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
 * Check that the file was successfully writen to the server
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
	function isValidMimeType(&$model, $check, $mimetypes) {
		if (empty($mimetypes)) return false;
		$field = array_pop(array_keys($check));

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
	function isWritable(&$model, $check, $path = '/webroot/files') {
		return is_writable(APP_PATH . $path);
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
	function isValidDir(&$model, $check, $path = '/webroot/files') {
		return is_dir(APP_PATH . $path);
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
	function isBelowMaxSize(&$model, $check, $size = 2097152) {
		$field = array_pop(array_keys($check));
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
	function isAboveMinSize(&$model, $check, $size = 8) {
		$field = array_pop(array_keys($check));
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
		if (empty($extensions)) return false;
		$field = array_pop(array_keys($check));
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
	function isAboveMinHeight(&$model, $check, $height) {
		if ($height < 0) return false;
		$field = array_pop(array_keys($check));

		return imagesy($check[$field]['tmp_name']) >= $height;
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
	function isBelowMaxHeight(&$model, $check, $height) {
		if ($height < 0) return false;
		$field = array_pop(array_keys($check));

		return imagesy($check[$field]['tmp_name']) <= $height;
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
	function isAboveMinWidth(&$model, $check, $width) {
		if ($width < 0) return false;
		$field = array_pop(array_keys($check));

		return imagesx($check[$field]['tmp_name']) >= $width;
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
	function isBelowMaxWidth(&$model, $check, $options) {
		if ($width < 0) return false;
		$field = array_pop(array_keys($check));

		return imagesx($check[$field]['tmp_name']) <= $width;
	}

}
