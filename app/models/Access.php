<?php

/**
 * Access
 *
 * @author Martin
 */
class Access
{
	const 
		OWNER = 'owner',
		ADMIN = 'admin',
		TRANSLATOR = 'translator'
	;
	
	private static $accessRules = array(
		'viewKeys' => array(
			'admin', 'owner'
		),
		'danger' => array(
			'owner'
		),
		'accessManagement' => array(
			'admin', 'owner'
		),
		'importTemplate' => array(
			'admin', 'owner'
		),
	);
	
	public static function assert($level, $privilege)
	{
		return in_array($level, self::$accessRules[$privilege]);
	}
}
