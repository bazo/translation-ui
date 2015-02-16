<?php

namespace FrontModule;

use Nette\Application\UI,
	Nette\Security as NS;

/**
 */
class SignPresenter extends BasePresenter
{

	/**
	 * Sign in form component factory.
	 * @return Nette\Application\UI\Form
	 */
	protected function createComponentSignInForm()
	{
		$form = new UI\Form;
		$form->addText('email', 'Email:')
				->setRequired('Please provide an email.');

		$form->addPassword('password', 'Password:')
				->setRequired('Please provide a password.');

		$form->addSubmit('btnSubmit', 'Sign in');

		$form->onSuccess[] = callback($this, 'signInFormSubmitted');
		return $form;
	}


	public function signInFormSubmitted($form)
	{
		try {
			$values = $form->getValues();
				$this->user->setExpiration('+ 365 days', FALSE);
			$this->user->onLoggedIn[] = callback($this, 'userLoggedIn');
			$this->user->login($values->email, $values->password);
			//$this->redirect('Homepage:');
		} catch (NS\AuthenticationException $e) {
			$this->flash($e->getMessage(), 'error');
		}
	}


	public function userLoggedIn(\Nette\Security\User $user)
	{
		$this->redirect('Dashboard:Default');
	}


}

