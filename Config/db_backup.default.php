<?php
/**
 * Config class for the DbBackupShell
 *
 */
$config = array(

	'DbBackup' => array(

		/**
		 * look for all databases in all of these hosts
		 *   you MUST add new hosts here, when they come online
		 *   only these sources will be used - (if default isn't there, it wont be used)
		 *   all of these sources will be used
		 */
		'sources' => array(
			// configured via app/Config/database.php
			//   limited to only the database configured,
			//     not all of the databases on the server
			'default',
			'my_fancy_config',
			// configured via any database in array format
			array(
				'datasource' => 'Database/Mysql',
				'persistent' => false,
				'host' => 'db-slave',
				'login' => 'username',
				'password' => 'mypass',
				// if database is not empty, we only use this database
				'database' => null,
			),
			// configured via any database in URL format
			//   sources in URL format (for easy parsing)
			//     mysql://<login>:<pass>@<host>:<port>
			'mysql://username:password@database-server.example.com:3306',
			'mysql://username:password@localhost:3306',
		),

		/**
		 * temp directory (no trailing slash)
		 *   (local filesystem better for RSC Performance servers = SSD)
		 */
		'tmp' => '/tmp',

		/**
		 * where do we "store" our backups?
		 *   you MUST add where you want to send your destinations to
		 */
		'dest' => array(
			// send ALL backups to ALL of these destinations
			//   remove any you don't want to use
			// send to this dir, locally
			'simplefilecopy' => array(
				'type' => 'File',
				'path' => '/tmp/my_backups/',
			),
			// send to this scp path
			'simplefilecopy' => array(
				'type' => 'SCP',
				'user' => 'root',
				'pass' => 'mypass',
				'host' => 'remoteserver',
				'port' => '22',
				'path' => '/tmp/my_backups/',
				'args' => '',
			),
			// send to Rackspace Cloud Files
			// RSC config is in app/Config/rsc.php
			'rackspacecloudfiles' => array(
				'type' => 'RSC',
				'container' => 'database-backups',
			),
			// ...?
		),

		/**
		 * rulesets are how you control
		 *  - what databases to include/exclude
		 *  - what to retain and for how long
		 *
		 * because of this setup, you can apply these rulesets accross multiple hosts/databases
		 *  - any rule that is empty, is ignored
		 *  - we include all matches (regex, in_array, or CSV string - <database> and <host>)
		 *  - we exclude all matches (regex, in_array, or CSV string - <database> and <host>)
		 */
		'ruleset_default' => array(
			'include_database' => null,
			'include_host' => null,
			'exclude_database' => '#(information_schema|database)#',
			'exclude_host' => null,
			'backup' => true,
			'retain' => array(),
		),
		// list all rule-sets
		'rulesets' => array(
			// production database rules
			'prod' => array(
				// filter (must match all includes and not match any excludes)
				'include_database' => '#^[a-z0-9-_]+prod$#',
				'exclude_database' => '#(information_schema|database|[a-z0-9-_]+dev)$#',
				'exclude_host' => 'non-prod-database.example.com',
				// keep only backups which fall into one of these date ranges
				'retain' => array(
					// first of the year (forever)
					'first_of_year' => '-99 years',
					// first of the month (<3 years)
					'first_of_month' =>  '-3 years',
					// first day of each weekly (<6 months)
					'first_of_week' => '-6 months',
					// daily
					'daily' => '-14 days',
				),
			),
			// whatever rulesets you want, match "top down"
			'anyextraruleset' => array(
				// CSV list of rules to apply to all <host>.<database>
				//   which aren't already matched in 'prod'
				'include_host' => 'funky1,funky2,funky3',
				// simple retain rules are great too!
				'retain' => array(
					'daily' => '-10 days',
				),
			),
			// default: everything which has not been assigned a different configuration
			//   (should be last)
			'default' => array(
				// no retain?  we delete after 1 day
				'retain' => array(),
			)
		),

		/**
		 * Customize these based on your envionrment and needs
		 * - replacements:
		 *   - {{host}}
		 *   - {{login}}
		 *   - {{password}}
		 *   - {{port}} (if set on the source config)
		 *   - {{database}}
		 *   - {{target}} = self:backupTempFilePath()
		 *
		 * NOTE: you can specify fill paths to commands if needed
		 *   - '/usr/bin/mysqldump'
		 *   - '/bin/gzip'
		 *
		 * NOTE: you should pipe mysqldump through gzip like:
		 *   mysqldump ... -h{{host}} ... {{database}} | gzip -c > {{target}}
		 */
		'mysqldump_command' => 'mysqldump ' .
			' --skip-lock-tables --create-options --add-drop-table ' .
			'-h"{{host}}" -u"{{login}}" -p"{{password}}" ' .
			// pipe directly through gzip
			' | gzip -c > {{target}}',

	),

);
