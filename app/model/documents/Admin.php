<?php

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * Description of User
 *
 * @author Martin
 * @ODM\Document
 * 
 */
class Admin implements Nette\Security\IIdentity
{

	/**
	 * @ODM\Id 
	 */
	private $id;

	/**
	 * @ODM\String
	 * @ODM\Index(unique=true) 
	 */
	private $email;

	/**
	 * @ODM\String 
	 */
	private $password;


	public function __construct()
	{
		$this->apps = new \Doctrine\Common\Collections\ArrayCollection;
	}


	public function getId()
	{
		return $this->id;
	}


	public function getRoles()
	{
		return ['admin'];
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


}

