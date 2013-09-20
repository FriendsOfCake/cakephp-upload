<?php
App::uses('UploadBehavior', 'Upload.Model/Behavior');
class FileImportBehavior extends UploadBehavior {

	public function handleUploadedFile($modelAlias, $field, $tmp, $filePath) {
		return !rename($tmp, $filePath);
	}

}
