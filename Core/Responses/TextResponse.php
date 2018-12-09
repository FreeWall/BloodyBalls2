<?php
namespace Core\Responses;

class TextResponse implements IResponse {

	private $content;

	public function __construct($content){
		$this->content = $content;
	}

	public function send(\Core\Http\Response $response){
		echo $this->content;
	}
}