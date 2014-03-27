<?php
/**
 * DbBackupLogFixture
 *
 */
class DbBackupLogFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 36, 'key' => 'primary'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'completed' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'is_success' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'key' => 'index'),
		'shell' => array('type' => 'string', 'null' => false, 'default' => null, 'key' => 'index'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'shell' => array('column' => 'shell', 'unique' => 0),
			'is_success' => array('column' => 'is_success', 'unique' => 0)
		),
	);

/**
 * Records
 *
 * @var array
 */
	public $records = array(
		array(
			'id' => '53334805-b580-474b-a3ec-4b26c8ead4a4',
			'created' => '2014-03-26 17:35:01',
			'completed' => '2014-03-26 17:35:01',
			'is_success' => 1,
			'shell' => 'all'
		),
	);

}
