<?php
App::uses('DbBackupLog', 'DbBackup.Model');
App::uses('DbBackupAction', 'DbBackup.Model');
App::uses('DbBackupExclude', 'DbBackup.Model');

/**
 * DbBackupLog Test Case
 *
 */
class DbBackupLogTest extends CakeTestCase {

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = array(
		'plugin.db_backup.db_backup_action',
		'plugin.db_backup.db_backup_log',
		'plugin.db_backup.db_backup_exclude'
	);

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$this->DbBackupLog = ClassRegistry::init('DbBackup.DbBackupLog');
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->DbBackupLog);
		parent::tearDown();
	}

	public function testBasics() {
		$data = $this->DbBackupLog->find('first');
		$saved = $this->DbBackupLog->save($data);
		$this->assertFalse(empty($saved));
	}

}
