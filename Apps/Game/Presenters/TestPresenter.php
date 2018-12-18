<?php
namespace Apps\Game\Presenters;

class TestPresenter extends BasePresenter {

	public function render(){
		$id = 123456789;
		echo "/$id/".substr(md5($id),0,8);
	}
}