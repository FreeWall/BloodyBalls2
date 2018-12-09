<?php
namespace Core\Router;

use Core\Router\Routes\RouteGame;

class RoutesFactory {

	public static function createRoutes(){
		/* GAME ------------------------------------------------------------- */
		Router::add(new RouteGame("","index"));
		Router::add(new RouteGame("api/[i:id]/user","api/user"));
		Router::add(new RouteGame("api/[i:id]/room","api/room"));
		Router::add(new RouteGame("test","test"));
		Router::add(new RouteGame("robots.txt","robots"));
	}
}