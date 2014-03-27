<?php
/**
 * Intranet Schema for DbBackups
 */

/*
 *
 * Using the Schema command line utility
 * cake schema run create DbBackups
 *
 */
class DbBackupsSchema extends CakeSchema {

	public $name = 'DbBackups';

	public function before($event = array()) {
		return true;
	}

	public function after($event = array()) {
	}

	// log of "tasks" run via DbBackupShell
	public $db_backup_logs = array(
		'id' => array('type' => 'string', 'length' => 36, 'null' => false, 'key' => 'primary'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'completed' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'is_success' => array('type' => 'boolean', 'null' => false, 'default' => 0),
		'task' => array('type' => 'string', 'null' => false, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'task' => array('column' => 'task', 'unique' => 0),
			'is_success' => array('column' => 'is_success', 'unique' => 0),
		)
	);
	// log of "actions/commands" for each "item" in DbBackupShell
	public $db_backup_actions = array(
		'id' => array('type' => 'string', 'length' => 36, 'null' => false, 'key' => 'primary'),
		'db_backup_log_id' => array('type' => 'string', 'length' => 36, 'null' => false, 'key' => 'index'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'completed' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'is_success' => array('type' => 'boolean', 'null' => false, 'default' => 0),
		'source' => array('type' => 'string', 'null' => false, 'default' => null),
		'dest' => array('type' => 'string', 'null' => false, 'default' => null),
		'command' => array('type' => 'string', 'null' => false, 'default' => null),
		'response' => array('type' => 'text', 'null' => true, 'default' => null),
		'error' => array('type' => 'text', 'null' => true, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'db_backup_log_id' => array('column' => 'db_backup_log_id', 'unique' => 0),
			'is_success' => array('column' => 'is_success', 'unique' => 0),
		)
	);
	// log of "exclusions" for each "item" in DbBackupShell
	public $db_backup_excludes = array(
		'id' => array('type' => 'string', 'length' => 36, 'null' => false, 'key' => 'primary'),
		'db_backup_log_id' => array('type' => 'string', 'length' => 36, 'null' => false, 'key' => 'index'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'command' => array('type' => 'string', 'null' => false, 'default' => null),
		'reason' => array('type' => 'string', 'null' => false, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'db_backup_log_id' => array('column' => 'db_backup_log_id', 'unique' => 0),
		)
	);

}
