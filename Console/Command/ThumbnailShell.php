<?php
App::uses('AppShell', 'Console/Command');
App::uses('ConnectionManager', 'Model');
App::uses('Model', 'Model');
App::uses('UploadBehavior', 'Upload.Model/Behavior');
App::uses('ShellUploadBehavior', 'Upload.Model/Behavior');

/**
 * Thumbnail Shell
 * This shell can be used to generate new thumbnail images when model settings
 * for the thumbnail sizes changes.
 *
 * @author David Yell <dyell@ukwebmedia.com>
 */
class ThumbnailShell extends AppShell {

/**
 * Return the shell options
 *
 * @return ConsoleOptionParser
 */
	public function getOptionParser() {
		$parser = parent::getOptionParser();
		$parser->addSubcommand('generate', array('help' => 'Find models and regenerate the thumbnails.'));
		return $parser;
	}

/**
 * Regenerate thumbnails for images linked in the database
 *
 * @return void
 */
	public function generate() {
		$modelName = $this->in(__('Which model would you like to regenerate thumbnails for?'));

		try {
			$this->loadModel($modelName);
		} catch (Exception $exc) {
			$this->out(__('<error>' . $exc->getMessage() . '</error>'));
			exit;
		}

		if (get_class($this->{$modelName}) === 'AppModel') {
			$this->out(__("<error>Model '$modelName' not found.</error>"));
			exit;
		}

		if (!$this->{$modelName}->Behaviors->attached('Upload')) {
			$this->out(__("<error>Model '$modelName' does not use the Upload behavior.</error>"));
			exit;
		}

		$uploadBehavior = new ShellUploadBehavior;
		$behaviorConfig = $this->{$modelName}->actsAs['Upload.Upload'];

		foreach ($behaviorConfig as $field => $config) {
			$mergedConfig = array_merge($uploadBehavior->defaults, $config);

			$options = array(
				'isThumbnail' => true,
				'path' => $mergedConfig['path'],
				'rootDir' => $mergedConfig['rootDir'],
			);

			$files = $this->{$modelName}->find('all', array(
				'fields' => [$this->{$modelName}->primaryKey, $field, $mergedConfig['fields']['dir']],
				'conditions' => array(
					$modelName . '.' . $field . " IS NOT NULL"
				)
			));

			foreach ($files as $file) {
				$this->{$modelName}->Behaviors->load('ShellUpload', $behaviorConfig);
				$sourceFilePath = $this->{$modelName}->path($field, $options) . $file[$modelName][$mergedConfig['fields']['dir']] . DS . $file[$modelName][$field];
				$this->{$modelName}->Behaviors->unload('ShellUpload');

				if (!file_exists($sourceFilePath)) {
					continue;
				}

				// $field needs to be an array like uploading an image
				$fieldData = array(
					'name' => basename($sourceFilePath),
					'type' => mime_content_type($sourceFilePath),
					'size' => filesize($sourceFilePath),
					'tmp_name' => $sourceFilePath,
					'error' => UPLOAD_ERR_OK
				);

				$data = array(
					$this->{$modelName}->primaryKey => $file[$modelName][$this->{$modelName}->primaryKey],
					$field => $fieldData,
					$config['fields']['dir'] => $file[$modelName][$this->{$modelName}->primaryKey],
				);

				if ($this->{$modelName}->hasField('modified')) {
					$data['modified'] = false;
				}

				$this->{$modelName}->set($data);

				if ($this->{$modelName}->save(null, false)) {
					$this->out(__("Created thumbnails in {$file[$modelName][$mergedConfig['fields']['dir']]} for {$file[$modelName][$field]}"));
				} else {
					$this->out(__("<error> Could not create thumbnails in {$file[$modelName][$mergedConfig['fields']['dir']]} for {$file[$modelName][$field]}</error>"));
				}
			}
		}
	}
}
