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
		$qb = $this->createQueryBuilder()
				->sort('added', 'desc');

		if ($limit !== null) {
			$qb->limit($limit);
		}

		return $qb->getQuery()->execute();
	}


}