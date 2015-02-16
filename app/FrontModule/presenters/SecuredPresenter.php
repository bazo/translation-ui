<?php

namespace FrontModule;

use Activity;
use AllowedLangs;
use ExistingProjectException;
use Facades\Project;
use Nette\Application\UI\Form;
use Project as Project2;
use Services\ActivityLogger;
use Services\Authorizator;
use Symfony\Component\Intl\Intl;



/**
 * Base class for all application presenters.
 *
 * @author     John Doe
 * @package    MyApplication
 */
abstract class SecuredPresenter extends BasePresenter
{

	protected $subTabs = [];

	/** @var Authorizator @inject */
	public $acl;

	/** @var Project @inject */
	public $projectFacade;

	/** @var ActivityLogger @inject */
	public $activityLogger;

	protected function startup()
	{
		parent::startup();
		if (!$this->user->isLoggedIn() or ! $this->user->isInRole('user')) {
			$this->redirect('Sign:In');
		}
	}


	protected function beforeRender()
	{
		parent::beforeRender();
		$this->template->parameters = $this->context->parameters;
		$this->template->acl = $this->acl;

		$this->template->addFilter('langName', function($locale) {
			return Intl::getLocaleBundle()->getLocaleName($locale);
		});

		$this->template->projects = $this->projectFacade->findAll();
	}


	public function handleLogout()
	{
		$this->user->logout(TRUE);
		$this->redirect('Sign:In');
	}


	protected function createComponentFormNewProject()
	{
		$form = new Form;

		$langs = AllowedLangs::getLangs();

		$form->addText('caption', 'Name')->setRequired();
		$form->addSelect('sourceLang', 'Source language', $langs)->setRequired();
		$form->addText('link', 'Link to web');
		$form->addSubmit('btnSubmit', 'Create');
		$form->onSuccess[] = callback($this, 'formNewProjectSubmitted');
		return $form;
	}


	public function formNewProjectSubmitted(Form $form)
	{
		$values = $form->getValues();
		if ($this->user->identity->canAddProject()) {
			try {
				$project = $this->projectFacade->createProject($values, $this->me);
				$this->log($project, Activity::CREATE_PROJECT);
				$this->redirect('Project:', array('id' => $project->getId()));
			} catch (ExistingProjectException $e) {
				$this->flash($e->getMessage(), 'error');
			}
		} else {
			$this->flash('You cannot add more projects.', 'error');
			$this->redirect('this');
		}
	}


	protected function log(Project2 $project, $activity, $object = null)
	{
		$this->activityLogger->log($this->me, $project, $activity, $object);
	}


}