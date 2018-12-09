<?php
namespace Core\Responses;

class JsonResponse implements IResponse {

	private $content;
	private $contentType;

	public function __construct($content,string $contentType = null){
		$this->content = $content;
		$this->contentType = $contentType ?: 'application/json';
	}

	public function send(\Core\Http\Response $response){
		$response->setContentType($this->contentType,'utf-8');
		echo json_encode($this->content);
	}
}