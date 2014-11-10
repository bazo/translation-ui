<?php

namespace Helpers;


use Nette\Utils\Strings;

/**
 * Utils
 *
 * @author martin.bazik
 */
class Message
{

	public static function encodeMessageId($messageId)
	{
		return Strings::replace($messageId, '/\./', 'DOT');
	}


	public static function decodeMessageId($messageId)
	{
		return Strings::replace($messageId, '/DOT/', '.');
	}


}
