<?php

namespace FrontModule;



abstract class BasePresenter extends \Base\BasePresenter
{

	/** @var User */
	protected $me;

	/** @var \Facades\User @inject */
	public $userFacade;

	protected function startup()
	{
		parent::startup();
		$this->user->getStorage()->setNamespace('user');
		if ($this->user->isLoggedIn()) {
			$this->me = $this->userFacade->find($this->user->getId());
		}
	}


	protected function beforeRender()
	{
		parent::beforeRender();
		$this->template->me = $this->me;
	}


}