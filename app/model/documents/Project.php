<?php

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Helpers\Message;



/**
 * Description of App
 *
 * @author Martin
 * @ODM\Document
 */
class Project
{

	/**
	 * @ODM\Id
	 */
	private $id;

	/**
	 * @ODM\String
	 * @ODM\Index
	 */
	private $caption;

	/**
	 * @ODM\String
	 * @ODM\Index
	 */
	private $name;

	/**
	 * @ODM\String
	 */
	private $key;

	/** @ODM\ReferenceOne(targetDocument="User") */
	private $owner;

	/** @ODM\ReferenceMany(targetDocument="ProjectAccess", repositoryMethod="getAccessesProject") */
	private $accesses;

	/**
	 * @ODM\ReferenceMany(targetDocument="Translation", cascade={"persist", "remove"})
	 * @ODM\Index
	 */
	private $translations;

	/**
	 * @ODM\Collection
	 * @ODM\Index
	 */
	private $translationLangs = [];

	/**
	 * @ODM\String
	 * @ODM\Index
	 */
	private $sourceLanguage;

	/**
	 * @ODM\String
	 * @ODM\Index
	 */
	private $link;

	/**
	 * @ODM\Hash
	 */
	private $templateMessages = [];

	public function __construct()
	{
		$this->translation = new Doctrine\Common\Collections\ArrayCollection;
		$this->accesses = new Doctrine\Common\Collections\ArrayCollection;
	}


	public function getId()
	{
		return $this->id;
	}


	public function getCaption()
	{
		return $this->caption;
	}


	public function setCaption($caption)
	{
		$this->caption = $caption;
		return $this;
	}


	public function getName()
	{
		return $this->name;
	}


	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}


	public function getKey()
	{
		return $this->key;
	}


	public function setKey($key)
	{
		$this->key = $key;
		return $this;
	}


	public function getOwner()
	{
		return $this->owner;
	}


	public function setOwner(\User $owner)
	{
		$this->owner = $owner;
		return $this;
	}


	public function getAccesses()
	{
		return $this->accesses;
	}


	public function getTranslations()
	{
		return $this->translations;
	}


	public function addTranslation(Translation $translation)
	{
		if (!in_array($translation->getLocale(), $this->translationLangs)) {
			$this->translationLangs[] = $translation->getLang();
			$this->translations->add($translation);
		} else {
			throw new ExistingTranslationException(sprintf('Project %s already has a translation for language: %s.', $this->caption, $translation->getLang()));
		}
	}


	public function getSourceLanguage()
	{
		return $this->sourceLanguage;
	}


	public function setSourceLanguage($sourceLanguage)
	{
		$this->sourceLanguage = $sourceLanguage;
		return $this;
	}


	public function getLink()
	{
		return $this->link;
	}


	public function setLink($link)
	{
		$this->link = $link;
		return $this;
	}


	public function getTemplateMessages()
	{
		return $this->templateMessages;
	}


	public function addTemplateMessages($templateMessages)
	{
		foreach ($templateMessages as $messageId => $templateMessage) {
			$this->addTemplateMessage($messageId, $templateMessage);
		}
		return $this;
	}


	public function hasTemplateMessage($messageId)
	{
		return isset($this->templateMessages[Message::encodeMessageId($messageId)]);
	}


	public function addTemplateMessage($messageId, $templateMessage)
	{
		if (!$this->hasTemplateMessage($messageId)) {
			unset($templateMessage['files']);
			$this->templateMessages[Message::encodeMessageId($messageId)] = $templateMessage;
			return TRUE;
		}
		return FALSE;
	}


	public function removeTemplateMessage($singular)
	{
		$id = Message::encodeMessageId($singular);
		unset($this->templateMessages[$id]);
		return $this;
	}


}