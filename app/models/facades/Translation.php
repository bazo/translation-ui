<?php
namespace Facades;
use Nette\Utils\Strings;

class Translation extends Base
{
	protected
		$documentClass = 'Translation'
	;
	
	public function findFilteredMessages($id, $filter = 'all')
	{
		$qb = $this->dm->getRepository('Message')->createQueryBuilder();
				//->field('translation.id')->equals($id);
		
		switch($filter)
		{
			case 'translated':
				$qb->field('translated')->equals(true);
				break;
			
			case 'untranslated':
				$qb->field('translated')->equals(false);
				break;
		}
		
		return $qb->getQuery()->execute();
	}
	
	public function findAllTranslationsForUser(\User $user)
	{
		$translations = array();
		$projects = $user->getProjects();
		
	}
	
	private function prepareTranslationsArray($count)
	{
		return array_fill(0, $count, '');
	}
	
	public function addMessageToProject(\Project $project, $values)
	{
		foreach($project->getTranslations() as $translation)
		{
			$message = new \Message;
			$message->setContext($values->context)->setSingular($values->singular);
			
			if($values->plural !== '')
			{	
				$message->setPlural($values->plural);
			}
			
			$pluralsCount = $translation->getPluralsCount();
			$message->setPluralsCount($pluralsCount)
					->setTranslations($this->prepareTranslationsArray($pluralsCount))
					->setTranslation($translation);
			$translation->addMessage($message);
			
			$this->dm->persist($message);
			$this->dm->persist($translation);
			
			$this->dm->flush();
		}
	}
	
	private function formatDictionaryMessages(\Translation $translation)
	{
		$translatedMessages = $this->dm->createQueryBuilder('Message')
				->field('translated')->equals(true)
				->field('translation.id')->equals($translation->getId())
				->getQuery()->execute();
		
		$messages = array();
		
		foreach($translatedMessages as $message)
		{
			$messageArr = array();
			
			if($message->getContext() !== null)
			{
				$messageArr['context'] = $message->getContext();
			}
			
			$messageArr['singular'] = $message->getSingular();
			
			if($message->hasPlural())
			{
				$messageArr['plural'] = $message->getPlural();
			}
			
			$messageArr['translations'] = $message->getTranslations();
			$messages[$message->getSingular()] = $messageArr;
		}
		
		return $messages;
	}
	
	public function getDictionaryData(\Translation $translation)
	{
		$metadata = array(
			'plural-count' => $translation->getPluralsCount(),
			'plural-rule' => $translation->getPluralRule(),
			'creation-date' => date('d.m.Y H:i:s')
		);
		
		$data['messages'] = $this->formatDictionaryMessages($translation);
		$data['lang'] = $translation->getLang();
		$data['metadata'] = $metadata;
		
		return $data;
	}
	
	public function getDictionary(\Translation $translation)
	{
		$data = $this->getDictionaryData($translation);
		
		return new \Translation\Dictionary($data);
	}
	
	public function importTranslation($data, \Translation $translation)
	{
		foreach($translation->getMessages() as $message)
		{
			$message->setTranslations($data['messages'][$message->getSingular()]['translations']);
			$this->dm->persist($message);
		}
		
		$this->dm->flush();
	}
}
