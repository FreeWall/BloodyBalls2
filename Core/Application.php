<?php
namespace Core;

use Core\Components\Component;
use Core\Http\RequestFactory;
use Core\Http\Session;
use Core\Presenters\Presenter;
use Core\Router\Router;
use Core\Router\Routes\NullRoute;
use Core\Router\RoutesFactory;
use Dibi\Bridges\Tracy\Panel;
use Tracy\Debugger;

abstract class Application {

	private static $instance;

	private static $type;
	private static $presenter;
	private static $httpRequest;
	private static $httpResponse;
	private static $request;
	private static $session;

	public function __construct($type){
		self::$instance = $this;
		self::$type = $type;
		try {
			Config::load();
			Debugger::enable(Config::get("environment")['production'] ? Debugger::PRODUCTION : Debugger::DEVELOPMENT);
			Debugger::$showBar = false;
			self::getSession()->start();
			RoutesFactory::createRoutes();
		} catch(\Exception $e){
			if(Debugger::isEnabled()) Debugger::exceptionHandler($e);
			$this->runErrorPresenter();
		}
	}

	/** @return Application */
	public static function self(){
		return self::$instance;
	}

	public static function getType():string {
		return self::$type;
	}

	public static function getPresenter():?Presenter {
		return self::$presenter;
	}

	public static function setPresenter(Presenter $presenter){
		self::$presenter = $presenter;
	}

	public static function getHttpRequest():Http\Request {
		if(!self::$httpRequest) self::$httpRequest = RequestFactory::createHttpRequest();
		return self::$httpRequest;
	}

	public static function getHttpResponse():Http\Response {
		if(!self::$httpResponse) self::$httpResponse = new Http\Response();
		return self::$httpResponse;
	}

	public static function getRequest():?Request {
		if(!self::$request) self::$request = Router::match(self::getHttpRequest());
		return self::$request;
	}

	public static function getSession():Session {
		if(!self::$session) self::$session = new Session(self::getHttpRequest(),self::getHttpResponse());
		return self::$session;
	}

	public static function getAppName():string {
		return self::$type;
	}

	public function run(){
		if(!self::getPresenter()){
			if(self::getRequest() == null){
				$this->runNotFoundPresenter();
			}
			if(self::getRequest()->getRoute()->getRedirect() != null){
				$response = new Http\Response();
				$response->redirect(Router::get(self::getRequest()->getRoute()->getRedirect())->generate(self::getRequest()->getParams()));
			}
			self::$presenter = self::getPresenterClass(self::getRequest());
			if(self::$presenter == null){
				$this->runNotFoundPresenter();
			}
		}
		if(self::getPresenter() == null){
			throw new \Exception("Presenter '".self::getRequest()->getPresenter()."' not found.");
		}
		$response = self::getPresenter()->run();
		if(!Debugger::$productionMode) new Panel(self::getPresenter());
		if($response != null){
			$response->send(self::getHttpResponse());
		}
		Debugger::shutdownHandler();
	}

	public function runErrorPresenter(int $code = 503){
		self::$request = new Request(self::getHttpRequest(),new NullRoute(),"Error",["code" => $code]);
		self::$presenter = self::getPresenterClass(self::$request);
		if(self::getPresenter()) self::run();
		exit();
	}

	public function runNotFoundPresenter(){
		self::$request = new Request(self::getHttpRequest(),new NullRoute(),"NotFound",["code" => 404]);
		self::$presenter = self::getPresenterClass(self::$request);
		if(self::getPresenter()) self::run();
		exit();
	}

	public static function getPresenterClass(Request $request):?Presenter {
		$name = str_replace("/","\\",$request->getPresenter());
		$className = "Apps\\".self::getAppName()."\\Presenters\\".$name."Presenter";
		if(class_exists($className)) return new $className($request);
		return null;
	}

	/** @throws \Exception */
	public static function getComponentClass($name,array $args = []):Component {
		$name = str_replace("/","\\",$name);
		$className = "Apps\\".self::getAppName()."\\Components\\".$name."Control";
		if(class_exists($className)) return new $className($args);
		else throw new \Exception("Component '".$name."' not found.");
	}

	public function getGlobalParams(){
		return [
			"version" => [
				"build" => Config::VERSION,
				"branch" => basename(BASEDIR),
				"time" => time()
			],
		];
	}
}