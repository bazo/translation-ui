<?php
namespace Facades;
use Doctrine\ODM\MongoDB\DocumentManager;
use Nette\Utils\Strings;

abstract class Base
{
	
	protected
		/** @var DocumentManager */	
		$dm,
		$documentClass	
	;
	
	function __construct($dm)
	{
		$this->dm = $dm;
	}
	
	public function getCount()
	{
		return $this->dm->getRepository($this->documentClass)->createQueryBuilder()->getQuery()->execute()->count(true);
	}
	
	public function find($id)
	{
		return $this->dm->getRepository($this->documentClass)->find($id);
	}
	
	public function findOneBy(array $criteria)
	{
		return $this->dm->getRepository($this->documentClass)->findOneBy($criteria);
	}

	/**
	 * @return \Doctrine\ODM\MongoDB\DocumentManager
	 */
	public function getDm()
	{
		return $this->dm;
	}

}