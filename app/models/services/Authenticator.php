<?php
use Nette\Security as NS;
/**
 * Users authenticator.
 *
 * @author     Martin Bazik
 */
class Authenticator extends Nette\Object implements NS\IAuthenticator
{
	private 
		/** @var Doctrine\ODM\MongoDB\DocumentRepository */	
		$usersRepository,
			
		/** @var \Security\PasswordHasher */	
		$passwordHasher
	;

	public function __construct(Doctrine\ODM\MongoDB\DocumentRepository $usersRepository, Phpass\Hash $passwordHasher)
	{
		$this->usersRepository = $usersRepository;
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
		$user = $this->usersRepository->findOneBy(array('email' => $email));
		if (!$user or !$this->passwordHasher->checkPassword($password, $user->getPassword())) 
		{
			throw new NS\AuthenticationException("Invalid credentials", self::FAILURE);
		}
		elseif(!$user->isActive())
		{
			throw new NS\AuthenticationException("Your account hasn't been activated yet.", self::FAILURE);
		}

		return $user;
	}
}
