<?php
namespace Facades;
use Doctrine\ODM\MongoDB\DocumentManager;
class User extends Base
{
	private
			
		/** @var PasswordHasher */	
		$passwordHasher,
			
		$presenter
	;
	
	protected
		$documentClass = 'User'
	;
	
	public function __construct(DocumentManager $dm, \Phpass\Hash $passwordHasher)
	{
		parent::__construct($dm);
		$this->passwordHasher = $passwordHasher;
	}
	
	public function setPresenter($presenter)
	{
		$this->presenter = $presenter;
	}
	
	public function createUser($email, $password)
	{
		$user = new \User;
		
		$hash = $this->passwordHasher->hashPassword($password);
		
		$user->setEmail($email)->setPassword($hash);
		$this->dm->persist($user);
		
		$token = new \RegistrationToken(sha1($user->getEmail().time()));
		$token->setUser($user);
		$this->dm->persist($token);
		
		
		try
		{
			$this->dm->flush($user, array('safe' => true));
			
			$mailBuilder = new \Jobzine\Services\MailBuilder($this->presenter);
			$mailBuilder->buildRegistrationEmail($user, $token)->send();
		}
		catch(\MongoCursorException $e)
		{
			throw new \ExistingUserException(sprintf('User with email %s already exists ', $email));
		}
	}
}
