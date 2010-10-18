<?php
/**
 * Media Helper File
 *
 * 2010 Nathan Tyler
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 *
 * PHP version 5
 * CakePHP version 1.3
 *
 * @package    upload
 * @subpackage upload.views.helpers
 * @copyright  2010 Nathan Tyler
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/tylerdigital/upload
 */

/**
 * Upload Helper Class
 *
 * To load the helper just include it in the helpers property
 * of a controller:
 * {{{
 *     var $helpers = array('Form', 'Html', 'Upload.Upload');
 * }}}
 *
 * @see __construct()
 * @link http://book.cakephp.org/view/99/Using-Helpers
 * @package    upload
 * @subpackage upload.views.helpers
 */
class UploadHelper extends AppHelper {
	var $helpers = array('Html', 'Form');
	function __construct($settings = array()) {

	}
	
	function input($field, $options = array()) {
		$defaults = array(
			'type' => 'file',
			'model' => $this->Form->model(),
		);
		$options = am($defaults, $options);
		extract($options);
		
		$name = $field.'_file';
		$response = $this->Form->input($name, $options);
		if(!empty($this->data[$model][$name])) {
			$response = $this->Form->label($name);
			$response .= $this->Html->tag('p', "Current file: ".$this->data[$model][$name]);
			$response .= $this->Form->input(
				$name.'.remove',
				array(
					'type' => 'checkbox',
					'value' => 1,
				)
			);
		}
		$response .= $this->Form->input('dir', array('type' => 'hidden',));
		return $response;
	}
	
	function link($title, $keyedData=NULL, $options=array(), $confirmMessage=false) {
		$defaults = array(
			'filesUrl' => '/files/',
			'pathMethod' => 'primaryKey',
		);
		$options = am($defaults, $options);
		extract($options);
		foreach ($keyedData as $modelDotField => $data) {}
		list($model, $field) = pluginSplit($modelDotField);
		
		if(isset($data[$model])) $data = $data[$model];
		if(empty($data[$field.'_file'])) {
			return $this->Html->link($title, '', $options, $confirmMessage);
		}
		$url = $options['filesUrl'];
		if($options['pathMethod']=='primaryKey') $url .= low($model).DS.$field.'_file'.DS;
		
		if(!empty($data['dir'])) $url .= $data['dir'].DS;
		$url .= $data[$field.'_file'];
		
		return $this->Html->link($title, $url, $options, $confirmMessage);
	}
}

?>