<?php
namespace Core\Router\Routes;

class NullRoute extends Route {

	public function __construct(){
		parent::__construct(null,null,null);
	}

	public function getBaseUrl():string {
		return null;
	}
}