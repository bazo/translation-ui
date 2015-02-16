<?php

namespace Facades;


use Doctrine\ODM\MongoDB\DocumentManager;
use Nette\Utils\Strings;
use Bazo\Translation\Langs;

class Project extends Base
{

	/** @var KeyGenerator */
	private $keyGenerator;
	protected $documentClass = 'Project';

	public function __construct(DocumentManager $dm, \Services\KeyGenerator $keyGenerator)
	{
		parent::__construct($dm);
		$this->keyGenerator = $keyGenerator;
	}


	public function delete(\Project $project)
	{
		$user = $project->getOwner();

		//$access = $this->dm->getRepository('ProjectAccess')
		//		->getAccessForUserAndProject($user, $project);

		//$this->dm->remove($access);

		$this->dm->remove($project);
		//$this->dm->persist($user);

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

		$name	 = Strings::webalize(Strings::lower(Strings::toAscii($values->caption)));
		$name	 = str_replace('-', '_', $name);
		$project
				->setCaption($values->caption)
				->setName($name)
				->setSourceLanguage($values->sourceLang)
				->setLink($values->link)
				->setKey($key)
		;

		$project->setOwner($user);

		$this->dm->persist($project);
		$this->dm->flush();

		return $project;
	}


	public function createTranslation(\Project $project, $locale)
	{
		$translation = new \Translation;

		$lang = substr($locale, 0, 2);

		$pluralRule		 = Langs::getPluralRule($lang);
		$pluralsCount	 = Langs::getPluralsCount($lang);
		$plurals		 = $this->getPlurals($locale, $pluralsCount);
		$language		 = \Symfony\Component\Intl\Intl::getLocaleBundle()->getLocaleName($locale);

		$translation
				->setLang($lang)
				->setLocale($locale)
				->setLanguage($language)
				->setProject($project)
				->setPluralRule($pluralRule)
				->setPluralsCount($pluralsCount)
				->setPluralNumbers($plurals)
		;

		$translations = $this->prepareTranslationsArray($pluralsCount);

		foreach ($project->getTemplateMessages() as $messageData) {
			$message = $this->prepareMessage($messageData, $translations, $pluralsCount);
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


	private function arrayFilled(&$array)
	{
		$filled = TRUE;
		foreach ($array as $key => $value) {
			if ($value === NULL) {
				$filled = FALSE;
				break;
			}
		}
		return $filled;
	}


	private function getPlurals($locale, $count)
	{
		$numbers = array_fill(0, $count, NULL);
		$n		 = 1;
		do {
			$plural = \Symfony\Component\Translation\PluralizationRules::get($n, $locale);
			if (!isset($numbers[$plural])) {
				$numbers[$plural] = $n;
			}
			$n++;
			if ($n > 10000) {
				break;
			}
		} while (!$this->arrayFilled($numbers));

		return $numbers;
	}


	public function authenticateProject($projectId, $projectKey)
	{
		$project = $this->dm->getRepository('Project')->find($projectId);
		if ($project === NULL or $project->getKey() !== $projectKey) {
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
	private function prepareMessage($messageData, $translations, $pluralsCount)
	{
		//workaround around something
		if (!isset($messageData['singular'])) {
			$messageData = current($messageData);
		}

		$message = $messageData['singular'];

		$context = 'messages';
		if (isset($messageData['context'])) {
			$context = $messageData['context'];
		} else {
			if (strpos($message, '.') !== FALSE && strpos($message, ' ') === FALSE) {
				list($context, $message) = explode('.', $message, 2);
			}
		}

		$msg = new \Message;
		$msg
				->setSingular($message)
				->setPluralsCount($pluralsCount)
		;

		$msg->setContext($context);
		$msg->setTranslations($translations);

		return $msg;
	}


	public function importTemplate($data, \Project $project)
	{
		$translations = $project->getTranslations();

		if ($translations->count() === 0) {
			return 0;
		}

		$imported			 = 0;
		$singleTranslation	 = $this->prepareTranslationsArray(1);

		$translationsData = [];

		foreach ($translations as $translation) {
			$pluralsCount								 = Langs::getPluralsCount($translation->getLang());
			$translationsData[$translation->getLang()]	 = array(
				'pluralsCount'	 => $pluralsCount,
				'translations'	 => $this->prepareTranslationsArray($pluralsCount)
			);
		}

		foreach ($translations as $translation) {
			$translationData = $translationsData[$translation->getLang()];
			foreach ($data['messages'] as $messageId => $messageData) {

				$project->addTemplateMessage($messageId, $messageData);
				$message = $this->prepareMessage($messageData, $translationData['translations'], $singleTranslation, $translationData['pluralsCount']);
				$added	 = $translation->addMessage($messageId, $message);

				if ($added) {
					$this->dm->persist($message);
					$imported++;
				}
			}


			$this->dm->persist($translation);
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


	public function findAll()
	{
		return $this->dm->getRepository(\Project::class)->findAll();
	}


	public function getTranslations(\Project $project)
	{
		$qb = $this->dm->getRepository(\Translation::class)->createQueryBuilder();

		$qb->field('project')->references($project)
				->sort('language');

		return $qb->getQuery()->execute();
	}


}
