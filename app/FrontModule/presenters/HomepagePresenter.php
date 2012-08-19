<?php
namespace FrontModule;
/**
 * Homepage presenter.
 *
 * @author     John Doe
 * @package    MyApplication
 */
class HomepagePresenter extends BasePresenter
{

	protected function startup()
	{
		parent::startup();
		if($this->user->isLoggedIn())
		{
			$this->redirect('dashboard:');
		}
	}

}
