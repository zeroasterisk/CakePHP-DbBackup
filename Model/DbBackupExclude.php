<?php
App::uses('AppModel', 'Model');
/**
 * DbBackupExclude Model
 *
 * @property DbBackupLog $DbBackupLog
 */
class DbBackupExclude extends AppModel {

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'db_backup_log_id' => array(
			'uuid' => array(
				'rule' => array('uuid'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'command' => array(
			'notEmpty' => array(
				'rule' => array('notEmpty'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'reason' => array(
			'notEmpty' => array(
				'rule' => array('notEmpty'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);

	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'DbBackupLog' => array(
			'className' => 'DbBackupLog',
			'foreignKey' => 'db_backup_log_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
}
