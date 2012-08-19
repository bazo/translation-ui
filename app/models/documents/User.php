<?php
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
/**
 * Description of User
 *
 * @author Martin
 * @ODM\Document
 * 
 */
class User extends Gridder\Document implements Nette\Security\IIdentity
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
		$nick,	
			
		/**
		 * @ODM\String
		 * @ODM\Index(unique=true) 
		 */	
		$email,
		
		/**
		 * @ODM\String 
		 */	
		$password,
			
		/** @ODM\ReferenceMany(targetDocument="Project") */	
		$projects,
			
		/**
		 * @ODM\Collection
		 * @ODM\Index 
		 */	
		$projectNames = array(),
		
		/**
		 * @ODM\Boolean 
		 */	
		$active = false,
		
		/**
		 * @ODM\String 
		 * @ODM\Index
		 */		
		$account = 'basic',
		
		/** @var AccountRule */	
		$accountRule
	;
	
	public function __construct()
	{
		$this->projects = new \Doctrine\Common\Collections\ArrayCollection;
	}
	
	public function getId()
	{
		return $this->id;
	}
	
	public function getRoles()
	{
		return array(
			'user'
		);
	}
	
	public function getNick()
	{
		return $this->nick;
	}

	public function setNick($nick)
	{
		$this->nick = $nick;
		return $this;
	}
		
	public function getEmail()
	{
		return $this->email;
	}

	public function setEmail($email)
	{
		$this->email = $email;
		return $this;
	}
	
	public function getPassword()
	{
		return $this->password;
	}

	public function setPassword($password)
	{
		$this->password = $password;
		return $this;
	}
	
	public function getProjects()
	{
		return $this->projects;
	}

	public function addProject(Project $project)
	{
		if(!in_array($project->getName(), $this->projectNames))
		{
			$this->projectNames[] = $project->getName();
			$this->projects->add($project);
			return $this;
		}
		else
		{
			return false;
		}
	}
	
	public function removeProject(Project $project)
	{
		$projectNameKey = array_search($project->getName(), $this->projectNames);
		unset($this->projectNames[$projectNameKey]);
		$this->projects->removeElement($project);
		return $this;
	}
	
	public function getProjectNames()
	{
		return $this->projectNames;
	}

	public function isActive()
	{
		return $this->active;
	}

	public function setActive($active)
	{
		$this->active = $active;
		return $this;
	}
	
	public function getAccount()
	{
		return $this->account;
	}

	public function setAccount($account)
	{
		$this->account = $account;
		return $this;
	}
	
	public function getAccountRule()
	{
		return $this->accountRule;
	}

	public function setAccountRule($accountRule)
	{
		$this->accountRule = $accountRule;
		return $this;
	}
		
	public function canAddProject()
	{
		return $this->accountRule->canAddProject($this);
	}
	
	public function canAddTranslation(\Project $project)
	{
		return $this->accountRule->canAddTranslation($this, $project);
	}
}