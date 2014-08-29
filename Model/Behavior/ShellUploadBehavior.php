<?php
App::uses('UploadBehavior', 'Upload.Model/Behavior');

/**
 * A wrapping behavior for the UploadBehavior so that it's protected methods
 * can be accessed from the shell.
 *
 */
class ShellUploadBehavior extends UploadBehavior {

	public function path(Model $model, $field, $options = []) {
		return parent::_path($model, $field, $options);
	}

	public function createThumbnails(Model $model, $field, $path, $thumbnailPath) {
		return parent::_createThumbnails($model, $field, $path, $thumbnailPath);
	}
}