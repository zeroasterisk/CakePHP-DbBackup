<?php
/**
 * DbBackupActionFixture
 *
 */
class DbBackupActionFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 36, 'key' => 'primary'),
		'db_backup_log_id' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 36, 'key' => 'index'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'completed' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'is_success' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'key' => 'index'),
		'source' => array('type' => 'string', 'null' => false, 'default' => null),
		'action' => array('type' => 'string', 'null' => false, 'default' => null),
		'command' => array('type' => 'string', 'null' => false, 'default' => null),
		'response' => array('type' => 'text', 'null' => true, 'default' => null),
		'error' => array('type' => 'text', 'null' => true, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'db_backup_log_id' => array('column' => 'db_backup_log_id', 'unique' => 0),
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
			'id' => '53334811-4bcc-429a-83b3-49f6c8ead4a4',
			'db_backup_log_id' => '53334805-b580-474b-a3ec-4b26c8ead4a4',
			'created' => '2014-03-26 02:45:01',
			'completed' => '2014-03-26 02:48:55',
			'is_success' => 1,
			'source' => 'datasource-mysql-localhost',
			'action' => 'export',
			'command' => '{export}',
			'response' => 'done',
			'error' => ''
		),
		array(
			'id' => '53334812-4bcc-429a-83b3-49f6c8ead4a4',
			'db_backup_log_id' => '53334805-b580-474b-a3ec-4b26c8ead4a4',
			'created' => '2014-03-26 02:48:55',
			'completed' => '2014-03-26 02:49:15',
			'is_success' => 1,
			'source' => 'datasource-mysql-localhost',
			'action' => 'verify',
			'command' => '{verify temp file}',
			'response' => '',
			'error' => ''
		),
		array(
			'id' => '53334813-4bcc-429a-83b3-49f6c8ead4a4',
			'db_backup_log_id' => '53334805-b580-474b-a3ec-4b26c8ead4a4',
			'created' => '2014-03-26 02:49:15',
			'completed' => '2014-03-26 02:49:59',
			'is_success' => 1,
			'source' => 'datasource-mysql-localhost',
			'action' => 'transfer',
			'command' => '{transfer}',
			'response' => '',
			'error' => ''
		),
		array(
			'id' => '53334814-4bcc-429a-83b3-49f6c8ead4a4',
			'db_backup_log_id' => '53334805-b580-474b-a3ec-4b26c8ead4a4',
			'created' => '2014-03-26 02:50:00',
			'completed' => '2014-03-26 02:50:15',
			'is_success' => 1,
			'source' => 'datasource-mysql-localhost',
			'action' => 'verify',
			'command' => '{verify remote file}',
			'response' => '',
			'error' => ''
		),
		array(
			'id' => '53334815-4bcc-429a-83b3-49f6c8ead4a4',
			'db_backup_log_id' => '53334805-b580-474b-a3ec-4b26c8ead4a4',
			'created' => '2014-03-26 02:50:15',
			'completed' => '2014-03-26 02:55:15',
			'is_success' => 1,
			'source' => 'datasource-mysql-localhost',
			'action' => 'cleanup',
			'command' => 'file1 file2 file3',
			'response' => '',
			'error' => ''
		),
	);

}
