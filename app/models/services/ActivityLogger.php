<?php
namespace Services;
/**
 * Logger
 *
 * @author Martin
 */
class ActivityLogger
{
	private
		/** @var \Doctrine\ODM\MongoDB\DocumentManager */	
		$dm
	;
	
	function __construct(\Doctrine\ODM\MongoDB\DocumentManager $dm)
	{
		$this->dm = $dm;
	}
	
	public function log(\User $user, \Project $project, $activity, $object)
	{
		$log = new \ActivityLog;
		
		$message = \Activity::getMessage($activity);
		
		$args = array();
		switch($activity)
		{
			case \Activity::ADD_COLLABORATOR:
			case \Activity::REMOVE_COLLABORATOR:	
				$args[] = $object->getUser()->getNick();
				$args[] = $object->getLevel();
				break;
			
			case \Activity::REMOVE_TRANSLATION:
			case \Activity::CREATE_TRANSLATION:
				$args[] = \AllowedLangs::getLangCaption($object->getLang());
				break;
			
			case \Activity::DELETE_MESSAGE:
			case \Activity::ADD_MESSAGE:
				$args[] = $object->getSingular();
				break;
			
			case \Activity::IMPORT_TEMPLATE:
				$args[] = $object; //count of imported messages
				$args[] = count($project->getTemplateMessages()); //total count
				break;
			
			case \Activity::TRANSLATE_SINGULAR:
				$args[] = $object->getSingular();
				$translations = $object->getTranslations();
				$args[] = array_shift($translations);
				break;
			
			case \Activity::TRANSLATE_PLURAL:
				$args[] = $object->getPlural();
				$args[] = implode(', ', $object->getTranslations());
				break;
		}
		
		$log->setActorId($user->getId())->setActorNick($user->getNick())
				->setProject($project)->setActivity($activity)
				->setMessage($message)->setArgs($args);
		
		$this->dm->persist($log);
		$this->dm->flush();
	}

}
