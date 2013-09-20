<?php
/**
 * All Upload plugin tests
 */
class AllUploadTest extends CakeTestCase {

/**
 * Suite define the tests for this suite
 *
 * @return void
 */
	public static function suite() {
		$suite = new CakeTestSuite('All Upload test');

		$path = CakePlugin::path('Upload') . 'Test' . DS . 'Case' . DS;
		$suite->addTestDirectoryRecursive($path);

		return $suite;
	}

}
