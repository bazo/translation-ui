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

	public 
		/** @persistent */	
		$filters,
			
		/** @persistent */	
		$levels
	;

	protected function startup()
	{
		parent::startup();
	}
	
	public function renderDefault()
	{
		/*
		$qb = $this->context->documentManager->getRepository('Log')->createQueryBuilder();
		
		$appNames = array_keys(array_filter($filters));
		
		$logLevels = array_keys(array_filter($levels));
		
		$indexes = array_merge($appNames, $logLevels);
		$indexes[] = $this->user->getId();
		$qb->field('level')->in($logLevels)->field('appName')->in($appNames)->field('user')->references($this->me);
		
		$logs = $qb->sort('added', 'desc')->limit(20)->getQuery()->execute();
		
		$this->template->logs = $logs;
		 * 
		 */
	}

}
