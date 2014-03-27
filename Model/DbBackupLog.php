<?php
App::uses('AppModel', 'Model');
/**
 * DbBackupLog Model
 *
 * @property DbBackupAction $DbBackupAction
 * @property DbBackupExclude $DbBackupExclude
 */
class DbBackupLog extends AppModel {

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'is_success' => array(
			'boolean' => array(
				'rule' => array('boolean'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'task' => array(
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
 * hasMany associations
 *
 * @var array
 */
	public $hasMany = array(
		'DbBackupAction' => array(
			'className' => 'DbBackupAction',
			'foreignKey' => 'db_backup_log_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'DbBackupExclude' => array(
			'className' => 'DbBackupExclude',
			'foreignKey' => 'db_backup_log_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		)
	);

}
