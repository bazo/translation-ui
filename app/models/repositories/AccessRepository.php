<?php

namespace Repositories;

use Doctrine\ODM\MongoDB\DocumentRepository;

/**
 * Description of DocumentRepository
 *
 * @author martin.bazik
 */
class AccessRepository extends DocumentRepository
{

	public function getAccessForUserAndProject(\User $user, \Project $project)
	{
		return $this->createQueryBuilder()
						->field('user.id')->equals($user->getId())
						->field('project.id')->equals($project->getId())
						->getQuery()->getSingleResult();
	}


	public function getAccesses(\User $user)
	{
		return $this->createQueryBuilder()
						->field('user.id')->equals($user->getId())
						->getQuery()->execute();
	}


	public function getAccessesProject(\Project $project)
	{
		return $this->createQueryBuilder()
						->field('project.id')->equals($project->getId())
						->getQuery()->execute();
	}


}

