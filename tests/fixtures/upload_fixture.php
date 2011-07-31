<?php
/* Upload Fixture generated on: 2010-08-11 21:08:00 : 1281575760 */
class UploadFixture extends CakeTestFixture {
	var $name = 'Upload';

	var $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'photo' => array('type' => 'string', 'null' => true, 'default' => NULL),
		'dir' => array('type' => 'string', 'null' => true, 'default' => NULL),
		'type' => array('type' => 'string', 'null' => true, 'default' => NULL),
		'size' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		'other_field' => array('type' => 'string', 'null' => true, 'default' => NULL),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)),
		'tableParameters' => array('charset' => 'latin1', 'collate' => 'latin1_swedish_ci', 'engine' => 'MyISAM')
	);

	var $records = array(
		array(
			'id' => 1,
			'photo' => 'Photo.png',
			'dir' => '1',
			'type' => 'image/png',
			'size' => 8192	
		),
	);
}
?>