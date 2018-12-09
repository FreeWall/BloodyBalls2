<?php
namespace Core\Presenters;

use Core\Application;
use Core\Request;
use Core\Responses\IResponse;
use Core\Responses\Response;
use Core\Responses\VoidResponse;
use Core\Templates\ITemplate;
use Core\Templates\Template;

abstract class Presenter implements IPresenter {

	private $request;
	private $response;
	private $template;

	public function __construct(Request $request){
		$this->request = $request;
	}

	public function getName(){
		$name = explode("Presenters\\",get_class($this))[1];
		return $name;
	}

	public function getRequest():Request {
		return $this->request;
	}

	public function getResponse():IResponse {
		return $this->response;
	}

	public function setResponse(IResponse $response){
		$this->response = $response;
	}

	public function getHttpResponse():\Core\Http\Response {
		return Application::getHttpResponse();
	}

	public function getSession():\Core\Http\Session {
		return Application::getSession();
	}

	public function getTemplate():ITemplate {
		return $this->template;
	}

	public function setBaseTemplate(ITemplate $template){
		$this->template = $template;
	}

	public function setDefaultTemplate(){
		$this->template = new Template($this->request->getPresenter().".latte");
	}

	public function setTemplate(string $template){
		$this->template = new Template($template.".latte");
	}

	public function setActionTemplate(string $action){
		$this->template = new Template($this->request->getPresenter()."[".$action."].latte");
	}

	public function getAction():?string {
		return $this->request->getHttpRequest()->getPost("action");
	}

	public function run():IResponse {
		$this->startup();
		$this->beforeRender();
		$this->render();
		if($this->template == null && !$this->response){
			$this->response = new VoidResponse();
		}
		if(!$this->response) $this->response = new Response($this->template);
		return $this->response;
	}

	public function redirect($url){
		$this->getHttpResponse()->redirect($url);
	}

	public function redirectSelf(string $args = null){
		$this->getHttpResponse()->redirect($this->getRequest()->getRoute()->generate($this->getRequest()->getParams()).$args);
	}

	protected function startup(){
	}

	protected function beforeRender(){
	}

	abstract public function render();
}