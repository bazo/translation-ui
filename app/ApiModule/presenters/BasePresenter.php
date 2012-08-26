<?php
namespace ApiModule;
/**
 * Base class for all application presenters.
 *
 * @author     John Doe
 * @package    MyApplication
 */
abstract class BasePresenter extends \Base\BasePresenter
{
	protected function startup()
	{
		parent::startup();
		$this->user->getStorage()->setNamespace('api');
	}
}
