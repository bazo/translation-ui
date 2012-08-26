<?php
namespace Services;
/**
 * Authorizator
 *
 * @author Martin
 */
class Authorizator
{
	private
		/** @var \Doctrine\ODM\MongoDB\DocumentManager */	
		$dm
	;

	public function __construct(\Doctrine\ODM\MongoDB\DocumentManager $dm)
	{
		$this->dm = $dm;
	}

	public function isAllowed(\User $user, \Project $project, $privilege)
	{
		$this->dm->refresh($user);

		$access = $this->dm->getRepository('ProjectAccess')->getAccessForUserAndProject($user, $project);

		return \Access::assert($access->getLevel(), $privilege);
	}
}
