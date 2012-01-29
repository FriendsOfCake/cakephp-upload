<?php
App::uses('UploadBehavior', 'Upload.Model/Behavior');
App::uses('HttpSocket', 'Utility');

class FileGrabberBehavior extends UploadBehavior {

/**
 * Download remote file into PHP's TMP dir
 */
	function _grab(Model $model, $field, $uri) {

		$socket = new HttpSocket();
		$socket->get($uri);
		$headers = $socket->response['header'];
		$file_name = basename($socket->request['uri']['path']);
		$tmp_file = sys_get_temp_dir() . '/' . $file_name;
		if (!$socket->response['status']['code'] != 200) {
			return false;
		}

		$model->data[$model->alias][$field] = array(
			$field => null,
			'name' => $file_name,
			'tmp_name' => $tmp_file,
			'error' => 1,
			'size' => $headers['Content-Length'],
			'type' => $headers['Content-Type']
		);

		$file = file_put_contents($tmp_file, $socket->response['body']);
		if (!$file) {
			return false;
		}

		$model->data[$model->alias][$field]['error'] = 0;
		return true;
	}

/**
 * Transform Model.field value like as PHP upload array (name, tmp_name)
 * for UploadBehavior plugin processing.
 */
	function beforeValidate(Model $model) {
		foreach($this->settings[$model->alias] as $field => $options) {
			$uri = $model->data[$model->alias][$field];
			if (!$this->_grab($model, $field, $uri)) {
				$this->invalidate($field, __d('upload', 'File was not downloaded.', true));
				return false;
			}
		}
		return true;
	}

	function handleUploadedFile($modelAlias, $field, $tmp, $filePath) {
		return !rename($tmp, $filePath);
	}

}
