<?php
/**
 * Utility class for the DbBackupShell which does most of the heavy lifting
 *
 */
Class DbBackup {

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
	public static function config($config = array()) {
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
	 * Get all possible database sources
	 *  - extend each with $defaults and verify
	 *
	 * @param array $config (if empty, will get from self::config())
	 * @return array $sources (each an array of DATABASE_CONFIG details)
	 */
	public static function sources($config = null) {
		if (empty($config)) {
			$config = self::config();
		}
		$default = array(
			'datasource' => 'Database/Mysql',
			'persistent' => false,
			'host' => null,
			'login' => null,
			'password' => null,
			'database' => null,
			'prefix' => '',
		);

		if (empty($config['sources'])) {
			$sources = array('default');
		} elseif (is_string($config['sources'])) {
			$sources = Hash::filter(explode(',', $config['sources']));
		} else {
			$sources = Hash::filter($config['sources']);
		}

		foreach ($sources as $key => $source) {
			if (is_string($source)) {
				$source = parse_url($source);
				if (!empty($source['user'])) {
					$source['login'] = $source['user'];
				}
				if (!empty($source['pass'])) {
					$source['password'] = $source['pass'];
				}
			}
			$source = array_merge($default, $source);
			$source = array_intersect_key($source, $default);
			$source = Hash::filter($source);
			unset($sources[$key]);
			$key = self::keyFromSource($source);
			$sources[$key] = $source;
		}

		return $sources;
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
	public static function keyFromSource($source) {
		$exclude_keys = array(
			'persistent',
			'login',
			'password',
		);
		$source = array_diff_key($source, array_flip($exclude_keys));
		$key = strtolower(implode('-', $source));
		$key = trim(preg_replace('#[^a-z0-9-]#', '', $key), '-');
		$key = str_replace('database-', '', $key);
		return $key;
	}

	/**
	 * gets a list of All databases for a Source
	 *
	 * @param array $source (source config array)
	 * @return array $databases (all on this source/server - flat and simple list)
	 * @throws OutOfBoundsException
	 */
	public static function databasesForSource($source) {
		// setup a new "db_backup_worker" connection config to source
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
	 * Return a list of all database for all sources
	 *
	 * @param array $params
	 * @return array $databases as a flat list of <host>.<database>
	 */
	public static function databases($params = array()) {
		$databases = array();
		$sources = self::sources();
		foreach ($sources as $source) {
			// optionally exclude $sources
			if (self::excludeParams($source['host'], $params, 'host')) {
				continue;
			}
			foreach (self::databasesForSource($source) as $database) {
				// optionally exclude $database
				if (self::excludeParams($database, $params, 'database')) {
					continue;
				}
				$databases[] = "{$source['host']}.{$database}";
			}
		}
		$databases = self::databasesFilter($databases);
		return $databases;
	}

	/**
	 * Based on params - should we exclude?
	 *
	 * @param string $value
	 * @param array $params (config array)
	 * @param string $key ($params[$key) if empty, don't exclude
	 * @return boolean [false] if true, exclude
	 */
	public static function excludeParams($value, $params, $key) {
		if (!is_array($params) || empty($params[$key])) {
			return false;
		}
		$filter = $params[$key];
		if (is_string($filter) && substr($filter, 0, 1) == '#') {
			return (!preg_match($filter, $value));
		}
		if (is_string($filter)) {
			$filter = explode(',', $filter);
		}
		return (!in_array($value, $filter));
	}

	/**
	 *
	 *
	 */
	public static function databasesFilter($host_databases) {
		foreach ($host_databases as $host_database) {
			list($host, $database) = explode('.', $host_database);
			print_r(compact('host', 'database'));
			////WIP
		}
	}


}
