<?php
namespace Models\Game\Users;

use Core\Application;
use Core\Database;
use Core\Utils\LocationUtil;
use Models\Database\BaseRepository;

class UserRepository {

	use BaseRepository;

	public static function getUser(int $id,$data = null):?User {
		return self::getEntity($id,$data,User::class);
	}

	/** @return User[] */
	public static function getUsers(array $wheres = null,array $sorting = null,array $limits = null){
		$entities = [];
		$data = Database::dataSource("users");
		if($wheres) $data->where($wheres);
		if($sorting) $data->orderBy($sorting[0],$sorting[1]);
		if($limits) $data->applyLimit($limits[0],$limits[1]);
		foreach($data AS $values){
			$entities[] = self::getUser($values['user_id'],$values);
		}
		return $entities;
	}

	/** @return User[] */
	public static function getNearestUsers(User $user){
		$entities = [];
		$data = Database::query("SELECT *,
			( 3959 * acos( cos( radians('".$user->getCoordLat()."') ) * 
			cos( radians( user_coord_lat ) ) * 
			cos( radians( user_coord_lon ) - 
			radians('".$user->getCoordLon()."') ) + 
			sin( radians('".$user->getCoordLat()."') ) * 
			sin( radians( user_coord_lat ) ) ) ) 
			AS distance FROM users WHERE user_lastping >= '".(time()-User::EXPIRE_TIMEOUT)."' ORDER BY distance ASC,user_lastping DESC");
		foreach($data AS $values){
			$entities[] = self::getUser($values['user_id'],$values);
		}
		return $entities;
	}

	public static function createUser(string $host,string $name):User {
		$coords = ['lat' => 0,'lon' => 0];//LocationUtil::getCoords();
		$country = "cz";//LocationUtil::getCountryCode();
		$coords = LocationUtil::getCoords();
		$country = LocationUtil::getCountryCode();
		Database::query("INSERT INTO users",[
			"user_host"      => $host,
			"user_name"      => $name,
			"user_ip"        => Application::getHttpRequest()->getRemoteAddress(),
			"user_country"   => $country,
			"user_coord_lat" => $coords['lat'],
			"user_coord_lon" => $coords['lon'],
			"user_lastping"  => time()
		]);
		return self::getUser(Database::getInsertId());
	}
}