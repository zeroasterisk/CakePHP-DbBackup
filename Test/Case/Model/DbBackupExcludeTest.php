<?php
App::uses('DbBackupLog', 'DbBackup.Model');
App::uses('DbBackupAction', 'DbBackup.Model');
App::uses('DbBackupExclude', 'DbBackup.Model');

/**
 * DbBackupExclude Test Case
 *
 */
class DbBackupExcludeTest extends CakeTestCase {

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
		$this->DbBackupExclude = ClassRegistry::init('DbBackup.DbBackupExclude');
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->DbBackupExclude);
		parent::tearDown();
	}

	public function testBasics() {
		$data = $this->DbBackupExclude->find('first');
		$saved = $this->DbBackupExclude->save($data);
		$this->assertFalse(empty($saved));
	}

}
