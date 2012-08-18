<?php
namespace Facades;
use Doctrine\ODM\MongoDB\DocumentManager;
use Nette\Utils\Strings;

class Message
{
	private
		/** @var DocumentManager */	
		$dm
	;
	
	public function __construct(DocumentManager $dm)
	{
		$this->dm = $dm;
	}
	
	/**
	 * @param type $id
	 * @return \Message
	 */
	public function find($id)
	{
		return $this->dm->getRepository('Message')->find($id);
	}
	
	public function translateMessage(\Message $message, $translations)
	{
		$message->setTranslations($translations);
		
		$this->dm->persist($message);
		
		$this->dm->flush();
	}
	
}
