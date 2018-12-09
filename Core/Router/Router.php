<?php
namespace Core\Router;

use Core\Application;
use Core\Http\Request;
use Core\Router\Routes\Route;
use Tracy\Debugger;

class Router {

	/** @var Route[] */
	private static $routes = [];

	public static function getRoutes():array {
		return self::$routes;
	}

	public static function add(Route $route){
		if(isset(self::$routes[$route->getFullName()])) throw new \RuntimeException("Route '".$route->getFullName()."' already exists.");
		self::$routes[$route->getFullName()] = $route;
		if(!Debugger::$productionMode){
			$bt = debug_backtrace();
			$caller = array_shift($bt);
			$route->setSource(["file" => $caller['file'],"line" => $caller['line']]);
		}
		return $route;
	}

	public static function get($name):Route {
		if(strstr($name,":") === false) $name = Application::getType().":".$name;
		if(!isset(self::$routes[$name])) throw new \RuntimeException("Route '".$name."' not found.");
		return self::$routes[$name];
	}

	public static function match(Request $request){
		foreach(self::$routes AS $route){
			if($route->getType() != Application::getType()) continue;
			$appRequest = $route->match($request);
			if($appRequest != null) return $appRequest;
		}
		return null;
	}
}