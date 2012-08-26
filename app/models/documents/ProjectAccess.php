<?php
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of App
 *
 * @author Martin
 * @ODM\Document(repositoryClass="Repositories\AccessRepository")
 * @ODM\UniqueIndex(keys={"project"="asc", "user"="asc"})
 */
class ProjectAccess
{
	private 
		/** 
		 * @ODM\Id 
		 */	
		$id,
			
		/**
		 * @var \Project
		 * @ODM\ReferenceOne(targetDocument="Project")
		 */	
		$project,
		
		/**
		 * @var \User
		 * @ODM\ReferenceOne(targetDocument="User")
		 */		
		$user,
		
		/**
		 * @ODM\String
		 * @ODM\Index
		 */	
		$level
	;
	
	public function __construct()
	{
	}
	
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