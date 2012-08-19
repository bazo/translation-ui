<?php
namespace Facades;
use Doctrine\ODM\MongoDB\DocumentManager;
use Nette\Utils\Strings;

class Project
{
	private
		/** @var DocumentManager */	
		$dm,
		
		/** @var KeyGenerator */	
		$keyGenerator
	;
	
	public function __construct(DocumentManager $dm, \Services\KeyGenerator $keyGenerator)
	{
		$this->dm = $dm;
		$this->keyGenerator = $keyGenerator;
	}
	
	/**
	 * @param type $id
	 * @return \Project
	 */
	public function find($id)
	{
		return $this->dm->getRepository('Project')->find($id);
	}
	
	public function delete($id)
	{
		$project = $this->dm->getRepository('Project')->find($id);
		/*
		$this->dm->getRepository('Log')->createQueryBuilder()
				->remove()->field('app')->references($project)
				->getQuery()->execute();
		*/
		$user = $project->getUser();
		$user->removeProject($project);
		
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
	public function createProject($caption, \User $user)
	{
		$project = new \Project;
		
		$key = $this->keyGenerator->generateKey();
		
		$name = Strings::webalize(Strings::lower(Strings::toAscii($caption)));
		$name = str_replace('-', '_', $name);
		$project->setCaption($caption)->setName($name)->setKey($key);
		
		$project->setUser($user);
		
		if($user->addProject($project) === false)
		{
			throw new \ExistingProjectException(sprintf('Project with name %s already exists ', $name));
		}
		
		$this->dm->persist($project);
		$this->dm->persist($user);
		$this->dm->flush();
		return $project;
	}
	
	public function createTranslation(\Project $project, $lang)
	{
		$translation = new \Translation;
		
		$pluralRule = \Translation\Langs::getPluralRule($lang);
		$pluralsCount = \Translation\Langs::getPluralsCount($lang);
		
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
			throw new \Exception(sprintf('plural rule for lang %s is broken. failed to evaluate the rule: %s', $lang, $rule));
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
		$singleTranslation = $this->prepareTranslationsArray(1);
		foreach($project->getTranslations() as $translation)
		{
			$pluralsCount = \Translation\Langs::getPluralsCount($translation->getLang());
			$translations = $this->prepareTranslationsArray($pluralsCount);
			
			foreach($data['messages'] as $messageId => $messageData)
			{
				$message = $this->prepareMessage($messageData, $translations, $singleTranslation, $pluralsCount);
				
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
			}
			$this->dm->persist($translation);
			$this->dm->flush();
		}
		
		$project->addTemplateMessages($data['messages']);
		$this->dm->persist($project);
		$this->dm->flush();
	}
	
}
