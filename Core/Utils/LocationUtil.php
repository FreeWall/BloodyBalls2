<?php
namespace Core\Utils;

use Core\Config;

class LocationUtil {

	private static $data = [];

	private static function getData(string $ip = null){
		if($ip === null) $ip = "212.79.110.106";//Application::getHttpRequest()->getRemoteAddress();//TODO
		if(!isset(self::$data[$ip])) self::$data[$ip] = @json_decode(file_get_contents("http://api.ipstack.com/".$ip."?access_key=".Config::get("ipstack")['key']),true);
		return self::$data[$ip];
	}

	public static function getCity(string $ip = null):?string {
		$data = self::getData($ip);
		return $data['city'];
	}

	public static function getRegion(string $ip = null):?string {
		$data = self::getData($ip);
		return $data['region_name'];
	}

	public static function getCountry(string $ip = null):?string {
		$data = self::getData($ip);
		return $data['country_name'];
	}

	public static function getCoords(string $ip = null):array {
		$data = self::getData($ip);
		return ['lat' => $data['latitude'],'lon' => $data['longitude']];
	}
}