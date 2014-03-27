# CakePHP Plugin - Database Backup Plugin

This plugin can be run to backup from many **sources**

* any configured databases for the hosting CakePHP app
* any "other" databases - configured via `app/Config/db_backup.php`

The backup files will be stored in any of these **destinations**

* File path
* SCP copy to remote server
* Rackspace Cloud Files
* Amazon Web Services S3 *(not yet built)*

## Install

via git clone:

    git clone https://github.com/zeroasterisk/CakePHP-DbBackup app/Plugin/DbBackup

submodule:

    git submodule add https://github.com/zeroasterisk/CakePHP-DbBackup app/Plugin/DbBackup

setup the config file (more on this file later):

    cp app/Plugin/DbBackup/Config/db_backup.default.php app/Config/db_backup.php


**app/Config/bootstrap.php**

    CakePlugin::load('DbBackup');

Destination to Rackspace Cloud Files:

    CakePlugin::load('RSC');

Setup Schema

    ./cake schema create -p DbBackup db_backup


## Config

edit: `app/Config/db_backup.php`

*(comments in the config file)*

## Console / Shell

    ./cake DbBackup.DbBackup help

    ./cake DbBackup.DbBackup backup
    ./cake DbBackup.DbBackup verfiy
    ./cake DbBackup.DbBackup cleanup

### Cron Setup

This is an excellent case to setup as a Cron Job

http://book.cakephp.org/2.0/en/console-and-shells/cron-jobs.html

    // -- /etc/crontab -- daily at 02:45
    45 02 * * *   cd /full/path/to/app && Console/cake DbBackup.DbBackup all


