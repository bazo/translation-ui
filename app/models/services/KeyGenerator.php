<?php
namespace Services;

/**
 * Description of KeyGenerator
 *
 * @author Martin
 */
class KeyGenerator
{

	public function generateKey($length = 40)
	{
		$tokenLen = $length;
		if (file_exists('/dev/urandom'))
		{ // Get 100 bytes of random data
			$randomData = file_get_contents('/dev/urandom', false, null, 0, 100) . uniqid(mt_rand(), TRUE);
		}
		else
		{
			$randomData = mt_rand() . mt_rand() . mt_rand() . mt_rand() . microtime(TRUE) . uniqid(mt_rand(), TRUE);
		}
		return substr(hash('sha512', $randomData), 0, $tokenLen);
	}

}