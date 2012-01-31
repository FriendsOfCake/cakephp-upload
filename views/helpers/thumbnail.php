<?php
class ThumbnailHelper extends AppHelper {  /* Create UploadAppHelper? */  
    var $helpers = array('Html'); 
    
    /** Helper default options */
    var $_defaultOptions = array();
    

   /**
    * Helper constructor
    * 
    * @param    array   ThumbnailHelper options
    */
    function __construct($options=array()) {
        parent::__construct($options);
        $this->settings = array_merge($this->_defaultOptions, $options);
    }
     
   /** 
    *  Print link (With HtmlHelper::link) for listening track
    * 
    *   @param  array   Model entry
    *   @param  string  Field name where get the thumb name (in format: Model.name)
    *   @param  mixed   Thumbnail size alias (thumb, small, etc..)
    * 
    *   @return string
    */     
    function url($entry, $field, $thumbnailSizeName) {
	$thumbName = Set::extract($field, $entry);
        $_src = $this->_getPath($field, $entry).'/'.$thumbnailSizeName.'_'.$thumbName;
	if ($_src) {
            return $this->output( $_src );
        }
    }

   /** 
    *  Print image (With HtmlHelper::image) for listening track
    * 
    *   @param  array   Model entry
    *   @param  string  Field name where get the thumb name (in format: Model.name)
    *   @param  mixed   Thumbnail size alias (thumb, small, etc..)
    *   @param  mixed   Link's HTML attributes (@see HtmlHelper::link method)
    *   @return string
    */     
    function image($entry, $field, $thumbnailSizeName, $htmlAttributes=array()) {
	$thumbName = Set::extract($field, $entry);
        $_src = $this->_getPath($field, $entry).'/'.$thumbnailSizeName.'_'.$thumbName;
        if ($_src) {
            return $this->output( $this->Html->image($_src, $htmlAttributes) );
        }
    }




    private function _getPath($model, $entry) {
	list($modelName, $modelField) = explode('.', $model);
	if (!($Model = ClassRegistry::init($modelName, 'Model'))) {	    		
	    trigger_error(sprintf(__d('upload', 'Model %s not exists', true), $modelName));		    
	    return null;
	}
	if (!$Model->hasField($modelField)) {
	    trigger_error(sprintf(__d('upload', '%s not have field called %s', true), $modelName, $modelField));		    
	    return null;		    
	}
	if (!$Model->Behaviors->attached('Upload')) {
	    trigger_error(sprintf(__d('upload', '%s not have Upload behavior', true), $modelName));		    
	    return false;
	}
	
	$Upload = $Model->Behaviors->Upload;			
	$uploadSettings = $Upload->settings[$modelName][$modelField];
	$uploadPath = $uploadSettings['path'];
	$uploadDir = $Upload->_path( &$Model, $modelField, $uploadPath);
	$uploadDirMethodField = $uploadSettings['fields']['dir'];

	// Get methodDir from $entry 
	if (Set::check($entry, "$modelName.$uploadDirMethodField")) {
	    $tmp = Set::extract("/$modelName/$uploadDirMethodField", $entry );
	    $uploadDirMethod = $tmp[0];
	} else {
	    /* This model use Upload with Upload.pathMethod not set to primaryKey
	     * and $entry not have "Upload->settings['Model']['field']['fields']['dir']
	     * field (@see Upload configuration).
	     *
	     * Triying to get Upload's methodPath from Model id (if is set) and the methodPath is set
	     * to "primaryKey".
	     * 
	     */
	    if (Set::check($entry, "$modelName.id") && $uploadSettings['methodPath'] == 'primaryKey') {
		$uploadDirMethod = $entry[$modelName]['id'];
	    } else {
		trigger_error(sprintf(__d('upload', 'I could not find the thumb of% s. Be sure to enter the field in your table that is referenced Upload.fields.dir.', true), $modelName);
		return null;
	    }
	}
	$needles = array('webroot', '\\');
	$replacements = array('', '/');
	$thumbnailPath = str_replace($needles, $replacements, $uploadDir.$uploadDirMethod);
	return $thumbnailPath;
    }
    

    

}
