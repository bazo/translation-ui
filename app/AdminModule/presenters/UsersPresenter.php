<?php
namespace AdminModule;
/**
 * Homepage presenter.
 *
 * @author     John Doe
 * @package    MyApplication
 */
class UsersPresenter extends SecuredPresenter
{

	protected function createComponentGrid()
	{
		$dg = new \Gridder\Gridder;
		$dg->setClass('users');
		$repository = $this->context->documentManager->getRepository('User');
		$source = new \Gridder\Sources\MongoRepositorySource($repository);
		//$source->prime('areaOfExpertise');
		$storageSection = $this->getSession()->getSection('gridder_users'.$this->user->id);
		$persister = new \Gridder\Persisters\SessionPersister($storageSection);

		$dg->setSource($source);
		$dg->setPersister($persister);
		$dg->setPresenter($this);

		$dg->setInitialItemsPerPage(10);

		$dg->setPrimaryKey('id');

		//$dg->addColumn('id')->setCaption('id');
		$dg->addColumn('nick')->setSortable(true)->setFilter('text');
		$dg->addColumn('email')->setSortable(true)->setFilter('text');
		
		$dg->addColumn('projectCount')->setCaption('project count')->setSortable(true);
		
		$dg->addColumn('account')->setSortable(true)->setFilter('text');

		$dg->addColumn('active')->setCaption('active')->setSortable(true)->valueModifier[] = function($boolean){
			return $boolean === true ? 'yes' : 'no';
		};

		$ac = $dg->addActionColumn('akcie');

		$ac->addAction('detail', 'detail:')->setTitle('detail');

		return $dg;
	}
	
	public function renderDefault()
	{
	}

}
