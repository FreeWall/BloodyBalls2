<?php
namespace Apps\Game\Presenters;

class NotFoundPresenter extends BasePresenter {

	public function render(){
		$this->getHttpResponse()->setCode($this->getRequest()->getParams()['code']);
	}
}