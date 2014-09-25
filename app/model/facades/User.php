<?php

namespace Facades;

use Doctrine\ODM\MongoDB\DocumentManager;



class User extends Base
{

	private $presenter;
	protected $documentClass = 'User';

	public function __construct(DocumentManager $dm)
	{
		parent::__construct($dm);
	}


	public function setPresenter($presenter)
	{
		$this->presenter = $presenter;
	}


	public function createUser($nick, $email, $password)
	{
		$user = new \User;

		$hash = password_hash($password, PASSWORD_BCRYPT);

		$user->setNick($nick)->setEmail($email)->setPassword($hash);
		$this->dm->persist($user);

		$token = new \RegistrationToken(sha1($user->getEmail() . time()));
		$token->setUser($user);
		$this->dm->persist($token);

		try {
			$this->dm->flush($user, ['safe' => TRUE]);
		} catch (\MongoCursorException $e) {
			if (strpos($e->getMessage(), $nick) !== false) {
				throw new \ExistingUserException(sprintf('User with nick %s already exists ', $nick));
			} elseif (strpos($e->getMessage(), $email) !== false) {
				throw new \ExistingUserException(sprintf('User with email %s already exists ', $email));
			}
		}
	}


	public function search($query, $excludeIds = null)
	{
		$query = \Nette\Utils\Strings::toAscii($query);
		$regex = new \MongoRegex('/.*' . $query . '.*/i');

		$qb = $this->dm->getRepository('User')->createQueryBuilder()
						->field('nick')->equals($regex);
		//->field('email')->equals($regex);
		//$qb->addOr($qb->field('nick')->equals($regex));
		$qb->addOr($qb->expr()->field('email')->equals($regex));

		if ($excludeIds !== null) {
			$qb->field('id')->notIn($excludeIds);
		}

		$qb->sort('nick', 'desc');

		return $qb->getQuery()->execute();
	}


}