<?php
/**
 * Utility class for the DbBackupShell which does most of the heavy lifting
 *
 */
if (!class_exists('DbBackupException')) {
	Class DbBackupException extends CakeException {}
}
Class DbBackup {
	/**
	 * Placeholder array for a log of what happened
	 *   useful for debugging
	 *   add with DbBackup::log('Add this entry')
	 *   read with DbBackup::log()
	 */
	static $_log = array();

	/**
	 * Get/Set configurations for DbBackup
	 *  - Basic/Default configuration in app/Config/db_backup.php
	 *  - if set (with new $config array, or first read)
	 *    - re-builds the $sources
	 *
	 * @param array $config (optional)
	 * @return array $config
	 * @throws OutOfBoundsException
	 */
	static function config($config = array()) {
		if (!Configure::read('DbBackupConfigLoaded')) {
			Configure::write('DbBackupConfigLoaded', true);
			Configure::load('db_backup');
			$config = Configure::read('DbBackup');
			if (empty($config)) {
				throw new OutOfBoundsException('DbBackup needs to be setup.  `cp app/Plugin/DbBackup/Config/db_backup.default.php app/Config/db_backup.php`');
			}
		}
		if (empty($config)) {
			return Configure::read('DbBackup');
		}
		$config = Hash::merge(
			Configure::read('DbBackup'),
			$config
		);
		$config['sources'] = self::sources($config);
		Configure::write(array('DbBackup' => $config));
		return $config;
	}

	/**
	 * quick access to any config key
	 *
	 * @param string $key
	 * @return mixed $config_value
	 */
	static function conf($key) {
		$config = self::config();
		if (array_key_exists($key, $config)) {
			return $config[$key];
		}
		return Configure::read('DbBackup.' . $key);
	}

	/**
	 * Get all possible database sources
	 *  - extend each with $defaults and verify
	 *
	 * @param array $config (if empty, will get from self::config())
	 * @return array $sources (each an array of DATABASE_CONFIG details)
	 */
	static function sources($config = null) {
		if (empty($config)) {
			$config = self::config();
		}

		if (empty($config['sources'])) {
			$sources = array('default');
		} elseif (is_string($config['sources'])) {
			$sources = Hash::filter(explode(',', $config['sources']));
		} else {
			$sources = Hash::filter($config['sources']);
		}

		foreach (array_keys($sources) as $key) {
			$source = self::source($sources[$key]);
			unset($sources[$key]);
			$key = self::keyFromSource($source);
			$sources[$key] = $source;
		}

		return $sources;
	}

	/**
	 * Get a source's details
	 *  - from app/Config/database.php
	 *  - from a URL encoded string
	 *  - as an array
	 *
	 * @param mixed $source
	 * @return array $source
	 */
	static function source($source) {
		$default = array(
			'datasource' => 'Database/Mysql',
			'persistent' => false,
			'host' => null,
			'login' => null,
			'password' => null,
			'database' => null,
			'prefix' => '',
		);

		if (is_string($source)) {
			// is the string a "property" of the database config?
			App::Import('ConnectionManager');
			try {
				$db = ConnectionManager::getDataSource($source);
			} catch (MissingDatasourceConfigException $e) {}
			if (!empty($db) && !empty($db->config)) {
				// assign from a configured source array
				$source = $db->config;
			} else {
				// parse a URL formatted config
				$source = parse_url($source);
				if (!empty($source['user'])) {
					$source['login'] = $source['user'];
				}
				if (!empty($source['pass'])) {
					$source['password'] = $source['pass'];
				}
				if (!empty($source['path'])) {
					$source['database'] = trim($source['path'], '/');
				}
			}
		}

		// default, normalize, clean, return
		$source = array_merge($default, $source);
		$source = array_intersect_key($source, $default);
		$source = Hash::filter($source);
		return $source;
	}

	/**
	 * Get a distinct key from a source array
	 *  - human readable
	 *  - no sensetive information
	 *  - distinct
	 *
	 * @param array $source
	 * @return string $key
	 */
	static function keyFromSource($source) {
		$exclude_keys = array(
			'persistent',
			'login',
			'password',
			'prefix',
			'encoding',
		);
		$source = array_diff_key($source, array_flip($exclude_keys));
		$key = strtolower(implode('-', $source));
		$key = trim(preg_replace('#[^a-z0-9-]#', '', $key), '-');
		$key = str_replace('databasemysql', 'mysql', $key);
		return $key;
	}

	/**
	 * gets a list of All databases for a Source (no filtering, rules applied)
	 *
	 * @param array $source (source config array)
	 * @return array $databases (all on this source/server - flat and simple list)
	 * @throws OutOfBoundsException
	 */
	static function _databasesForSource($source) {
		// setup a new "db_backup_worker" connection config to source
		//   we piggyback on the 'default' connection config, just in case
		$db = ConnectionManager::getDataSource('default');
		$config = $db->config;
		$config = array_merge($config, $source);
		ConnectionManager::drop('db_backup_worker');
		$db = ConnectionManager::create('db_backup_worker', $config);
		// setup a new copy of DbBackupLog - set to new worker config
		$Model = ClassRegistry::init('DbBackup.DbBackupLog');
		$oldConfig = $Model->useDbConfig;
		$oldTable = $Model->useTable;
		$Model->useTable = false;
		$Model->setDataSource('db_backup_worker');
		// query for databases
		if ($config['datasource'] == 'Database/Mysql') {
			$databases = $Model->query('SHOW DATABASES;');
			$databases = array_values(Hash::flatten($databases));
		} else {
			// Wanna help?  get the Databases for this type and return a simple/flat list
			throw new OutOfBoundsException("DbBackup::databasesForSource() unable to show databases for {$config['datasource']}");
		}
		// cleanup/reset (objects are persistant)
		$Model->useTable = $oldTable;
		$Model->setDataSource($oldConfig);
		unset($Model, $db);
		return $databases;
	}

	/**
	 * For a $source assign $databases if able
	 *  - any <source> not allowed via shell $params = false
	 *  - any <database> not allowed via shell $params is excluded
	 *  - any <source> without databases is removed =
	 *  - does not filter based on include/exclude for rulesets
	 *
	 * @param array $source
	 * @param array $params
	 * @return array $databases
	 */
	static function databasesForSource($source, $params = array()) {
		// optionally exclude $sources (shell params)
		//   done before we even try to get $databases for performance
		if (!self::testFilterInclusive($source['host'], 'host', $params)) {
			return array();
		}

		// get all possible databases
		$databases = self::_databasesForSource($source);

		// filter if any excluded via shell rules
		foreach (array_keys($databases) as $i) {
			// optionally exclude $database (shell params)
			//   done here because "why not"?
			if (!self::testFilterInclusive($databases[$i], 'database', $params)) {
				unset($databases[$i]);
			}
		}

		return $databases;
	}

	/**
	 * For a $source + $database assign $ruleset (key/string) or default
	 *  - does filter based on include/exclude for rulesets
	 *  - first "allowed" ruleset applied
	 *  - if no ruleset = "default"
	 *
	 * @param array $source
	 * @param string $database
	 * @return string $ruleset
	 */
	static function ruleset($source, $database) {
		$rulesets = self::conf('rulesets');
		$default = self::conf('ruleset_default');
		do {
			// iterate and reduce the list of possible $rulesets to apply
			//   only until one is found, or until the all are tried
			$ruleset = key($rulesets);
			$rules = current($rulesets);
			$rules = array_merge($default, $rules);

			// if not matching inclusive rules - remove/skip
			if (!self::testFilterInclusive($source['host'], 'include_host', $rules)) {
				self::log("ruleset $ruleset skipped for {$source['host']}.{$database} because of the rule[include_host]: {$rules['include_host']}");
				unset($rulesets[$ruleset]);
				continue;
			}
			if (!self::testFilterInclusive($database, 'include_database', $rules)) {
				self::log("ruleset $ruleset skipped for {$source['host']}.{$database} because of the rule[include_database]: {$rules['include_database']}");
				unset($rulesets[$ruleset]);
				continue;
			}

			// if is matching exclusive rules - remove/skip
			if (self::testFilterExclusive($source['host'], 'exclude_host', $rules)) {
				self::log("ruleset $ruleset skipped for {$source['host']}.{$database} because of the rule[exclude_host]: {$rules['exclude_host']}");
				unset($rulesets[$ruleset]);
				continue;
			}
			if (self::testFilterInclusive($database, 'exclude_database', $rules)) {
				self::log("ruleset $ruleset skipped for {$source['host']}.{$database} because of the rule[exclude_database]: {$rules['exclude_database']}");
				unset($rulesets[$ruleset]);
				continue;
			}

			// made it here?  this is our ruleset
			return $ruleset;
		} while (!empty($rulesets));

		// still no ruleset?  assign 'default'
		return 'default';
	}

	/**
	 * Make a map of host+database+ruleset+source <step>s to act upon
	 *
	 * NOTE: organized by $source still, because we want to complete all
	 * backups for a specific server first/fast, then move onto the next
	 *
	 * (do all backups for server/source A, then do all backups for server/source B)
	 *
	 * @param array $params from shell
	 * @return array $map (flat, not nested/grouped, multi-dim)
	 */
	static function map($params = array()) {
		$map = array();
		$sources = self::sources();
		foreach (array_keys($sources) as $key) {
			$source = self::source($sources[$key]);
			$host = $source['host'];
			$databases = self::databasesForSource($source, $params);
			foreach ($databases as $i => $database) {
				$ruleset = self::ruleset($source, $database);
				$mapKey = implode('', compact('host', 'database', 'ruleset')) . $i;
				$map[$mapKey] = compact('host', 'database', 'ruleset', 'source');
			}
		}
		asort($map);
		return array_values($map);
	}

	/**
	 * Ensure we have a valid <step>
	 *
	 * @param array $step
	 * @return boolean
	 * @throws DbBackupException
	 */
	static function validateStep($step) {
		$keys = array('host', 'database', 'ruleset', 'source');
		foreach ($keys as $key) {
			if (empty($step[$key])) {
				throw new DbBackupException("Invald step, missing {$key} " . json_encode($step));
			}
		}
		return true;
	}

	/**
	 * backup temp filename for a <step> + <date>
	 *
	 * @param array $step
	 * @return string $filepath
	 * @throws DbBackupException
	 */
	static function backupTempFileName($step) {
		if (empty($step['date'])) {
			$step['date'] = 'now';
		}
		$date = date('YmdHis', strtotime($step['date']));
		$host = $step['host'];
		$database = $step['database'];
		$template = self::conf('filename_template');
		if (empty($template)) {
			$template = "{{host}}.{{database}}.{{date}}.sql.gz";
		}
		return self::replaceTemplate($template, compact('host', 'database', 'date'));
	}

	/**
	 * backup temp file for a <step>
	 *
	 * @param array $step
	 * @return string $filepath
	 * @throws DbBackupException
	 */
	static function backupTempFilePath($step) {
		$tmpDir = self::conf('tmp');
		if (!file_exists($tmpDir)) {
			@mkdir($tmpDir);
		}
		if (!file_exists($tmpDir)) {
			throw new DbBackupException('TMP dir does not exist ' . $tmpDir);
		}
		if (!is_dir($tmpDir)) {
			throw new DbBackupException('TMP dir not a directory ' . $tmpDir);
		}
		if (!is_writeable($tmpDir)) {
			throw new DbBackupException('TMP dir not writeable ' . $tmpDir);
		}
		if (substr($tmpDir, -1) != DS) {
			$tmpDir .= DS;
		}
		$filename = self::backupTempFileName($step);
		return $tmpDir . $filename;
	}

	/**
	 * Backup Command for a Step
	 *  - replacement: {{target}} - temp file path
	 *
	 * @param array $step
	 * @param string $target (creates from step if empty)
	 * @return string $command to backup
	 * @throws DbBackupException
	 */
	static function backupTempCommand($step, $target = null) {
		if (empty($target)) {
			$target = self::backupTempFilePath($step);
		}
		if ($step['source']['datasource'] == 'Database/Mysql') {
			$command = self::conf('mysqldump_command');
			$command = self::replaceTemplate($command, $step);
			$command = self::replaceTemplate($command, compact('target'));
			return $command;
		}
		// Wanna help?  get the Databases for this type and return a simple/flat list
		throw new OutOfBoundsException("DbBackup::backupTempCommand() unable to determine databases for {$step['source']['datasource']}");
	}

	/**
	 * Do a backup for a <step>
	 *
	 * @param array $step
	 * @return boolean
	 * @throws DbBackupException
	 */
	static function backupToTemp($step) {
		self::validateStep($step);
		$target = self::backupTempFilePath($step);
		if (file_exists($target)) {
			self::log('backupToTemp skipped - file already exists: ' . $target);
			return true;
		}
		$command = self::backupTempCommand($step, $target);
		$result = self::runCommand($command);
		// TODO: validation?
		return true;
	}

	/**
	 * Verify that a temp backup file exists and is "valid"
	 *  - filesize > 0
	 *
	 * @param array $step
	 * @return boolean
	 * @throws DbBackupException
	 */
	static function verifyTempBackup($step) {
		$target = self::backupTempFilePath($step);
		if (file_exists($target)) {
			self::log('verifyTempBackup failed - file does not exists: ' . $target);
			return false;
		}
		$filesize = filesize($target);
		if ($filesize == 0) {
			self::log('verifyTempBackup failed - file too small: ' . $target);
			return false;
		}
		return true;
	}



	/**
	 * Apply "rules" and decide if the $value is good or not
	 *  - if rule is (empty) return $ifEmptyRuleReturn [true] = inclusive, false = exclusive
	 *  - if rule starts with '#' or '/' treat as a regular expression
	 *  - if rule is a string, explode(',' $rule) and continue
	 *  - if rule is array, return in_array($value, $rule);
	 *
	 * @param string $value
	 * @param string $ruleKey ($rules[$ruleKey) if empty, don't exclude
	 * @param array $rules
	 * @return boolean $ruleMatched or $ifEmptyRuleReturn
	 */
	static function testFilter($value, $ruleKey, $rules, $ifEmptyRuleReturn = true) {
		if (!is_array($rules) || empty($rules[$ruleKey])) {
			return $ifEmptyRuleReturn;
		}
		$rule = $rules[$ruleKey];
		if (is_string($rule) && (substr($rule, 0, 1) == '#' || substr($rule, 0, 1) == '/')) {
			$match = (preg_match($rule, $value));
			return (!empty($match));
		}
		if (is_string($rule)) {
			$rule = explode(',', $rule);
		}
		return (in_array($value, $rule, true));
	}
	static function testFilterInclusive($value, $ruleKey, $rules) {
		return self::testFilter($value, $ruleKey, $rules, true);
	}
	static function testFilterExclusive($value, $ruleKey, $rules) {
		return self::testFilter($value, $ruleKey, $rules, false);
	}

	/**
	 * Add/Read an internal log array.
	 *   useful for debugging
	 *   add with DbBackup::log('Add this entry')
	 *   read with DbBackup::log()
	 *
	 * @param string $message (optional)
	 * @return array $log
	 */
	static function log($message = null) {
		if (!empty($message)) {
			self::$_log[] = date('Y-m-d H:i:s ') . $message;
		}
		return self::$_log;
	}

	/**
	 * Simple template engine
	 *
	 * for every key=>val in $array
	 *   {{key}} --> val
	 *     (if val is array, recurse)
	 *
	 * @param string $template
	 * @param array $array
	 * @return string $template with replacements
	 */
	static function replaceTemplate($template, $array) {
		foreach ($array as $key => $val) {
			if (is_array($val)) {
				$template = self::replaceTemplate($template, $val);
				continue;
			}
			if (!is_string($val) && !is_int($val)) {
				continue;
			}
			$template = str_replace('{{' . $key . '}}', $val, $template);
		}
		return $template;
	}


}
