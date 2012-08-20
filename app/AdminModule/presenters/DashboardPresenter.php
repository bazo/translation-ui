<?php
namespace AdminModule;
/**
 * Homepage presenter.
 *
 * @author     John Doe
 * @package    MyApplication
 */
class DashboardPresenter extends SecuredPresenter
{

	public function renderDefault()
	{
		$this->template->usersCount = $this->context->userFacade->getCount();
		$this->template->projectsCount = $this->context->projectFacade->getCount();
		$this->template->translationsCount = $this->context->translationFacade->getCount();
		$this->template->messagesCount = $this->context->messageFacade->getCount();
	}

}
