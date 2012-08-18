<?php
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
/**
 * Log
 *
 * @author martin.bazik
 * @ODM\Document
 */
class Translation
{
	private 
		/** 
		 * @ODM\Id 
		 */	
		$id,
		
		/**
		 * @ODM\String
		 * @ODM\Index
		 */	
		$lang,
		
		/**
		 * @ODM\Int
		 */		
		$pluralsCount,
		
		/**
		 * @ODM\String
		 */		
		$pluralRule,
			
		/**
		 * @ODM\Hash
		 */		
		$pluralNumbers,	
			
		/**
		 * @ODM\Int
		 */	
		$messagesCount,
			
		/**
		 * @ODM\Int
		 */	
		$translated,	
			
		/** 
		 * @ODM\ReferenceMany(targetDocument="Message") 
		 * @ODM\Index
		 */		
		$messages,	
			
		/**
		 * @ODM\Collection
		 * @ODM\Index
		 */	
		$messageIds = array(),	
			
		/** @ODM\ReferenceOne(targetDocument="Project") */	
		$project,
			
		/** 
		 * @ODM\ReferenceMany(targetDocument="User") 
		 * @ODM\Index
		 */	
		$user,	
			
		/** @ODM\Date  
		 * @ODM\Index 
		 */	
		$created
	;
	
	public function __construct()
	{
		$this->created = new DateTime;
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

	public function getTranslated()
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
		if(!in_array($message->getSingular(), $this->messageIds))
		{
			$this->messages->add($message);
			$this->messageIds[] = $message->getSingular();
			return $this;
		}
	}

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
}