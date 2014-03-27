<?php
/**
 *
 */
App::uses('DbBackup', 'Lib');
App::uses('DbBackupLog', 'Model');
App::uses('DbBackupAction', 'Model');
Class DbBackupShell extends AppShell {
	public $uses = array(
		'DbBackup.DbBackupLog',
		'DbBackup.DbBackupAction',
	);

	public function main() {
		$this->help();
	}

	public function help() {
		$this->out('DB Backup', 2);
		$this->out('./cake DbBackup.DbBackup help                shows this help output');
		$this->out();
		$this->out('./cake DbBackup.DbBackup backup              backup all configured hosts/databases');
		$this->out('./cake DbBackup.DbBackup backup -h <host>    backup all configured databases on this host');
		$this->out('./cake DbBackup.DbBackup backup -b <database>    backup this database on all configured hosts');
		$this->out();
		$this->out('misc, developer access functions');
		$this->out();
		$this->out('./cake DbBackup.DbBackup config              shows all the config details');
		$this->out('./cake DbBackup.DbBackup sources             shows all the config details');
	}

	public function config() {
		$config = DbBackup::config();
		print_r($config);
	}

	public function sources() {
		$sources = DbBackup::sources();
		print_r($sources);
	}

	public function databases() {
		$databases = DbBackup::databases($this->params);
		print_r($databases);
	}

	// convience wrapper for: backup, verify, cleanup
	public function all() {
	}

	public function backup() {
	}

	public function verify() {
	}

	public function cleanup() {
	}

	/**
	 * get the option parser.
	 *
	 * @return void
	 */
	public function getOptionParser() {
		$parser = parent::getOptionParser();
		return $parser->description(
			__d('cake_console', 'Database Backup Shell')
		)->addOption('dryRun', array(
			'help' => __d('cake_console', 'Dry Run (no commands executed)'),
			'short' => 'd',
			'boolean' => true,
		))->addOption('verbose', array(
			'help' => __d('cake_console', 'Display extra feedback in console'),
			'short' => 'v',
			'boolean' => true,
		))->addOption('host', array(
			'help' => __d('cake_console', 'Limit to this host'),
			'short' => 'h'
		))->addOption('database', array(
			'help' => __d('cake_console', 'Limit to this database'),
			'short' => 'b'
		));
	}
}
