<?php
namespace Apps\Game\Presenters\Api;

use Apps\Game\Presenters\BasePresenter;
use Core\Responses\JsonResponse;
use Models\Game\Users\UserRepository;

class UserPresenter extends BasePresenter {

	public function render(){
		if($this->getAction() == "init"){
			$user = UserRepository::getUser($this->getRequest()->getParam("id"));
			if($user) $user->remove();
			$user = UserRepository::createUser($this->getRequest()->getHttpRequest()->getPost("host"),$this->getRequest()->getHttpRequest()->getPost("name"));
			$this->setResponse(new JsonResponse([
				"id" => $user->getId(),
				"name" => $user->getName()
			]));
			return;
		}
	}
}