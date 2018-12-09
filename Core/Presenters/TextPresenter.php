<?php
namespace Core\Presenters;

use Core\Responses\IResponse;
use Core\Responses\TextResponse;

abstract class TextPresenter extends Presenter {

	private $content;

	public function setContent($content){
		$this->content = $content;
	}

	public function run():IResponse {
		ob_start();
		$this->render();
		$content = ob_get_clean();
		if(!empty($content)) $this->content = $content;
		return new TextResponse($this->content);
	}
}