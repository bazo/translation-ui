<?php
use Nette\Security as NS;
/**
 * Users authenticator.
 *
 * @author     Martin Bazik
 */
class AdminAuthenticator extends Nette\Object implements NS\IAuthenticator
{
	private 
		/** @var Doctrine\ODM\MongoDB\DocumentRepository */	
		$adminRepository,
			
		/** @var \Security\PasswordHasher */	
		$passwordHasher
	;

	public function __construct(Doctrine\ODM\MongoDB\DocumentRepository $adminRepository, \Phpass\Hash $passwordHasher)
	{
		$this->adminRepository = $adminRepository;
		$this->passwordHasher = $passwordHasher;
	}

	/**
	 * Performs an authentication
	 * @param  array
	 * @return Nette\Security\Identity
	 * @throws Nette\Security\AuthenticationException
	 */
	public function authenticate(array $credentials)
	{
		list($email, $password) = $credentials;
		$user = $this->adminRepository->findOneBy(array('email' => $email));
		if (!$user or !$this->passwordHasher->checkPassword($password, $user->getPassword())) 
		{
			throw new NS\AuthenticationException("Invalid credentials", self::FAILURE);
		}

		return $user;
	}
}
