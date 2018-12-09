<?php
namespace Apps\Game\Presenters;

use Core\Application;
use Core\Presenters\Presenter;
use Core\Templates\BaseTemplate;

class ErrorPresenter extends Presenter {

	public function render(){
		$this->setBaseTemplate(new BaseTemplate("error.latte"));
		if(isset($this->getRequest()->getParams()['code'])){
			$this->getHttpResponse()->setCode($this->getRequest()->getParams()['code']);
			$this->getTemplate()->addParams(["code" => $this->getRequest()->getParams()['code']]);
		}
		$this->getTemplate()->addParams(Application::self()->getGlobalParams());
	}
}