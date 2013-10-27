<?php

namespace Base;

/**
 * Base class for all application presenters.
 *
 * @author     John Doe
 * @package    MyApplication
 */
abstract class BasePresenter extends \Nette\Application\UI\Presenter
{

	public function flash($message, $type = 'success')
	{
		$this->flashMessage($message, $type);
		$this->invalidateControl('flashes');
	}


}

