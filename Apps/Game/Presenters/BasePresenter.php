<?php
namespace Apps\Game\Presenters;

use Core\Presenters\Presenter;

abstract class BasePresenter extends Presenter {

	public function startup(){
		$this->setDefaultTemplate();
	}
}