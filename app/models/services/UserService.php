<?php
namespace Services;
use Doctrine\ODM\MongoDB\DocumentManager;
class UserService
{
	private
		/** @var DocumentManager */	
		$documentManager,
			
		/** @var PasswordHasher */	
		$passwordHasher,
			
		$presenter
	;
	
	public function __construct(DocumentManager $documentManager, \Phpass\Hash $passwordHasher)
	{
		$this->documentManager = $documentManager;
		$this->passwordHasher = $passwordHasher;
	}
	
	public function setPresenter($presenter)
	{
		$this->presenter = $presenter;
	}
	
	public function find($id)
	{
		return $this->documentManager->getRepository('User')->find($id);
	}
	
	public function createUser($email, $password)
	{
		$user = new \User;
		
		$hash = $this->passwordHasher->hashPassword($password);
		
		$user->setEmail($email)->setPassword($hash);
		$this->documentManager->persist($user);
		
		$token = new \RegistrationToken(sha1($user->getEmail().time()));
		$token->setUser($user);
		$this->documentManager->persist($token);
		
		
		try
		{
			$this->documentManager->flush($user, array('safe' => true));
			
			$mailBuilder = new \Jobzine\Services\MailBuilder($this->presenter);
			$mailBuilder->buildRegistrationEmail($user, $token)->send();
		}
		catch(\MongoCursorException $e)
		{
			throw new \ExistingUserException(sprintf('User with email %s already exists ', $email));
		}
	}
}
