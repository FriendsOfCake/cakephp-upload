<?php
App::uses('UploadBehavior', 'Upload.Model/Behavior');

/**
 * A wrapping behavior for the UploadBehavior so that it's protected methods
 * can be accessed from the shell.
 *
 */
class ShellUploadBehavior extends UploadBehavior {

/**
 * Wrapper method for getting the path
 *
 * @param Model $model The instance of the model
 * @param string $field The name of the upload field
 * @param array $options The options array
 * @return string
 */
	public function path(Model $model, $field, $options = array()) {
		return parent::_path($model, $field, $options);
	}

/**
 * Wrapper method for thumbnail creation
 *
 * @param Model $model The model instance
 * @param string $field The name of the upload field
 * @param string $path The path to the source image
 * @param string $thumbnailPath The path in which to create the thumbnails
 * @throws Exception
 * @return void
 */
	public function createThumbnails(Model $model, $field, $path, $thumbnailPath) {
		return parent::_createThumbnails($model, $field, $path, $thumbnailPath);
	}
}
