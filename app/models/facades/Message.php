<?php
namespace Facades;
use Doctrine\ODM\MongoDB\DocumentManager;
use Nette\Utils\Strings;

class Message extends Base
{
	protected
		$documentClass = 'Message'
	;
	
	public function translateMessage(\Message $message, $translations)
	{
		$message->setTranslations($translations);
		
		$this->dm->persist($message);
		
		$this->dm->flush();
	}
	
}
