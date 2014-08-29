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
		
		$this->{$modelName}->Behaviors->load('ShellUpload', $behaviorConfig);

		foreach ($behaviorConfig as $field => $config) {
			$mergedConfig = array_merge($uploadBehavior->defaults, $config);

			$options = array(
				'isThumbnail' => true,
				'path' => $mergedConfig['path'],
				'rootDir' => $mergedConfig['rootDir'],
			);
			$filePath = APP . $this->{$modelName}->path($field, $options);

			$files = $this->{$modelName}->find('all', [
				'fields' => [$field, $mergedConfig['fields']['dir']]
			]);

			foreach ($files as $sourceFile) {
				$sourceFilePath = $filePath . DS . $sourceFile[$modelName][$mergedConfig['fields']['dir']] . DS . $sourceFile[$modelName][$field];

				try {
					$this->{$modelName}->createThumbnails($field, $sourceFilePath, $sourceFilePath);
				} catch (Exception $exc) {
					$this->out(__('<error>' . $exc->getMessage() . '</error>'));
					exit;
				}
				$this->out('Created thumbnails for ' . $sourceFile[$modelName][$field]);
			}
		}
	}

}