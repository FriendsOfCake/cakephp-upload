<?php
/**
 * Thumbnail helper
 *
 * Fast way to embed UploadPlugin's thumb in your views
 * 
 * @package     upload
 * @subpackage  upload.views.helpers
 */
class ThumbnailHelper extends AppHelper {

    var $helpers = array('Html'); 
    
/** 
 * Helper default options  
 */
    var $_defaultOptions = array(
        'warnings' => true
    );
    

/**
 * Helper constructor
 * 
 * @param    array   ThumbnailHelper options.
 * @todo     This helper need options? 
 */
    function __construct($options = array()) {
        parent::__construct($options);
        $this->settings = array_merge($this->_defaultOptions, $options);
    }
     
/** 
 *   Return url of (With HtmlHelper::image) $data thumbnail
 * 
 *   @param  array  $data  Model entry
 *   @param  string $field  Field name where get the thumb name (in format: Model.name)
 *   @param  mixed  $thumbnailSizeName  Thumbnail size alias (thumb, small, etc..)
 *   @example
 *          $data = array('User'=> array('id' => 1, 'name' => 'Mirko', [...]));
 *          echo $this->Thumbnail->url('User.avatar', 'small', $data)
*   @return string
 */     
    function url($field, $thumbnailSizeName, $data) {
        list($modelName, $modelField) = explode('.', $field);        
        $thumbName = Set::extract($field, $data);
        $basePath = $this->_getPath($field, $data);
        if (is_null($basePath) || is_null($thumbName)) {
            $errmsg = __d('upload', "{$modelName} primary key, {$modelField} or {$modelName}.{$modelField}_dir not exists in $data.", true);
            $this->__error($errmsg);
            return;
        }
        return $this->output( "{$basePath}/{$thumbnailSizeName}_{$thumbName}");         
    }

/** 
 *   Print image tag (With HtmlHelper::image) with $data thumbnail
 * 
 *   @param  array  $data  Model entry
 *   @param  string $field  Field name where get the thumb name (in format: Model.name)
 *   @param  mixed  $thumbnailSizeName  Thumbnail size alias (thumb, small, etc..)
 *   @param  mixed   Link's HTML attributes (@see HtmlHelper::link method)
 *   @example   
 *          $data = array('User'=> array('id' => 1, 'name' => 'Mirko', [...]));
 *          echo $this->Thumbnail->image('User.avatar', 'small', $data, array('title' => 'User avatar'))
 *   @return string
 */     
    function image($field, $thumbnailSizeName, $data, $htmlAttributes=array()) {
        list($modelName, $modelField) = explode('.', $field);        
        $thumbName = Set::extract($field, $data);
        $basePath = $this->_getPath($field, $data);
        if ($basePath === null || $thumbName === null) {
            $errmsg = sprintf(__d('upload', '%s\'s primary key, %s or %s.%s_dir not exists in $data.', true), $modelName, $modelField, $modelName, $modelField);
            $this->__error($errmsg);
            return;
        }
        $_src = "{$basePath}/{$thumbnailSizeName}_{$thumbName}";
        return $this->output( $this->Html->image($_src, $htmlAttributes) );        
    }

/**
 *  Get Upload's plugin path from Model
 *
 *  Make sure that your model schema has a $field_dir where UploadPlugin store methodPath directory
 * (flat, primaryKey, random).
 *
 *  @param  string  Field name where get the thumb name (in format: Model.name)
 *  @param  array   Model row
 *  @return mixed   String path or null on fail
 */
    protected function _getPath($model, $data) {
        list($model, $field) = explode('.', $model);
        if (!($Model = ClassRegistry::init($model, 'Model'))) {             
            $this->__error(sprintf(__d('upload', 'Model %s not exists', true), $model));         
            return null;
        }
        if (!$Model->hasField($field)) {
            $this->__error(sprintf(__d('upload', '%s not have field called %s', true), $model, $field));            
            return null;
        }
        if (!$Model->Behaviors->attached('Upload')) {
            $this->__error(sprintf(__d('upload', '%s not have Upload behavior', true), $model));         
            return null;
        }
        
        $Upload = $Model->Behaviors->Upload;            
        $uploadSettings = $Upload->settings[$model][$field];
        $uploadDir = $Upload->_path(&$Model, $field, $uploadSettings['path']);
        $uploadDirPathMethodField = $uploadSettings['fields']['dir'];

        if (Set::check($data, "{$model}.{$uploadDirPathMethodField}")) {
            // Upload pathMethod is in $data
            $tmp = Set::extract("/{$model}/{$uploadDirPathMethodField}", $data );
            $uploadDirMethod = $tmp[0];
        } elseif (Set::check($data, "{$model}.{$Model->primaryKey}") && $uploadSettings['pathMethod'] == '_getPathPrimaryKey') { 
            // Triying to get Upload's methodPath from Model id (if is set) and the methodPath is set
            // to "primaryKey".            
            $uploadDirMethod = $data[$model][$Model->primaryKey];
        } else {
            $errmsg = __d('upload', 'I could not find the thumb of %s. Be sure to enter the field in your table
                      that is referenced UploadBehavior->settings[%s][%s][\'fields\'][\'dir\'] .', true);
            $this->__error(sprintf($errmsg, $model, $model, $field));
            return null;
        }  
        return str_replace(array('webroot', '\\'), array('', '/'), "{$uploadDir}{$uploadDirMethod}");
    }
    

    private function __error($errmsg) { 
        if ($this->settings['warnings']) {
            trigger_error($errmsg);
        }
    }

}