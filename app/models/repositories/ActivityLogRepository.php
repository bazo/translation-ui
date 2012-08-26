<?php
namespace Repositories;
use Doctrine\ODM\MongoDB\DocumentRepository;

/**
 * Description of DocumentRepository
 *
 * @author martin.bazik
 */
class ActivityLogRepository extends DocumentRepository
{
	public function getUserLogs(\User $user, $limit = null)
	{
		$accesses = $user->getAccesses();
		$projectIds = array();
		foreach($accesses as $access)
		{
			$projectIds[] = $access->getProject()->getId();
		}
		
		$qb = $this->createQueryBuilder()
				->field('project.id')->in($projectIds)
				->sort('added', 'desc');
		
		if($limit !== null)
		{
			$qb->limit($limit);
		}
		
		return $qb->getQuery()->execute();
	}
}