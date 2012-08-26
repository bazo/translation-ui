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
		
		$parameters = $this->getParameter();
		
		try
		{
			$this->context->projectFacade->authenticateProject($parameters['id'], $parameters['key']);
		}
		catch(\Exception $e)
		{
			$this->sendResponse(new \Nette\Application\Responses\JsonResponse(array('error' => 'true', 'message' => $e->getMessage())));
		}
	}
}
