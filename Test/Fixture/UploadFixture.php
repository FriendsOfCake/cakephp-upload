<?php
/* Upload Fixture generated on: 2010-08-11 21:08:00 : 1281575760 */
class UploadFixture extends CakeTestFixture {

	public $name = 'Upload';

	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary'),
		'photo' => array('type' => 'string', 'null' => true, 'default' => null),
		'dir' => array('type' => 'string', 'null' => true, 'default' => null),
		'type' => array('type' => 'string', 'null' => true, 'default' => null),
		'size' => array('type' => 'integer', 'null' => true, 'default' => null),
		'other_field' => array('type' => 'string', 'null' => true, 'default' => null),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)),
		'tableParameters' => array(
			'charset' => 'utf8',
			'collate' => 'utf8_general_ci'
		),
	);

	public $records = array(
		array(
			'id' => 1,
			'photo' => 'Photo.png',
			'dir' => '1',
			'type' => 'image/png',
			'size' => 8192
		),
		array(
			// Intentionally empty record, for testing isFileUploadOrHasExistingValue validation
			'id' => 2,
			'photo' => '',
			'dir' => '',
			'type' => '',
			'size' => 0
		),
	);

}
