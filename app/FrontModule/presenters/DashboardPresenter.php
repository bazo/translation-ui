<?php

namespace FrontModule;

use Nette\Application\UI\Form;

/**
 * Homepage presenter.
 *
 * @author     John Doe
 * @package    MyApplication
 */
class DashboardPresenter extends SecuredPresenter
{

	/** @persistent */
	public $filters;

	/** @persistent */
	public $levels;


	protected function startup()
	{
		parent::startup();
	}


	public function renderDefault()
	{
		$logs = $this->context->getByType(\Doctrine\ODM\MongoDB\DocumentManager::class)->getRepository('ActivityLog')->getUserLogs($this->me, 20);
		$this->template->logs = $logs;
	}


}

