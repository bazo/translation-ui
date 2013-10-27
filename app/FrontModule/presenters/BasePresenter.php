<?php

namespace FrontModule;

/**
 * Base class for all application presenters.
 *
 * @author     John Doe
 * @package    MyApplication
 */
abstract class BasePresenter extends \Base\BasePresenter
{

	/** @var User */
	protected $me;


	protected function startup()
	{
		parent::startup();
		$this->user->getStorage()->setNamespace('user');
		if ($this->user->isLoggedIn()) {
			$this->me = $this->context->userFacade->find($this->user->getId());
		}
	}


	protected function beforeRender()
	{
		parent::beforeRender();
		$this->template->me = $this->me;
	}


}

