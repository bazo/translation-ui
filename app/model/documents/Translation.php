<?php

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Helpers\Message;



/**
 * Log
 *
 * @author martin.bazik
 * @ODM\Document
 */
class Translation
{

	/**
	 * @ODM\Id
	 */
	private $id;

	/**
	 * @ODM\String
	 * @ODM\Index
	 */
	private $lang;

	/**
	 * @ODM\String
	 * @ODM\Index
	 */
	private $language;

	/**
	 * @ODM\String
	 * @ODM\Index
	 */
	private $locale;

	/**
	 * @ODM\Int
	 */
	private $pluralsCount = 1;

	/**
	 * @ODM\String
	 */
	private $pluralRule;

	/**
	 * @ODM\Hash
	 */
	private $pluralNumbers = [];

	/**
	 * @ODM\Int
	 */
	private $messagesCount = 0;

	/**
	 * @ODM\Int
	 */
	private $translated = 0;

	/**
	 * @ODM\ReferenceMany(targetDocument="Message", cascade={"persist", "remove"})
	 * @ODM\Index
	 */
	private $messages;

	/**
	 * @ODM\Hash
	 * @ODM\Index
	 */
	private $messageIds = [];

	/** @ODM\ReferenceOne(targetDocument="Project") */
	private $project;

	/**
	 * @ODM\ReferenceMany(targetDocument="User")
	 * @ODM\Index
	 */
	private $user;

	/** @ODM\Date
	 * @ODM\Index
	 */
	private $created;

	public function __construct()
	{
		$this->created = new DateTime;
		$this->messages = new \Doctrine\Common\Collections\ArrayCollection;
	}


	public function getId()
	{
		return $this->id;
	}


	public function getLang()
	{
		return $this->lang;
	}


	public function setLang($lang)
	{
		$this->lang = $lang;
		return $this;
	}


	public function getLocale()
	{
		return $this->locale;
	}


	public function setLocale($locale)
	{
		$this->locale = $locale;
		return $this;
	}


	public function getPluralsCount()
	{
		return $this->pluralsCount;
	}


	public function setPluralsCount($pluralsCount)
	{
		$this->pluralsCount = $pluralsCount;
		return $this;
	}


	public function getPluralRule()
	{
		return $this->pluralRule;
	}


	public function setPluralRule($pluralRule)
	{
		$this->pluralRule = $pluralRule;
		return $this;
	}


	public function getPluralNumbers()
	{
		return $this->pluralNumbers;
	}


	public function setPluralNumbers($pluralNumbers)
	{
		$this->pluralNumbers = $pluralNumbers;
		return $this;
	}


	public function getMessagesCount()
	{
		return $this->messagesCount;
	}


	public function setMessagesCount($messagesCount)
	{
		$this->messagesCount = $messagesCount;
		return $this;
	}


	public function getTranslatedCount()
	{
		return $this->translated;
	}


	public function setTranslated($translated)
	{
		$this->translated = $translated;
		return $this;
	}


	public function getMessages()
	{
		return $this->messages;
	}


	public function addMessage(\Message $message)
	{
		if (!in_array($message->getSingular(), $this->messageIds)) {
			$this->messages->add($message);
			$this->messageIds[Message::encodeMessageId($message->getSingular())] = $message->getSingular();
			$this->messagesCount++;
			return TRUE;
		} else {
			return FALSE;
			//throw new \ExistingMessageException(sprintf('Translation already contains message %s', $message->getSingular()));
		}
	}


	public function hasMessage(\Message $message)
	{
		return $this->messages->contains($message);
	}


	public function removeMessage(\Message $message)
	{
		$this->messages->removeElement($message);
		unset($this->messageIds[\Helpers\Message::encodeMessageId($message->getId())]);
		return $this;
	}


	/**
	 * @return \Project
	 */
	public function getProject()
	{
		return $this->project;
	}


	public function setProject($project)
	{
		$this->project = $project;
		return $this;
	}


	public function getUser()
	{
		return $this->user;
	}


	public function setUser($user)
	{
		$this->user = $user;
	}


	public function getAdded()
	{
		return $this->added;
	}


	public function setAdded($added)
	{
		$this->added = $added;
	}


	public function getIndex()
	{
		return $this->index;
	}


	public function setIndex($index)
	{
		$this->index = $index;
	}


	public function getCreated()
	{
		return $this->created;
	}


	public function getTranslatedMessages()
	{
		$translatedMessages = $this->messages->filter(function (\Message $message) {
			if ($message->isTranslated()) {
				return TRUE;
			}
		});
		return $translatedMessages;
	}


	public function getTranslatedMessagesCount()
	{

		return count($this->getTranslatedMessages());
	}


	public function getCompletionPercentage($decimals = 2)
	{
		if ($this->getMessagesCount() === 0) {
			return 0;
		}
		return round(($this->getTranslatedMessagesCount() / $this->getMessagesCount()) * 100, $decimals);
	}


	public function getLanguage()
	{
		return $this->language;
	}


	public function setLanguage($language)
	{
		$this->language = $language;
	}


}