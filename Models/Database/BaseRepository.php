<?php
namespace Models\Database;

trait BaseRepository {

	/** @var BaseEntity[] */
	private static $entities = [];

	/** @return mixed|null */
	private static function getEntity($id,$data,$class){
		if(!isset(self::$entities[$id])){
			self::$entities[$id] = new $class($id,$data);
			if(!self::$entities[$id]->exists()) self::$entities[$id] = null;
		}
		return self::$entities[$id];
	}
}