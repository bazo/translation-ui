<?php

namespace Services;



/**
 * Authorizator
 *
 * @author Martin
 */
class Authorizator
{

	/** @var \Doctrine\ODM\MongoDB\DocumentManager */
	private $dm;

	public function __construct(\Doctrine\ODM\MongoDB\DocumentManager $dm)
	{
		$this->dm = $dm;
	}


	public function isAllowed(\User $user, \Project $project, $privilege)
	{
		return TRUE;
	}


}