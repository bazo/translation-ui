<?php

/**
 * ContainerHelper
 *
 * @author martin.bazik
 */
class ContainerHelper extends Symfony\Component\Console\Helper\Helper
{
	/**
	 * @var SystemContainer 
	 */
	private $container;


	public function __construct(SystemContainer $container)
	{
		$this->container = $container;
	}
	
	/**
	 * @return SystemContainer 
	 */
	public function getContainer()
	{
		return $this->container;
	}
	
	public function getName()
	{
		return 'containerHelper';
	}
}