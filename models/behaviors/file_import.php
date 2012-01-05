<?php
if (!class_exists('UploadBehavior')) {
	App::import('Behavior', 'Upload.Upload');
}
class FileImportBehavior extends UploadBehavior {

	function handleUploadedFile($modelAlias, $field, $tmp, $filePath) {
			return !rename($tmp, $filePath);
	}
}
?>