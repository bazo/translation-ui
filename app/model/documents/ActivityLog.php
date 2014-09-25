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

	/**
	 * @ODM\Id 
	 */
	private $id;

	/**
	 * @ODM\String 
	 * @ODM\Index
	 */
	private $activity;

	/**
	 * @ODM\String 
	 * @ODM\Index
	 */
	private $message;

	/** @ODM\ReferenceOne(targetDocument="Project") */
	private $project;

	/**
	 * @ODM\String 
	 * @ODM\Index
	 */
	private $actorNick;

	/**
	 * @ODM\String 
	 * @ODM\Index
	 */
	private $actorId;

	/**
	 * @ODM\String 
	 * @ODM\Index
	 */
	private $projectName;

	/**
	 * @ODM\Date  
	 * @ODM\Index(order="desc") 
	 */
	private $added;

	/**
	 * @ODM\Collection
	 */
	private $args = [];


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
		return vsprintf($this->message, array_merge([$this->projectName], $this->args));
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


	/**
	 * @param Project $project
	 * @return \ActivityLog
	 */
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

