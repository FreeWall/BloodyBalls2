<?php
namespace Models\Game\Rooms;

use Core\Database;
use Models\Database\BaseEntity;
use Models\Game\Users\User;
use Models\Game\Users\UserRepository;

class Room extends BaseEntity {

	const EXPIRE_TIMEOUT = 30;

	protected function load(array $data = null):?array {
		return ($data != null ? $data : Database::fetch("SELECT * FROM rooms WHERE room_id = ?",$this->getId()));
	}

	public function getHost():string {
		return $this->data['room_host'];
	}

	public function getUserId():int {
		return $this->data['user_id'];
	}

	public function getUser():User {
		return UserRepository::getUser($this->getUserId());
	}

	public function getName():string {
		return $this->data['room_name'];
	}

	public function hasPassword():bool {
		return boolval($this->data['room_password']);
	}

	public function getPlayers():int {
		return $this->data['room_players'];
	}

	public function getMaxPlayers():int {
		return $this->data['room_maxplayers'];
	}

	public function getCreated():int {
		return $this->data['room_created'];
	}

	public function getCountry():string {
		return $this->getUser()->getCountry();
	}

	public function getDistanceToUser(User $user):int {
		return $this->getUser()->getDistanceToUser($user);
	}

	public function update($data){
		$values = [];
		if(isset($data['players'])) $values['players'] = $data['players'];
		$values['room_updated'] = time();
		Database::query("UPDATE rooms SET %a WHERE user_id = ?",$values,$this->getId());
	}

	public function remove(){
		Database::query("DELETE FROM rooms WHERE room_id = ?",$this->getId());
	}
}