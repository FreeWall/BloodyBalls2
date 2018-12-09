<?php
namespace Models\Game\Rooms;

use Core\Database;
use Core\Security\Passwords;
use Models\Database\BaseRepository;
use Models\Game\Users\User;

class RoomRepository {

	use BaseRepository;

	public static function getRoom(int $id,$data = null):Room {
		return self::getEntity($id,$data,Room::class);
	}

	/** @return Room[] */
	public static function getRooms(array $wheres = null,array $sorting = null,array $limits = null){
		$entities = [];
		$data = Database::dataSource("rooms");
		if($wheres) $data->where($wheres);
		if($sorting) $data->orderBy($sorting[0],$sorting[1]);
		if($limits) $data->applyLimit($limits[0],$limits[1]);
		foreach($data AS $values){
			$entities[] = self::getRoom($values['room_id'],$values);
		}
		return $entities;
	}

	/** @return Room[] */
	public static function getNearestRooms(User $user){
		$entities = [];
		$data = Database::query("SELECT *,
			( 3959 * acos( cos( radians('".$user->getCoordLat()."') ) * 
			cos( radians( user_coord_lat ) ) * 
			cos( radians( user_coord_lon ) - 
			radians('".$user->getCoordLon()."') ) + 
			sin( radians('".$user->getCoordLat()."') ) * 
			sin( radians( user_coord_lat ) ) ) ) 
			AS distance FROM rooms INNER JOIN users USING(user_id) WHERE user_lastping >= '".(time()-User::EXPIRE_TIMEOUT)."' ORDER BY distance ASC");
		foreach($data AS $values){
			$entities[] = self::getRoom($values['room_id'],$values);
		}
		return $entities;
	}

	public static function createRoom(User $user,string $host,string $name,string $password,int $maxplayers):Room {
		Database::query("INSERT INTO rooms",[
			"user_id"         => $user->getId(),
			"room_host"       => $host,
			"room_name"       => $name,
			"room_password"   => (!empty($password) ? Passwords::hash($password) : ""),
			"room_players"    => 1,
			"room_maxplayers" => $maxplayers
		]);
		return self::getRoom(Database::getInsertId());
	}
}