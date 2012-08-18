<?php
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
/**
 * Log
 *
 * @author martin.bazik
 * @ODM\Document
 */
class Log
{
	const 
		ERROR = 'error',
		SUCCESS = 'success',
		INFO = 'info',
		NOTICE = 'notice'
	;

	private 
		/** 
		 * @ODM\Id 
		 */	
		$id,
		
		/**
		 * @ODM\String
		 * @ODM\Index
		 */	
		$level,
		
		/**
		 * @ODM\String 
		 * @ODM\Index
		 */	
		$message,
			
		/** @ODM\ReferenceOne(targetDocument="Project") */	
		$app,
			
		/** @ODM\ReferenceOne(targetDocument="User") @ODM\Index */	
		$user,	
		
		/**
		 * @ODM\String 
		 * @ODM\Index
		 */	
		$appName,	
			
		/** @ODM\Date  @ODM\Index */	
		$added,
		
		/**
		 * @ODM\Collection
		 * @ODM\Index(order="desc")
		 */	
		$index,
		
		/**
		 * @ODM\Increment 
		 */	
		$count = 1
	;
	
	public function __construct()
	{
		$this->added = new DateTime;
	}
	
	public function getId()
	{
		return $this->id;
	}

	public function getLevel()
	{
		return $this->level;
	}

	public function setLevel($level = 'notice')
	{
		if($level === '')
		{
			$level = 'notice';
		}
		$this->level = $level;
		return $this;
	}

	public function getMessage()
	{
		return $this->message;
	}

	public function setMessage($message)
	{
		$this->message = $message;
		return $this;
	}

	public function getApp()
	{
		return $this->app;
	}

	public function setApp(App $app)
	{
		$this->app = $app;
		$this->appName = $app->getName();
		return $this;
	}

	public function addIndex($indexExpression)
	{
		$this->index[] = $indexExpression;
		return $this;
	}
	
	public function getUser()
	{
		return $this->user;
	}

	public function setUser(User $user)
	{
		$this->user = $user;
		return $this;
	}
	
	public function getAdded()
	{
		return $this->added;
	}
	
	public function getCount()
	{
		return $this->count;
	}

	public function addCount()
	{
		$this->count++;
		return $this;
	}
}