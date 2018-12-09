<?php
namespace Core;

use Core\Router\Routes\Route;

class Request {

	private $httpRequest;
	private $route;
	private $presenter;
	private $params;

	public function __construct(Http\Request $httpRequest,?Route $route,string $presenter,array $params = []){
		$this->httpRequest = $httpRequest;
		$this->route = $route;
		$this->presenter = $presenter;
		$this->params = $params;
	}

	public function getHttpRequest():Http\Request {
		return $this->httpRequest;
	}

	public function getRoute():Route {
		return $this->route;
	}

	public function getPresenter():string {
		return $this->presenter;
	}

	public function getParams():array {
		return $this->params;
	}

	public function getParam(string $key){
		return $this->params[$key] ?? null;
	}

	public function addParams(array $params){
		$this->params = array_merge($this->params,$params);
	}
}