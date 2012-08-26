<?php
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
/**
 * Log
 *
 * @author martin.bazik
 * @ODM\Document(repositoryClass="Repositories\ActivityLogRepository")
 */
class ActivityLog
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
		$activity,	
			
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
		 * @ODM\Index(order="desc") 
		 */	
		$added,
		
		/**
		 * @ODM\Collection
		 */	
		$args = array()
	;
	
	public function __construct()
	{
		$this->added = new DateTime;
	}
	
	public function getId()
	{
		return $this->id;
	}

	public function getActivity()
	{
		return $this->activity;
	}

	public function setActivity($activity)
	{
		$this->activity = $activity;
		return $this;
	}
		
	public function getMessage()
	{
		return vsprintf($this->message, array_merge(array($this->projectName), $this->args));
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
	
	public function getArgs()
	{
		return $this->args;
	}

	public function setArgs($args)
	{
		$this->args = $args;
		return $this;
	}
}