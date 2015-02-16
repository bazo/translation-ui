<?php

namespace App;

use Nette\Application\Routers\RouteList,
	Nette\Application\Routers\Route;



/**
 * Router factory.
 */
class RouterFactory
{

	/**
	 * @return \Nette\Application\IRouter
	 */
	public function createRouter()
	{
		$router = new RouteList();
		$router[] = new Route('<presenter>/<action>[/<id>]', 'Front:Dashboard:Default');
		return $router;
	}


}