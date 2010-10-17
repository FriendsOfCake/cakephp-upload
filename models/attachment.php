<?php
class Attachment extends UploadAppModel {
	var $name = 'Attachment';
	var $useTable = 'attachments';
	var $actsAs = array(
		'Containable',
		'Meta.Sluggable',
		'Meta.Auditable',
	);
	var $displayField = 'name';
}
?>