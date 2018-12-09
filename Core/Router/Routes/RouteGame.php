<?php
namespace Core\Router\Routes;

use Core\ApplicationType;
use Core\Config;

class RouteGame extends Route {

	public function __construct($mask,$presenter,$name = null,$redirect = null){
		parent::__construct($mask,ApplicationType::GAME,$presenter,$name,$redirect);
	}

	public function getBaseUrl():string {
		return Config::get("environment")['domain'];
	}
}