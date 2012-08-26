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
		$logs = $this->context->documentManager->getRepository('ActivityLog')->getUserLogs($this->me, 20);
		$this->template->logs = $logs;
	}

}
