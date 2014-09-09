<?php

namespace FrontModule;

use Nette\Application\UI\Form;



/**
 * Homepage presenter.
 *
 * @author     John Doe
 * @package    MyApplication
 */
class RegisterPresenter extends BasePresenter
{


	public function renderDefault()
	{
		$this->template->anyVariable = 'any value';
	}


	protected function createComponentFormRegister()
	{
		$form = new Form;
		$form->addText('nick', 'Nick')->setRequired()->addRule(Form::MIN_LENGTH, 'Nick must be at least %d characters long', 4);
		$form->addText('email', 'Email')->setRequired()->addRule(Form::EMAIL);
		$form->addPassword('password', 'Password')->setRequired();
		$form->addCheckbox('show_password', 'Show password');
		$form->addSubmit('btnSubmit', 'Register');
		$form->onSuccess[] = callback($this, 'formRegisterSubmitted');
		return $form;
	}


	public function formRegisterSubmitted(Form $form)
	{
		$values = $form->getValues();

		try {
			$this->userFacade->setPresenter($this);
			$this->userFacade->createUser($values->nick, $values->email, $values->password);
			$this->flash('You have been successfully registered. A confirmation email to activate your account has been sent to you.');
		} catch (\ExistingUserException $e) {
			$this->flash($e->getMessage(), 'error');
		}

		$this->redirect('this');
	}


	public function actionConfirmation($token)
	{
		if ($token === null) {
			$this->redirect('step1');
		} else {
			$this->template->tokenUsed = false;
			$tokenDoc = $this->context->documentManager->getRepository('RegistrationToken')->findOneBy(array(
				'token' => $token));
			if ($tokenDoc === null or $tokenDoc->isUsed()) {
				$this->template->tokenUsed = TRUE;
			} else {
				$user = $tokenDoc->getUser();

				$user->setActive(TRUE);

				$tokenDoc->setUsed(TRUE);

				$this->context->documentManager->persist($user);
				$this->context->documentManager->persist($tokenDoc);

				$this->context->documentManager->flush();
			}
		}
	}


	public function renderConfirmation($token)
	{

	}


}