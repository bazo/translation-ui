<?php

namespace Facades;


class Translation extends Base
{

	protected $documentClass = 'Translation';

	public function findFilteredMessages($id, $filter = 'all', $search = NULL, $page = 1, $maxItems = 5)
	{
		$qb = $this->dm->getRepository('Message')->createQueryBuilder()
				->field('translation.id')->equals($id)
				->sort('singular', 'asc');

		switch ($filter) {
			case 'translated':
				$qb->field('translated')->equals(true);
				break;

			case 'untranslated':
				$qb->field('translated')->equals(false);
				break;
		}

		if (!empty($search)) {
			$regex = new \MongoRegex('/.*' . $search . '.*/i');
			$qb->addOr($qb->expr()->field('singular')->equals($regex));
			$qb->addOr($qb->expr()->field('translations')->equals($regex));
		}

		$offset = ($page - 1) * $maxItems;

		$qb->skip($offset)->limit($maxItems);


		return $qb->getQuery()->execute();
	}


	private function prepareTranslationsArray($count)
	{
		return array_fill(0, $count, '');
	}


	public function addMessageToProject(\Project $project, $values)
	{
		foreach ($project->getTranslations() as $translation) {
			$message = new \Message;
			$message->setContext($values->context)->setSingular($values->singular);

			if ($values->plural !== '') {
				$message->setPlural($values->plural);
			}

			$pluralsCount = $translation->getPluralsCount();
			$message->setPluralsCount($pluralsCount)
					->setTranslations($this->prepareTranslationsArray($pluralsCount))
					->setTranslation($translation);

			$res = $translation->addMessage($message);
			if ($res === TRUE) {
				$this->dm->persist($message);
				$this->dm->persist($translation);
			}
		}

		$templateMessage = [$values->singular => ['singular' => $values->singular, 'translations' => []]];
		$res			 = $project->addTemplateMessage($values->singular, $templateMessage);
		if ($res === TRUE) {
			$this->dm->persist($project);
		}
		$this->dm->flush();
		return $message;
	}


	private function formatDictionaryMessages(\Translation $translation)
	{
		$translatedMessages = $this->dm->createQueryBuilder('Message')
						->field('translation')->references($translation)
						->field('singular')->notEqual('')
						->getQuery()->execute();

		$messages = [];

		foreach ($translatedMessages as $message) {
			$messageArr = [];

			if ($message->getContext() !== null) {
				$messageArr['context'] = $message->getContext();
			}

			$messageArr['singular'] = $message->getSingular();

			if ($message->hasPlural()) {
				$messageArr['plural'] = $message->getPlural();
			}

			$messageArr['translations']			 = $message->getTranslations();
			$messages[$message->getSingular()]	 = $messageArr;
		}

		return $messages;
	}


	public function getDictionaryData(\Translation $translation)
	{
		$metadata = array(
			'plural-count'	 => $translation->getPluralsCount(),
			'plural-rule'	 => $translation->getPluralRule(),
			'creation-date'	 => date('d.m.Y H:i:s')
		);

		$data['messages']	 = $this->formatDictionaryMessages($translation);
		$data['lang']		 = $translation->getLang();
		$data['metadata']	 = $metadata;

		return $data;
	}


	public function getDictionary(\Translation $translation)
	{
		$data = $this->getDictionaryData($translation);

		return new \Mazagran\Translation\Dictionary($data);
	}


	public function importTranslation($data, \Translation $translation)
	{
		foreach ($translation->getMessages() as $message) {
			if (isset($data['messages'])) {
				$translations = $data['messages'][$message->getSingular()]['translations'];
			} else {
				$translations = [$data[$message->getSingular()]];
			}

			if (!empty(current($translations))) {
				$message->setTranslations($translations);
				$this->dm->persist($message);
			}
		}

		$this->dm->flush();
	}


	public function importPOTranslation($filename, \Translation $translation)
	{
		$parser	 = new \Sepia\PoParser;
		$data	 = $parser->parseFile($filename);

		foreach ($translation->getMessages() as $message) {
			$msgId = $message->getSingular();
			if (isset($data[$msgId])) {

				$entry = $data[$msgId];

				$trans = implode('|', $entry['msgstr']);
				$message->setTranslations($entry['msgstr']);
				$this->dm->persist($message);
			}
		}

		$this->dm->flush();
	}


}
