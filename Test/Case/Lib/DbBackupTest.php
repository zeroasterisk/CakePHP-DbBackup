<?php
App::uses('DbBackup', 'DbBackup.Lib');
App::uses('DbBackupLog', 'DbBackup.Model');
App::uses('DbBackupAction', 'DbBackup.Model');
App::uses('DbBackupExclude', 'DbBackup.Model');

/**
 * DbBackup (Lib) Test Case
 *
 */
class DbBackupTest extends CakeTestCase {

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
	}

	public function testConfig() {
		$config = DbBackup::config();
		$this->assertFalse(empty($config));
		$input = array('aaa' => time(), 'nest' => array('bbb' => rand(1, 99)));
		$config = DbBackup::config($input);
		$this->assertEqual($config['aaa'], $input['aaa']);
		$this->assertEqual($config['nest'], $input['nest']);
	}

}

