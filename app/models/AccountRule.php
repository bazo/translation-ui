<?php

/**
 * AccountRule
 *
 * @author Martin
 */
class AccountRule
{
	private
		$maximumProjects,
		$maximumTranslationsPerProject,
		$monthlyPrice
	;
	
	function __construct($rules)
	{
		$this->maximumProjects = $rules['maximumProjects'];
		$this->maximumTranslationsPerProject = $rules['maximumTranslationsPerProject'];
		$this->monthlyPrice = $rules['monthlyPrice'];
	}
	
	public function getMaximumProjects()
	{
		if($this->maximumProjects === -1)
		{
			return '&infin;';
		}
		return $this->maximumProjects;
	}

	public function getMaximumTranslationsPerProject()
	{
		return $this->maximumTranslationsPerProject;
	}

	public function getMonthlyPrice()
	{
		return $this->monthlyPrice;
	}
	
	public function __set($name, $value)
	{
		throw new ErrorException('Can\'t change account rule.');
	}
	
	public function canAddProject(\User $user)
	{
		if($this->maximumProjects === -1)
		{
			return true;
		}
		return count($user->getProjectNames()) < $this->maximumProjects;
	}
	
	public function canAddTranslation(\User $user, \Project $project)
	{	
		if($this->maximumTranslationsPerProject === -1)
		{
			return true;
		}
		return true;
	}

}
