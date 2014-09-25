<?php

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * Description of App
 *
 * @author Martin
 * @ODM\Document(repositoryClass="Repositories\AccessRepository")
 * @ODM\UniqueIndex(keys={"project"="asc", "user"="asc"})
 */
class ProjectAccess
{

	/**
	 * @ODM\Id 
	 */
	private $id;

	/**
	 * @var \Project
	 * @ODM\ReferenceOne(targetDocument="Project")
	 */
	private $project;

	/**
	 * @var \User
	 * @ODM\ReferenceOne(targetDocument="User")
	 */
	private $user;

	/**
	 * @ODM\String
	 * @ODM\Index
	 */
	private $level;


	public function getId()
	{
		return $this->id;
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
		return $this;
	}


	public function getLevel()
	{
		return $this->level;
	}


	public function setLevel($level)
	{
		$this->level = $level;
		return $this;
	}


}

