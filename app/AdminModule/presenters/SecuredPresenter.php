<?php
namespace AdminModule;

/**
 * Base class for all application presenters.
 *
 * @author     John Doe
 * @package    MyApplication
 */
abstract class SecuredPresenter extends BasePresenter
{

	protected
		$subTabs = array(
		)
	;		
	
	protected function startup()
	{
		parent::startup();
		if(!$this->user->isLoggedIn() or !$this->user->isInRole('admin'))
		{
			$this->redirect('sign:in');
		}
	}


	protected function beforeRender()
	{
		parent::beforeRender();
		$this->template->parameters = $this->context->parameters;
	}
	
	public function handleLogout()
	{
		$this->user->logout(true);
		$this->redirect('sign:in');
	}

}
