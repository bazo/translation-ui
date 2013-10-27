<?php

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Nette\Utils\Strings;

/**
 * Description of User
 *
 * @author Martin
 * @ODM\Document
 * 
 */
class User extends Gridder\Document implements Nette\Security\IIdentity
{

	/**
	 * @ODM\Id
	 */
	private $id;

	/**
	 * @ODM\String
	 * @ODM\Index(unique=true)
	 */
	private $nick;

	/**
	 * @ODM\String
	 * @ODM\Index(unique=true) 
	 */
	private $email;

	/**
	 * @ODM\String 
	 */
	private $password;

	/** @ODM\ReferenceMany(targetDocument="Project") */
	private $projects;

	/**
	 * @ODM\ReferenceMany(targetDocument="ProjectAccess", repositoryMethod="getAccesses") 
	 */
	private $accesses;

	/**
	 * @ODM\Collection
	 * @ODM\Index 
	 */
	private $projectNames = [];

	/**
	 * @ODM\Increment
	 * @ODM\Index 
	 */
	private $projectCount = 0;

	/**
	 * @ODM\Boolean 
	 */
	private $active = FALSE;

	/**
	 * @ODM\String 
	 * @ODM\Index
	 */
	private $account = 'basic';

	/** @var AccountRule */
	private $accountRule;

	/**
	 * @ODM\ReferenceMany(targetDocument="ActivityLog", repositoryMethod="getUserLogs")
	 */
	private $logs;

	/**
	 * @ODM\Date
	 * @ODM\Index(order="desc")
	 */
	private $registered;


	public function __construct()
	{
		$this->projects = new \Doctrine\Common\Collections\ArrayCollection;
		$this->accesses = new Doctrine\Common\Collections\ArrayCollection;
		$this->registered = new DateTime;
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
		if (!in_array($project->getName(), $this->projectNames)) {
			$this->projectNames[] = $project->getName();
			$this->projects->add($project);
			$this->projectCount++;
			return $this;
		} else {
			return FALSE;
		}
	}


	public function removeProject(Project $project)
	{
		if ($this->projects->contains($project)) {
			$projectNameKey = array_search($project->getName(), $this->projectNames);
			unset($this->projectNames[$projectNameKey]);
			$this->projects->removeElement($project);
			$this->projectCount--;
		}
		return $this;
	}


	public function getAccesses($levels = [])
	{
		if (!empty($levels)) {
			return $this->accesses->filter(function(\ProjectAccess $access) use($levels) {
						if (in_array($access->getLevel(), $levels)) {
							return TRUE;
						}
					});
		}
		return $this->accesses;
	}


	public function getProjectNames()
	{
		return $this->projectNames;
	}


	public function getProjectCount()
	{
		return $this->projectCount;
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


	public function getGravatar()
	{
		return md5(Strings::lower(Strings::trim($this->email)));
	}


	public function getLogs()
	{
		return $this->logs;
	}


}

