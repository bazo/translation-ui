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
		
		return $message;
	}
	
	public function delete(\Message $originalMessage)
	{
		$singular = $originalMessage->getSingular();
		$project = $originalMessage->getTranslation()->getProject();
		
		$result = $this->dm->createQueryBuilder('Translation')
				->select('id')
				->field('project.id')->equals($project->getId())
				->hydrate(false)
				->getQuery()->execute()->toArray();
		
		$ids = array_keys($result);
		
		$messages = $this->dm->createQueryBuilder('Message')
				->field('translation.id')->in($ids)
				->field('singular')->equals($singular)
				->getQuery()->execute();
		
		foreach($messages as $message)
		{
			$translation = $message->getTranslation();
			
			$translation->removeMessage($message);
			$this->dm->persist($translation);
			$this->dm->remove($message);
			
			$this->dm->flush();
		}
		
		$project->removeTemplateMessage($singular);
		$this->dm->flush();
	}
	
}
