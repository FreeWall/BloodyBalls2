<?php
namespace Core\Responses;

interface IResponse {

	public function send(\Core\Http\Response $response);
}