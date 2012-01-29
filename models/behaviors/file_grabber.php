<?php
if (!class_exists('UploadBehavior')) {
	App::import('Behavior', 'Upload.Upload');
}
class FileGrabberBehavior extends UploadBehavior {

   /**
    * Download remote file into PHP's TMP dir
    * 
    * @author	Mirko 'hiryu' Chialastri
    */ 
    function _grab(&$model, $field, $uri) {
	debug('Grabbing file: '.$uri);
	App::import('HttpSocket');

	$socket = new HttpSocket();
	$socket->get($uri);
	$headers = $socket->response['header'];
	$file_name = basename($socket->request['uri']['path']);
	$tmp_file = sys_get_temp_dir().'/'.$file_name;
	$not_founded = $socket->response['status']['code'] != 200;	// HttpSocket follow redirection?
	// Populating data (like $_FILES)
	$model->data[$model->alias][$field] = array(
	    $field => null,
	    'name' => $file_name,
	    'tmp_name' => $tmp_file,
	    'error' => 1,
	    'size' => $headers['Content-Length'],
	    'type' => $headers['Content-Type']
	);

	if ($not_founded) return;
	//debug('Write remote file into: '.$tmp_file);
	$file = file_put_contents($tmp_file, $socket->response['body']);
	if ($file === FALSE) {
	    debug('Writing error...');
	    return null;
	}
	$model->data[$model->alias][$field]['error'] = 0;
	return true;
   }

  /**
   * Transform Model.field value like as PHP upload array (name, tmp_name)
   * for UploadBehavior plugin processing.
   * 
   * @author	Mirko 'hiryu' Chialastri
   */  
   function beforeValidate(&$model) {
       foreach($this->settings[$model->alias] as $field => $options) {
	    $uri = $model->data[$model->alias][$field];
	    if ($this->_grab($model, $field, $uri) !== TRUE) {
		$this->invalidate($field, __d('photo_gallery', 'Photo not downloaded..', true));
		return false;
	    }
       }
	return true;
   }

    function handleUploadedFile($modelAlias, $field, $tmp, $filePath) {
	return !rename($tmp, $filePath);
    }

}
