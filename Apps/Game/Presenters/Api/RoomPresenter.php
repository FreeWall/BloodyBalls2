<?php
namespace Apps\Game\Presenters\Api;

use Apps\Game\Presenters\BasePresenter;
use Core\Responses\JsonResponse;
use Core\Responses\VoidResponse;
use Core\Templates\Template;
use Core\Utils\Validators;
use Models\Game\Rooms\RoomRepository;
use Models\Game\Users\UserRepository;

class RoomPresenter extends BasePresenter {

	public function render(){
		//usleep(500*1000);
		$user = UserRepository::getUser($this->getRequest()->getParam("id"));
		if(!$user){
			$this->setResponse(new VoidResponse());
			return;
		} else {
			$user->update();
		}

		if($this->getAction() == "rooms-users"){
			$rooms = [];
			$roomsTmp = RoomRepository::getNearestRooms($user);
			foreach($roomsTmp AS $room){
				$rooms[$room->getId()] = [
					"id"         => $room->getId(),
					"name"       => $room->getName(),
					"password"   => $room->hasPassword(),
					"players"    => $room->getPlayers(),
					"maxplayers" => $room->getMaxPlayers(),
					"country"    => $room->getCountry(),
					"distance"   => $room->getDistanceToUser($user),
				];
			}

			$users = [];
			$usersTmp = UserRepository::getNearestUsers($user);
			foreach($usersTmp AS $user2){
				$users[$user2->getId()] = [
					"id"         => $user2->getId(),
					"name"       => $user2->getName(),
					"country"    => $user2->getCountry(),
					"distance"   => $user2->getDistanceToUser($user)
				];
			}

			$roomsTemplate = new Template("rooms.latte");
			$roomsTemplate->addParams(["rooms" => $rooms]);

			$usersTemplate = new Template("users.latte");
			$usersTemplate->addParams(["users" => $users]);

			$this->setResponse(new JsonResponse([
				"rooms" => $roomsTemplate->renderToString(),
				"users" => $usersTemplate->renderToString()
			]));
		}
		else if($this->getAction() == "create"){
			$host = $this->getRequest()->getHttpRequest()->getPost("host");
			$name = $this->getRequest()->getHttpRequest()->getPost("name");
			$password = boolval($this->getRequest()->getHttpRequest()->getPost("password"));
			$maxplayers = $this->getRequest()->getHttpRequest()->getPost("maxplayers");
			if(!Validators::isEmpty($host) && !Validators::isEmpty($name)){
				if($maxplayers < 2) $maxplayers = 2;
				else if($maxplayers > 20) $maxplayers = 20;
				$room = RoomRepository::createRoom($user,$host,$name,$password,$maxplayers);
				$this->setResponse(new JsonResponse([
					"id"   => $room->getId(),
					"host" => $room->getHost(),
					"name" => $room->getName(),
				]));
				return;
			}
			$this->setResponse(new VoidResponse());
		}
		else if($this->getAction() == "join"){
			$id = $this->getRequest()->getHttpRequest()->getPost("id");
			$room = RoomRepository::getRoom($id);
			if($room){
				$this->setResponse(new JsonResponse([
					"id"       => $room->getId(),
					"host"     => $room->getHost(),
					"name"     => $room->getName(),
				]));
				return;
			}
			$this->setResponse(new VoidResponse());
		}
	}
}