<?php

use FrontModule\SecuredPresenter;

namespace FrontModule;



/**
 * Homepage presenter.
 *
 * @author     John Doe
 * @package    MyProjectlication
 */
class ProjectsPresenter extends SecuredPresenter
{

	public function renderProject($id)
	{
		$this->template->app = $this->projectFacade->find($id);
	}


	public function handleDelete($id)
	{
		$this->context->projectFacade->delete($id);
		$this->flash('Project sucessfully removed', 'success');
		$this->redirect('apps:');
	}


}