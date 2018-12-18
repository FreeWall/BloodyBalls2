<?php
namespace Models\Game\Users;

use Core\Database;
use Models\Database\BaseEntity;

class User extends BaseEntity {

	const EXPIRE_TIMEOUT = 20;

	protected function load(array $data = null):?array {
		return ($data != null ? $data : Database::fetch("SELECT * FROM users WHERE user_id = ?",$this->getId()));
	}

	public function getHost():string {
		return $this->data['user_host'];
	}

	public function getName():string {
		return $this->data['user_name'];
	}

	public function getIp():string {
		return $this->data['user_ip'];
	}

	public function getCountry():string {
		return $this->data['user_country'];
	}

	public function getCoordLat():float {
		return $this->data['user_coord_lat'];
	}

	public function getCoordLon():float {
		return $this->data['user_coord_lon'];
	}

	public function getLastPing():int {
		return $this->data['user_lastping'];
	}

	public function isOnline():bool {
		return ($this->getLastPing()+self::EXPIRE_TIMEOUT > time());
	}

	public function getDistanceToUser(User $user):int {
		$latFrom = deg2rad($this->getCoordLat());
		$lonFrom = deg2rad($this->getCoordLon());
		$latTo = deg2rad($user->getCoordLat());
		$lonTo = deg2rad($user->getCoordLon());
		$latDelta = $latTo - $latFrom;
		$lonDelta = $lonTo - $lonFrom;
		$angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
		return round($angle*6371);
	}

	public function update(){
		Database::query("UPDATE users SET user_lastping = ? WHERE user_id = ?",time(),$this->getId());
	}
}