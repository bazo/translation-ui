<?php

use Nette\Security as NS;
use Doctrine\ODM\MongoDB\DocumentRepository;

/**
 * Users authenticator.
 *
 * @author     Martin Bazik
 */
class AdminAuthenticator extends Nette\Object implements NS\IAuthenticator
{

	/** @var DocumentRepository */
	private $adminRepository;


	public function __construct(DocumentRepository $adminRepository)
	{
		$this->adminRepository = $adminRepository;
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
		
		$user = $this->adminRepository->findOneBy(['email' => $email]);
		if (!$user or !$this->passwordHasher->checkPassword($password, $user->getPassword())) {
			throw new NS\AuthenticationException("Invalid credentials", self::FAILURE);
		}

		return $user;
	}


}

