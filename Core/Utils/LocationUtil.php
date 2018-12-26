<?php
namespace Core\Utils;

use Core\Application;
use Core\Config;
use Core\Database;

class LocationUtil {

	const CACHE_TIMEOUT = 86400;

	private static $data = [];

	private static function getData(string $ip = null){
		if($ip === null) $ip = Config::get("environment")['domain'] != "game.localhost" ? Application::getHttpRequest()->getRemoteAddress() : "212.79.110.106";
		if(!isset(self::$data[$ip])){
			$data = self::getCachedData($ip);
			if(!$data || $data['location_updated']+self::CACHE_TIMEOUT < time()){
				$content = @json_decode(file_get_contents("http://api.ipstack.com/".$ip."?access_key=".Config::get("ipstack")['key']),true);
				self::$data[$ip] = [
					'city'         => $content['city'],
					'region_name'  => $content['region_name'],
					'country_name' => $content['country_name'],
					'country_code' => $content['country_code'],
					'latitude'     => $content['latitude'],
					'longitude'    => $content['longitude'],
				];
				if($data) Database::query("UPDATE locations SET location_data = ?,location_updated = ? WHERE location_ip = ?",json_encode(self::$data[$ip]),time(),$ip);
				else Database::query("INSERT INTO locations",[
					"location_ip"      => $ip,
					"location_data"    => json_encode(self::$data[$ip]),
					"location_updated" => time()
				]);
			}
			else self::$data[$ip] = json_decode($data['location_data'],true);
		}
		return self::$data[$ip];
	}

	private static function getCachedData(string $ip = null){
		$data = Database::fetch("SELECT * FROM locations WHERE location_ip = ?",$ip);
		return $data ?? null;
	}

	public static function getCity(string $ip = null):string {
		$data = self::getData($ip);
		return ($data['city'] ?? "");
	}

	public static function getRegion(string $ip = null):string {
		$data = self::getData($ip);
		return ($data['region_name'] ?? "");
	}

	public static function getCountry(string $ip = null):string {
		$data = self::getData($ip);
		return ($data['country_name'] ?? "");
	}

	public static function getCountryCode(string $ip = null):string {
		$data = self::getData($ip);
		return ($data['country_code'] ?? "");
	}

	public static function getCoords(string $ip = null):array {
		$data = self::getData($ip);
		return ['lat' => ($data['latitude'] ?? 0),'lon' => ($data['longitude'] ?? 0)];
	}
}