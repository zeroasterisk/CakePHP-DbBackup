<?php
/**
 * This is a Collection of Tests
 */
class AllDbBackupTest extends CakeTestSuite {

	/**
	 * $list of tests to run
	 *
	 * array(
	 *   $plugin => array(
	 *     $path, $path, ...
	 *
	 * @param array
	 */
	public static $list = array(
		'DbBackup' => array(
			'Lib/DbBackup',
			'Model/DbBackupLog',
			'Model/DbBackupExclude',
			'Model/DbBackupAction',
		)
	);

	/**
	 * Add in all the test files in the list
	 */
	public static function suite() {
		$suite = new CakeTestSuite('All Plugins tests');
		foreach (self::$list as $plugin => $files) {
			foreach ($files as $file) {
				$suite->addTestFile(self::path($plugin, $file));
			}
		}
		return $suite;
	}

	/**
	 * Helper to determine test file path
	 *
	 * @param string $plugin CamelCase plugin name
	 * @param string $file path (within the Plugin)
	 *               -- should have a "{$file}Test.php" in the appropriate path
	 * @return string $file
	 */
	static function path($plugin, $file) {
		$app = APP;
		$options = array(
			"{$app}Plugin/$plugin/Test/Case/{$file}Test.php",
		);
		foreach ($options as $option) {
			if (is_file($option)) {
				return $option;
			}
		}
		throw new CakeException("AllDbBackupTest::path() unable to find test file for {$plugin}: {$file}");
	}

}

