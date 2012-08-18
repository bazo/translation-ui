<?php
namespace FrontModule;
use Nette\Application\UI,
	Nette\Security as NS;


/**
 * Sign in/out presenters.
 *
 * @author     John Doe
 * @package    MyApplication
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

		$form->addCheckbox('remember', 'Remember me on this computer');

		$form->addSubmit('btnSubmit', 'Sign in');

		$form->onSuccess[] = callback($this, 'signInFormSubmitted');
		return $form;
	}



	public function signInFormSubmitted($form)
	{
		try {
			$values = $form->getValues();
			if ($values->remember) {
				$this->getUser()->setExpiration('+ 14 days', FALSE);
			} else {
				$this->getUser()->setExpiration('+ 20 minutes', TRUE);
			}
			$this->getUser()->onLoggedIn[] = callback($this, 'userLoggedIn');
			$this->getUser()->setAuthenticator($this->getService('authenticator'));
			$this->getUser()->login($values->email, $values->password);
			//$this->redirect('Homepage:');

		} catch (NS\AuthenticationException $e) {
			$this->flash($e->getMessage(), 'error');
		}
	}
	
	public function userLoggedIn(\Nette\Security\User $user)
	{
		$this->redirect('dashboard:default');
	}
}
