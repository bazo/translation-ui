<?php

use Nette\Security as NS;

/**
 * Users authenticator.
 *
 * @author     Martin Bazik
 */
class Authenticator extends Nette\Object implements NS\IAuthenticator
{

	/** @var Doctrine\ODM\MongoDB\DocumentRepository */
	private $usersRepository;


	public function __construct(Doctrine\ODM\MongoDB\DocumentRepository $usersRepository)
	{
		$this->usersRepository = $usersRepository;
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
		if (!$user or !password_verify($password, $user->getPassword())) {
			throw new NS\AuthenticationException("Invalid credentials", self::FAILURE);
		} elseif (!$user->isActive()) {
			throw new NS\AuthenticationException("Your account hasn't been activated yet.", self::FAILURE);
		}

		$user->setAccountRule(AccountRules::getRuleForAccount($user->getAccount()));

		return $user;
	}


}

