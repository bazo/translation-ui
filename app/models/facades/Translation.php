<?php
namespace Facades;
use Doctrine\ODM\MongoDB\DocumentManager;
use Nette\Utils\Strings;

class Translation
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
	 * @return \Translation
	 */
	public function find($id)
	{
		return $this->dm->getRepository('Translation')->find($id);
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
	
	public function addMessageToProject(\Project $project, \Message $message)
	{
		foreach($project->getTranslations() as $translation)
		{
			$pluralsCount = $translation->getPluralsCount();
			$message->setPluralsCount($pluralsCount)->setTranslations($this->prepareTranslationsArray($pluralsCount));
			$translation->addMessage($message);
			
			$this->dm->persist($message);
			$this->dm->persist($translation);
			
			$this->dm->flush();
		}
	}
	
	public function getDictionary(\Translation $translation)
	{
		
	}
}
