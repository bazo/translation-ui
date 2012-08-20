<?php
/**
 * AllowedLangs
 *
 * @author Martin
 */
class AllowedLangs
{
	private static $langs = array(
		'en' => 'English',
		'sk' => 'Slovak',
		'cs' => 'Czech'
	);
	
	public static function getLangs()
	{
		return self::$langs;
	}
	
	public static function getLangCaption($iso)
	{
		return self::$langs[$iso];
	}
}
