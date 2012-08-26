<?php
use Doctrine\ODM\MongoDB\Mprojecting\Annotations as ODM;
/**
 * Log
 *
 * @author martin.bazik
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
		$message,
			
		/** @ODM\ReferenceOne(targetDocument="Project") */	
		$project,

		/**
		 * @ODM\String 
		 * @ODM\Index
		 */		
		$actorNick,
		
		/**
		 * @ODM\String 
		 * @ODM\Index
		 */		
		$actorId,
			
		/**
		 * @ODM\String 
		 * @ODM\Index
		 */	
		$projectName,	
			
		/** 
		 * @ODM\Date  
		 * @ODM\Index(sort="desc") 
		 */	
		$added		
	;
	
	public function __construct()
	{
		$this->added = new DateTime;
	}
	
	public function getId()
	{
		return $this->id;
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

	public function getProject()
	{
		return $this->project;
	}

	public function setProject(Project $project)
	{
		$this->project = $project;
		$this->projectName = $project->getName();
		return $this;
	}

	public function getActorNick()
	{
		return $this->actorNick;
	}

	public function setActorNick($actorNick)
	{
		$this->actorNick = $actorNick;
		return $this;
	}

	public function getActorId()
	{
		return $this->actorId;
	}

	public function setActorId($actorId)
	{
		$this->actorId = $actorId;
		return $this;
	}
		
	public function getAdded()
	{
		return $this->added;
	}
}