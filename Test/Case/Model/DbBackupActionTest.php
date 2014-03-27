<?php
App::uses('DbBackupLog', 'DbBackup.Model');
App::uses('DbBackupAction', 'DbBackup.Model');

/**
 * DbBackupAction Test Case
 *
 */
class DbBackupActionTest extends CakeTestCase {

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = array(
		'plugin.db_backup.db_backup_action',
		'plugin.db_backup.db_backup_log',
	);

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$this->DbBackupAction = ClassRegistry::init('DbBackup.DbBackupAction');
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->DbBackupAction);
		parent::tearDown();
	}

	public function testBasics() {
		$data = $this->DbBackupAction->find('first');
		$saved = $this->DbBackupAction->save($data);
		$this->assertFalse(empty($saved));
	}

}
