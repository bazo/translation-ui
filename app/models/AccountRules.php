<?php

/**
 * AccountRules
 *
 * @author Martin
 */
class AccountRules
{
	private static $rules = array(
		'basic' => array(
			'maximumProjects' => 5,
			'maximumTranslationsPerProject' => 2,
			'monthlyPrice' => 0
		),
		
		'premium' => array(
			'maximumProjects' => -1,
			'maximumTranslationsPerProject' => -1,
			'monthlyPrice' => 5
		)
	);
	
	public static function getRuleForAccount($accountType)
	{
		return new AccountRule(self::$rules[$accountType]);
	}
}
