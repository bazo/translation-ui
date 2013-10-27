<?php

/**
 * Access
 *
 * @author Martin
 */
class Access
{

	const OWNER = 'owner';
	const ADMIN = 'admin';
	const TRANSLATOR = 'translator';


	private static $accessRules = [
		'viewKeys' => [
			'admin', 'owner'
		],
		'danger' => [
			'owner'
		],
		'accessManagement' => [
			'admin', 'owner'
		],
		'importTemplate' => [
			'admin', 'owner'
		],
	];


	public static function assert($level, $privilege)
	{
		return in_array($level, self::$accessRules[$privilege]);
	}


}

