<?php
App::uses('UploadBehavior', 'Upload.Model/Behavior');
class FileImportBehavior extends UploadBehavior {

	function handleUploadedFile($modelAlias, $field, $tmp, $filePath) {
		return !rename($tmp, $filePath);
	}

}