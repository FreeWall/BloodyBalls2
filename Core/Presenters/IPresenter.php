<?php
namespace Core\Presenters;

use Core\Responses\IResponse;

interface IPresenter {

	public function run():IResponse;
}