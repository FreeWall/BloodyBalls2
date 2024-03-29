<?php
namespace Core;

class Config {

	const VERSION = "0.0.1";

	const PROJECT_DIR = BASEDIR."../";
	const STATIC_DIR = APPDIR."static/";

	private static $data = [];

	/** @throws \Exception */
	public static function load(){
		self::$data = @parse_ini_file(self::PROJECT_DIR."config.ini",true);
		if(empty(self::$data)) throw new \Exception("Config file not found.");
	}

	public static function get($name){
		return self::$data[$name];
	}
}