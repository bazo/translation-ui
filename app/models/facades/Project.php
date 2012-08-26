<?php
namespace Facades;
use Doctrine\ODM\MongoDB\DocumentManager;
use Nette\Utils\Strings;

use Mazagran\Translation\Langs;

class Project extends Base
{
	private
		/** @var KeyGenerator */	
		$keyGenerator
	;
	
	protected
		$documentClass = 'Project'
	;
	
	public function __construct(DocumentManager $dm, \Services\KeyGenerator $keyGenerator)
	{
		parent::__construct($dm);
		$this->keyGenerator = $keyGenerator;
	}
	
	public function delete(\Project $project)
	{
		$user = $project->getOwner();
		$user->removeProject($project);
		
		$access = $this->dm->getRepository('ProjectAccess')
				->getAccessForUserAndProject($user, $project);
		
		$this->dm->remove($access);
		
		$this->dm->remove($project);
		$this->dm->persist($user);
		
		$this->dm->flush();
	}
	
	/**
	 * @param type $name
	 * @param \User $user
	 * @return \Project
	 * @throws \ExistingProjectException 
	 */
	public function createProject($values, \User $user)
	{
		$project = new \Project;
		
		$key = $this->keyGenerator->generateKey();
		
		$name = Strings::webalize(Strings::lower(Strings::toAscii($values->caption)));
		$name = str_replace('-', '_', $name);
		$project->setCaption($values->caption)->setName($name)->setSourceLanguage($values->sourceLang)->setLink($values->link)->setKey($key);
		
		$project->setOwner($user);
		
		if($user->addProject($project) === false)
		{
			throw new \ExistingProjectException(sprintf('Project with name %s already exists ', $name));
		}
		
		$this->addCollaboratorToProject($user, $project, \Access::OWNER);
		/*
		$this->dm->persist($project);
		$this->dm->persist($user);
		$this->dm->flush();
		 * 
		 */
		return $project;
	}
	
	public function createTranslation(\Project $project, $lang)
	{
		$translation = new \Translation;
		
		$pluralRule = Langs::getPluralRule($lang);
		$pluralsCount = Langs::getPluralsCount($lang);
		
		$translation->setLang($lang)->setProject($project)->setPluralRule($pluralRule)->setPluralsCount($pluralsCount)
				->setPluralNumbers($this->getPluralNumbers($pluralRule, $pluralsCount, $lang));
		
		$singleTranslation = $this->prepareTranslationsArray(1);
		$translations = $this->prepareTranslationsArray($pluralsCount);
		
		foreach($project->getTemplateMessages() as $messageData)
		{
			$message = $this->prepareMessage($messageData, $translations, $singleTranslation, $pluralsCount);
			$translation->addMessage($message);
			$message->setTranslation($translation);
				
			$this->dm->persist($message);
		}
		
		$project->addTranslation($translation);
		
		$this->dm->persist($translation);
		$this->dm->persist($project);
		
		$this->dm->flush();
		
		return $translation;
	}
	
	private function evaluateRule($n, $rule)
	{
		$tmp = preg_replace('/([a-z]+)/', '$$1', "n=$n;" . $rule);
		eval($tmp);
		return $plural;
	}
	
	private function arrayFilled(&$array)
	{
		$filled = true;
		foreach($array as $key => $value)
		{
			if($value === null)
			{
				$filled = false;
				break;
			}
		}
		return $filled;
	}
	
	private function getPluralNumbers($rule, $count, $lang)
	{
		$numbers = array_fill(0, $count, null);
		$n = 1;
		do
		{
			$plural = $this->evaluateRule($n, $rule);
			if(!isset($numbers[$plural]))
			{
				$numbers[$plural] = $n;
			}
			$n++;
			if($n > 10000) break;
		}while(!$this->arrayFilled($numbers));
		
		if(!$this->arrayFilled($numbers))
		{
			throw new \InvalidPluralRuleException(sprintf('plural rule for lang %s is broken. failed to evaluate the rule: %s', $lang, $rule));
		}
		return $numbers;
	}
	
	public function authenticateProject($projectId, $projectKey)
	{
		$project = $this->dm->getRepository('Project')->find($projectId);
		if($project === null or $project->getKey() !== $projectKey)
		{
			throw new \Nette\Security\AuthenticationException('Bad authentication credentials.');
		}
		return $project;
	}
	
	/**
	 * @param int $count
	 * @return array
	 */
	private function prepareTranslationsArray($count)
	{
		return array_fill(0, $count, '');
	}
	
	/**
	 * @param array $messageData
	 * @return \Message
	 */
	private function prepareMessage($messageData, $translations, $singleTranslation, $pluralsCount)
	{
		$message = new \Message;
		$message->setSingular($messageData['singular'])->setPluralsCount($pluralsCount);

		if(isset($messageData['context']))
		{
			$message->setContext($messageData['context']);
		}

		if(isset($messageData['plural']))
		{
			$message->setPlural($messageData['plural']);
			$message->setTranslations($translations);
		}
		else
		{
			$message->setTranslations($singleTranslation);
		}
		
		return $message;
	}
	
	public function importTemplate($data, \Project $project)
	{
		$imported = 0;
		$singleTranslation = $this->prepareTranslationsArray(1);
		
		$translations = $project->getTranslations();
		
		$translationsData = array();
		
		foreach($translations as $translation)
		{
			$pluralsCount = Langs::getPluralsCount($translation->getLang());
			$translationsData[$translation->getLang()] = array(
				'pluralsCount' => $pluralsCount,
				'translations' => $this->prepareTranslationsArray($pluralsCount)
			);
		}
		
		foreach($data['messages'] as $messageId => $messageData)
		{
			if(!$project->hasTemplateMessage($messageId))
			{
				$project->addTemplateMessage($messageId, $messageData);
				
				foreach($translations as $translation)
				{
					$translationData = $translationsData[$translation->getLang()];
					$message = $this->prepareMessage($messageData, $translationData['translations'], $singleTranslation, $translationData['pluralsCount']);
					try
					{
						$translation->addMessage($message);
					}
					catch(\ExistingMessageException $e)
					{
						//ignore
					}

					$message->setTranslation($translation);

					$this->dm->persist($message);
					//$this->dm->flush();
				}
				$imported++;
			}
		}
		
		$this->dm->persist($project);
		$this->dm->flush();
		
		return $imported;
	}
	
	public function addMessage($messageData, \Project $project)
	{
		$data = array('messages' => array($messageData));
		$this->importTemplate($data, $project);
	}
	
	public function addCollaboratorToProject(\User $user, \Project $project, $level)
	{
		$access = new \ProjectAccess;
		$access->setUser($user)->setProject($project)->setLevel($level);
		
		$this->dm->persist($access);
		
		$this->dm->flush();
		
		return $access;
	}
	
}
