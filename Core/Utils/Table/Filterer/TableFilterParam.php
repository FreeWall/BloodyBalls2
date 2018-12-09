<?php
namespace Core\Utils\Table\Filterer;

use Core\Utils\Validators;

class TableFilterParam implements ITableFilterParam {

	private $type;
	private $value;
	private $column;

	public function __construct(int $type,string $column = null){
		$this->type = $type;
		$this->column = $column;
	}

	public function getValue(){
		return $this->value;
	}

	public function setValue($value){
		$this->value = $value;
	}

	public function isValid($data):bool {
		if(Validators::isEmpty($this->value)) return true;
		if($this->type == TableFilterParamType::EQUAL && $this->value == $data) return true;
		else if($this->type == TableFilterParamType::CONTAINS && stristr($data,$this->value) !== false) return true;
		else if($this->type == TableFilterParamType::MIN && $this->value < $data) return true;
		else if($this->type == TableFilterParamType::MAX && $this->value > $data) return true;
		return false;
	}

	public function getQuery():?string {
		if(Validators::isEmpty($this->value)) return null;
		if($this->type == TableFilterParamType::EQUAL) return $this->column." = '".$this->value."'";
		else if($this->type == TableFilterParamType::CONTAINS) return $this->column." LIKE '%".$this->value."%'";
		else if($this->type == TableFilterParamType::MIN) return $this->column." >= '".$this->value."'";
		else if($this->type == TableFilterParamType::MAX) return $this->column." <= '".$this->value."'";
		return null;
	}
}