<?php
namespace Models\Database;

abstract class BaseEntity implements \ArrayAccess {

	private static $entities = [];

	protected $id;
	protected $data;
	private $className;

	public function __construct($id,$data = null){
		$this->id = $id;
		$this->className = get_class($this);
		$this->data = (!$data && isset(self::$entities[$this->className][$id]) ? self::$entities[$this->className][$id] : $this->load($data === true ? null : (array)$data));
		self::$entities[$this->className][$id] = $this->data;
	}

	public function reload($data = null){
		$this->data = $this->load($data);
	}

	protected function getClassName():string {
		return $this->className;
	}

	public function exists():bool {
		return !empty($this->data);
	}

	public function getId(){
		return $this->id;
	}

	public function getData():array {
		return $this->data;
	}

	protected abstract function load(array $data = null):?array;

	public function offsetExists($key){
		return isset($this->data[$key]);
	}

	public function offsetGet($key){
		return $this->data[$key];
	}

	public function offsetSet($key,$value){
		$this->data[$key] = $value;
	}

	public function offsetUnset($key){
		unset($this->data[$key]);
	}
}