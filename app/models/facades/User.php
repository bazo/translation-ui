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
	
	public function createUser($nick, $email, $password)
	{
		$user = new \User;
		
		$hash = $this->passwordHasher->hashPassword($password);
		
		$user->setNick($nick)->setEmail($email)->setPassword($hash);
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
			if(strpos($e->getMessage(), $nick) !== false)
			{
				throw new \ExistingUserException(sprintf('User with nick %s already exists ', $nick));
			}
			elseif(strpos($e->getMessage(), $email) !== false)
			{
				throw new \ExistingUserException(sprintf('User with email %s already exists ', $email));
			}
		}
	}
	
	public function search($query, $excludeIds = null)
	{
		$query = \Nette\Utils\Strings::toAscii($query);
		$regex = new \MongoRegex('/.*'.$query.'.*/i');
		
		$qb = $this->dm->getRepository('User')->createQueryBuilder()
				->field('nick')->equals($regex);
				//->field('email')->equals($regex);
		
		//$qb->addOr($qb->field('nick')->equals($regex));
		$qb->addOr($qb->expr()->field('email')->equals($regex));

		if($excludeIds !== null)
		{
			$qb->field('id')->notIn($excludeIds);
		}
		
		$qb->sort('nick', 'desc');
		
		return $qb->getQuery()->execute();
	}
}
