<?php
namespace Core\Router;

use Core\Router\Routes\RouteGame;

class RoutesFactory {

	public static function createRoutes(){
		/* GAME ------------------------------------------------------------- */
		Router::add(new RouteGame("","Index"));
		Router::add(new RouteGame("api/[i:id]/user","Api/User"));
		Router::add(new RouteGame("api/[i:id]/room","Api/Room"));
		Router::add(new RouteGame("test","Test"));
		Router::add(new RouteGame("robots.txt","Robots"));
	}
}