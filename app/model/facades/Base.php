<?php

namespace Facades;

use Doctrine\ODM\MongoDB\DocumentManager;



abstract class Base
{

	/** @var DocumentManager */
	protected $dm;
	protected $documentClass;

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
	 * @return DocumentManager
	 */
	public function getDm()
	{
		return $this->dm;
	}


}