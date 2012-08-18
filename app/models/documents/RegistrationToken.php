<?php
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
/**
 * RegistrationToken
 *
 * @author martin.bazik
 * @ODM\Document
 */
class RegistrationToken
{

	private
			/**
			 * @ODM\Id
			 */
			$id,
			/**
			 * @ODM\String
			 */
			$token,
			/**
			 * @var User
			 * @ODM\ReferenceOne(targetDocument="User")
			 */
			$user,
			/**
			 * @ODM\Boolean
			 */
			$used

	;

	public function __construct($token)
	{
		$this->token = $token;
		$this->used = false;
	}

	public function getId()
	{
		return $this->id;
	}

	public function getToken()
	{
		return $this->token;
	}

	public function setToken($token)
	{
		$this->token = $token;
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

	public function isUsed()
	{
		return $this->used;
	}

	public function setUsed($used)
	{
		$this->used = $used;
		return $this;
	}

}