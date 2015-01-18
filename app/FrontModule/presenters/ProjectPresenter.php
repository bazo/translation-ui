<?php

namespace FrontModule;

use Access;
use Activity;
use ExistingTranslationException;
use InvalidPluralRuleException;
use Nette\Application\Responses\JsonResponse;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\Button;
use Nette\Neon\Neon;
use Nette\Utils\TokenizerException;
use Project;
use Symfony\Component\Intl\Intl;

/**
 * Homepage presenter.
 *
 * @author     John Doe
 * @package    MyApplication
 */
class ProjectPresenter extends SecuredPresenter
{

	/** @persistent */
	public $id;

	/** @var Project */
	private $project;


	protected function startup()
	{
		parent::startup();
		$this->id = $this->getParameter('id');
		$this->project = $this->projectFacade->find($this->id);
	}


	public function beforeRender()
	{
		parent::beforeRender();
		$this->template->project = $this->project;
		$this->template->translations = $this->projectFacade->getTranslations($this->project);
	}


	protected function createComponentFormAddTranslation()
	{
		$form = new Form;

		$langs = Intl::getLocaleBundle()->getLocaleNames();

		$filtered = [];
		foreach($langs as $locale => $name) {
			try {
				$lang = substr($locale, 0, -strlen(strrchr($locale, '_')));
				\Bazo\Translation\Langs::getPluralRule($lang);
				$filtered[$locale] = $name;
			} catch (\InvalidArgumentException $ex) {
			}
		}

		$form->addSelect('lang', 'Language', $filtered);
		$form->addSubmit('btnSubmit', 'Create');

		$form->onSuccess[] = callback($this, 'formAddTranslationSubmitted');
		return $form;
	}


	public function formAddTranslationSubmitted(Form $form)
	{
		$values = $form->getValues();

		try {
			$translation = $this->projectFacade->createTranslation($this->project, $values->lang);
			$this->log($this->project, Activity::CREATE_TRANSLATION, $translation);
			$this->flash(sprintf('Translation for language %s created', Intl::getLocaleBundle()->getLocaleName($values->lang)));
		} catch (ExistingTranslationException $e) {
			$this->flash($e->getMessage(), 'error');
		} catch (InvalidPluralRuleException $e) {
			$this->flash($e->getMessage(), 'error');
		}

		$this->redirect('this');
	}


	protected function createComponentFormImportTemplate()
	{
		if ($this->acl->isAllowed($this->me, $this->project, 'importTemplate')) {
			$form = new Form;

			$form->addUpload('template', 'Template file')->setRequired();
			$form->addSubmit('btnSubmit', 'Import');

			$form->onSuccess[] = callback($this, 'formImportTemplateSubmitted');

			return $form;
		}
	}


	public function formImportTemplateSubmitted(Form $form)
	{
		$values = $form->getValues();

		if ($values->template->isOk()) {
			try {
				$neon = file_get_contents($values->template->getTemporaryFile());
				$data = Neon::decode($neon);
				$imported = $this->projectFacade->importTemplate($data, $this->project);
				$status = $imported > 0 ? 'success' : 'error';
				$this->flash(sprintf('%d messages imported.', $imported), $status);
				if ($imported > 0) {
					$this->log($this->project, Activity::IMPORT_TEMPLATE, $imported);
				}
			} catch (\Nette\Utils\NeonException $e) {
				$this->flash(sprintf('Template contains illegal characters: %s', $e->getMessage()), 'error');
			} catch (TokenizerException $e) {
				$this->flash('Uploaded file is not a valid template. Please upload a valid template.', 'error');
			}
		} else {
			$this->flash('Template file has not uploaded succesfully. Please try again.', 'error');
		}
		$this->redirect('this');
	}


	public function createComponentFormDelete()
	{
		//if ($this->acl->isAllowed($this->me, $this->project, 'danger')) {
			$form = new Form;

			$form->addSubmit('btnSubmit', 'Delete');

			$form->onSuccess[] = callback($this, 'formDeleteSubmitted');

			return $form;
		//}
	}


	public function formDeleteSubmitted(Form $form)
	{
		//if ($this->me === $this->project->getOwner()) {
			$this->projectFacade->delete($this->project);
			$this->flash(sprintf('Project %s was successfully deleted', $this->project->getCaption()));

			$this->log($this->project, Activity::DELETE_PROJECT);
		//}
		$this->redirect('projects:');
	}


	protected function createComponentFormInviteCollaborator()
	{
		$form = new Form;
		$form->addText('search', 'Search');
		$form->addHidden('id');

		$form->addSubmit('btnTranslate', 'Translate')->onClick[] = callback($this, 'formInviteCollaboratorBtnTranslateClicked');
		$form->addSubmit('btnAdmin', 'Admin')->onClick[] = callback($this, 'formInviteCollaboratorBtnAdminClicked');

		return $form;
	}


	public function handleSearch($query)
	{
		$result = [];
		$users = $this->context->userFacade->search($query, [$this->me->getId()]);

		foreach ($users as $user) {
			$result[$user->getId()] = [
				'id' => $user->getId(),
				'nick' => $user->getNick(),
				'email' => $user->getEmail(),
				'gravatar' => $user->getGravatar()
			];
		}

		$this->sendResponse(new JsonResponse($result));
	}


	public function formInviteCollaboratorBtnTranslateClicked(Button $button)
	{
		$values = $button->getForm()->getValues();
		$id = $values->id;

		$user = $this->context->userFacade->find($id);

		$level = Access::TRANSLATOR;

		$access = $this->context->projectFacade->addCollaboratorToProject($user, $this->project, $level);
		$this->log($this->project, Activity::ADD_COLLABORATOR, $access);

		$this->flash(sprintf('User <strong>%s</strong> has been added to project <strong>%s</strong> as %s', $user->getNick(), $this->project->getCaption(), $level));
		$this->redirect('this');
	}


	public function formInviteCollaboratorBtnAdminClicked(Button $button)
	{
		$values = $button->getForm()->getValues();
		$id = $values->id;

		$user = $this->context->userFacade->find($id);

		$level = Access::ADMIN;

		$access = $this->context->projectFacade->addCollaboratorToProject($user, $this->project, $level);
		$this->log($this->project, Activity::ADD_COLLABORATOR, $access);

		$this->flash(sprintf('User <strong>%s</strong> has been added to project %s as %s', $user->getNick(), $this->project->getCaption(), $level));
		$this->redirect('this');
	}


}

