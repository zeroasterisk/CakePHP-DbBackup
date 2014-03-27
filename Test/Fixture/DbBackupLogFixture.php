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
		'id' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 36, 'key' => 'primary', 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'completed' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'is_success' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'key' => 'index'),
		'task' => array('type' => 'string', 'null' => false, 'default' => null, 'key' => 'index', 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'task' => array('column' => 'task', 'unique' => 0),
			'is_success' => array('column' => 'is_success', 'unique' => 0)
		),
		'tableParameters' => array('charset' => 'latin1', 'collate' => 'latin1_swedish_ci', 'engine' => 'MyISAM')
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
			'task' => 'Lorem ipsum dolor sit amet'
		),
	);

}
