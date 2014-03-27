<?php
App::uses('DbBackup', 'DbBackup.Lib');
App::uses('DbBackupLog', 'DbBackup.Model');
App::uses('DbBackupAction', 'DbBackup.Model');

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
	);


	/**
	 * example db config source
	 */
	public $sourceArray = array(
		'datasource' => 'Database/Mysql',
		'persistent' => false,
		'host' => 'localhost',
		'login' => 'loginval',
		'password' => 'passwordval',
		'database' => 'databaseval',
		'prefix' => 'prefixval'
	);

	/**
	 * example db config source, as URL
	 */
	public $sourceUrl = 'Mysql://loginval:passwordval@localhost:3306/databaseval';


	/**
	 * example <step> (node of a <map>)
	 */
	public $step = array(
		'host' => 'localhost',
		'database' => 'foobar',
		'ruleset' => 'default',
		'source' => array(
			'datasource' => 'Database/Mysql',
			'persistent' => false,
			'host' => 'localhost',
			'login' => 'loginval',
			'password' => 'passwordval',
			'database' => 'databaseval',
			'prefix' => 'prefixval'
		)
	);

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		// force this "test" config
		Configure::write('DbBackupConfigLoaded', true);
		Configure::write('DbBackup', array(
			'tmp' => '/tmp',
			'dest' => array(
				'simplefilecopy' => array(
					'type' => 'File',
					'path' => '/tmp/my_backups/',
				),
			),
			'sources' => array(
				// we need a "real" database to query here
				//   so we are just using 'test' for this config
				'test',
			),
			'ruleset_default' => array(
				'include_database' => null,
				'include_host' => null,
				'exclude_database' => '#(information_schema|database)#',
				'exclude_host' => null,
				'backup' => true,
				'retain' => array(),
			),
			'rulesets' => array(
				'prod' => array(
					'include_database' => '#^[a-z0-9-_]+prod$#',
					'exclude_database' => '#(backup|test)#',
					'exclude_host' => 'non-prod-database.example.com',
					'retain' => array(
						'first_of_year' => '-99 years',
						'first_of_month' =>  '-3 years',
						'first_of_week' => '-6 months',
						'daily' => '-14 days',
					),
				),
				'qa' => array(
					'include_database' => '#^[a-z0-9-_]+(qa|qaprod)$#', // can match _qaprod and _qa
					'exclude_database' => '#(backup)#',
					'exclude_host' => '#prod#', // will match "non-prod" becuase its a very open regex
					'retain' => array(
						'first_of_week' => '-8 months',
					),
				),
				'dev' => array(
					'include_database' => '#^[a-z0-9-_]+dev$#',
					'exclude_database' => '#test#',
					'retain' => array(
						'daily' => '-2 days',
					),
				),
			),
			'mysqldump_command' => '/usr/bin/mysqldump ' .
				' --skip-lock-tables --create-options --add-drop-table ' .
				'-h {{host}} -u {{login}} -p {{password}} ' .
				// pipe directly through gzip
				' | /bin/gzip -c > {{target}}',
		));
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

	public function testKeyFromSource() {
		$this->assertEqual(DbBackup::keyFromSource(array()), '');
		$this->assertEqual(DbBackup::keyFromSource($this->sourceArray), 'mysql-localhost-databaseval');
	}

	public function testSource() {
		$result = DbBackup::source($this->sourceArray);
		$expect = array(
			'datasource' => 'Database/Mysql',
			'host' => 'localhost',
			'login' => 'loginval',
			'password' => 'passwordval',
			'database' => 'databaseval',
			'prefix' => 'prefixval',
		);
		$this->assertEqual($result, $expect);
		// get from URL format
		$result = DbBackup::source($this->sourceUrl);
		$expect = array(
			'datasource' => 'Database/Mysql',
			'host' => 'localhost',
			'login' => 'loginval',
			'password' => 'passwordval',
			'database' => 'databaseval',
		);
		$this->assertEqual($result, $expect);
		// get from app/onfig/database.php (don't know the values to check)
		$result = DbBackup::source('test');
		$this->assertTrue(is_array($result));
	}

	public function testSources() {
		$result = DbBackup::sources(array('sources' => array($this->sourceArray)));
		$expect = array(
			'mysql-localhost-databaseval' => array (
				'datasource' => 'Database/Mysql',
				'host' => 'localhost',
				'login' => 'loginval',
				'password' => 'passwordval',
				'database' => 'databaseval',
				'prefix' => 'prefixval',
			),
		);
		$this->assertEqual($result, $expect);
	}

	public function testConfig() {
		$config = DbBackup::config();
		$this->assertFalse(empty($config));
		$input = array('aaa' => time(), 'nest' => array('bbb' => rand(1, 99)));
		$config = DbBackup::config($input);
		$this->assertEqual($config['aaa'], $input['aaa']);
		$this->assertEqual($config['nest'], $input['nest']);
	}

	public function testTestFilterInclusive() {
		// if no filter = true (inclusive)
		$this->assertTrue(DbBackup::testFilterInclusive('aaa', 'host', array()));
		$this->assertTrue(DbBackup::testFilterInclusive('aaa', 'host', array('aaa' => null)));
		$this->assertTrue(DbBackup::testFilterInclusive('aaa', 'host', array('aaa' => true)));
		$this->assertTrue(DbBackup::testFilterInclusive('aaa', 'host', array('host' => null)));
		$this->assertTrue(DbBackup::testFilterInclusive('aaa', 'host', array('host' => false)));
		$this->assertTrue(DbBackup::testFilterInclusive('aaa', 'host', array('host' => '')));
		// if value "in_array" or  "matches pattern" = don't exclude
		$this->assertTrue(DbBackup::testFilterInclusive('aaa', 'host', array('host' => 'aaa')));
		$this->assertTrue(DbBackup::testFilterInclusive('aaa', 'host', array('host' => '#^a.+a$#')));
		$this->assertTrue(DbBackup::testFilterInclusive('aaa', 'host', array('host' => '/^a.+a$/')));
		// if value not in array and not match = exclude
		$this->assertFalse(DbBackup::testFilterInclusive('aaa', 'host', array('host' => 'aaaX')));
		$this->assertFalse(DbBackup::testFilterInclusive('aaa', 'host', array('host' => '#^b.+b$#')));
		$this->assertFalse(DbBackup::testFilterInclusive('aaa', 'host', array('host' => '/^b.+b$/')));
	}

	public function testTestFilterExclusive() {
		// if no filter = false (exclusive)
		$this->assertFalse(DbBackup::testFilterExclusive('aaa', 'host', array()));
		$this->assertFalse(DbBackup::testFilterExclusive('aaa', 'host', array('aaa' => null)));
		$this->assertFalse(DbBackup::testFilterExclusive('aaa', 'host', array('aaa' => true)));
		$this->assertFalse(DbBackup::testFilterExclusive('aaa', 'host', array('host' => null)));
		$this->assertFalse(DbBackup::testFilterExclusive('aaa', 'host', array('host' => false)));
		$this->assertFalse(DbBackup::testFilterExclusive('aaa', 'host', array('host' => '')));
		// if value "in_array" or  "matches pattern" = don't exclude
		$this->assertTrue(DbBackup::testFilterExclusive('aaa', 'host', array('host' => 'aaa')));
		$this->assertTrue(DbBackup::testFilterExclusive('aaa', 'host', array('host' => '#^a.+a$#')));
		$this->assertTrue(DbBackup::testFilterExclusive('aaa', 'host', array('host' => '/^a.+a$/')));
		// if value not in array and not match = exclude
		$this->assertFalse(DbBackup::testFilterExclusive('aaa', 'host', array('host' => 'aaaX')));
		$this->assertFalse(DbBackup::testFilterExclusive('aaa', 'host', array('host' => '#^b.+b$#')));
		$this->assertFalse(DbBackup::testFilterExclusive('aaa', 'host', array('host' => '/^b.+b$/')));
	}

	// get all databases for source (unfiltered)
	public function test_DatabasesForSource() {
		$result = DbBackup::_databasesForSource(DbBackup::source('test'));
		$this->assertTrue(is_array($result));
		$this->assertEqual(Hash::dimensions($result), 1);
	}

	// get filtered databases for source (see testFilterExclusive)
	public function testDatabasesForSource() {
		$source = DbBackup::source('test');
		$all = DbBackup::databasesForSource($source);
		$params = array(
			'host' => $source['host'],
			'database' => $all[0],
		);
		$result = DbBackup::databasesForSource($source, $params);
		$this->assertTrue(is_array($result));
		$this->assertEqual(Hash::dimensions($result), 1);
		$expect = array($all[0]);
		$this->assertEqual($result, $expect);
	}

	public function testRuleset() {
		$source = DbBackup::source($this->sourceArray);
		// not host rules happen to match
		$source['host'] = 'foobar';
		// prod (if allowed)
		$this->assertEqual(DbBackup::ruleset($source, 'example1_prod'), 'prod');
		$this->assertEqual(DbBackup::ruleset($source, 'backup_prod'), 'default');
		$this->assertEqual(DbBackup::ruleset($source, 'test_prod'), 'default');
		// qa
		$this->assertEqual(DbBackup::ruleset($source, 'example1_qa'), 'qa');
		$this->assertEqual(DbBackup::ruleset($source, 'backup_qa'), 'default');
		$this->assertEqual(DbBackup::ruleset($source, 'test_qa'), 'qa');
		// dev
		$this->assertEqual(DbBackup::ruleset($source, 'example1_dev'), 'dev');
		$this->assertEqual(DbBackup::ruleset($source, 'backup_dev'), 'dev');
		$this->assertEqual(DbBackup::ruleset($source, 'test_dev'), 'default');
		// unknown
		$this->assertEqual(DbBackup::ruleset($source, 'example1_unknown'), 'default');
		// qaprod = matches prod if allowed - fails to QA (if allowed for QA)
		//   (demonstrates cascade allowances)
		$this->assertEqual(DbBackup::ruleset($source, 'example1_qaprod'), 'prod');
		$this->assertEqual(DbBackup::ruleset($source, 'backup_qaprod'), 'default');
		$this->assertEqual(DbBackup::ruleset($source, 'test_qaprod'), 'qa');
		// apply host rules too
		$source['host'] = 'non-prod-database.example.com';
		$this->assertEqual(DbBackup::ruleset($source, 'example1_prod'), 'default');
		$this->assertEqual(DbBackup::ruleset($source, 'example1_qa'), 'default');
		$this->assertEqual(DbBackup::ruleset($source, 'example1_dev'), 'dev');
		$this->assertEqual(DbBackup::ruleset($source, 'example1_unknown'), 'default');
	}

	public function testMap() {
		$map = DbBackup::map();
		$this->assertTrue(is_array($map));
		$step = array_shift($map);
		$this->assertTrue(is_array($step));
		$keys = array('host', 'database', 'ruleset', 'source');
		$this->assertEqual(array_keys($step), $keys);
	}

	public function testValidateStep() {
		$this->assertTrue(DbBackup::validateStep($this->step));
	}

	public function testBackupTempFileName() {
		$result = DbBackup::backupTempFileName($this->step);
		$expect = 'localhost.foobar.' . date('YmdHis') . '.sql.gz';
		$this->assertEqual($result, $expect);
	}
	public function testBackupTempFilePath() {
		$result = DbBackup::backupTempFilePath($this->step);
		$expect = '/tmp/' .  DbBackup::backupTempFileName($this->step);
		$this->assertEqual($result, $expect);
	}
	public function testBackupTempCommand() {
		$result = DbBackup::backupTempCommand($this->step);
		$expect = '/usr/bin/mysqldump ' .
			' --skip-lock-tables --create-options --add-drop-table ' .
			'-h"localhost" -u"loginval" -p"passwordval" ' .
			' | /bin/gzip -c > ' .
			DbBackup::backupTempFilePath($this->step);
		$this->assertEqual($result, $expect);
	}



}

