<?php

Warning: date(): It is not safe to rely on the system's timezone settings. You are *required* to use the date.timezone setting or the date_default_timezone_set() function. In case you used any of those methods and you are still getting this warning, you most likely misspelled the timezone identifier. We selected 'America/Denver' for 'MST/-7.0/no DST' instead in /Users/nathan/Dropbox/Sites/cakephp/cake/console/templates/default/classes/test.ctp on line 22
/* Attachments Test cases generated on: 2010-10-17 01:10:53 : 1287299933*/
App::import('Controller', 'Upload.Attachments');

class TestAttachmentsController extends AttachmentsController {
	var $autoRender = false;

	function redirect($url, $status = null, $exit = true) {
		$this->redirectUrl = $url;
	}
}

class AttachmentsControllerTestCase extends CakeTestCase {
	function startTest() {
		$this->Attachments =& new TestAttachmentsController();
		$this->Attachments->constructClasses();
	}

	function endTest() {
		unset($this->Attachments);
		ClassRegistry::flush();
	}

}
?>