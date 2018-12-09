<?php
namespace Core\Responses;

use Core\Templates\ITemplate;

class Response implements IResponse {

	private $template;

	public function __construct(ITemplate $template){
		$this->template = $template;
	}

	public function send(\Core\Http\Response $response){
		$this->template->render();
	}
}