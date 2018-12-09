<?php
namespace Core\Utils\Table\Filterer;

class TableFilterParamGroup implements ITableFilterParam {

	/** @var ITableFilterParam[] */
	private $filters;

	public function __construct($filters){
		$this->filters = $filters;
	}

	public function getValue(){
		return reset($this->filters)->getValue();
	}

	public function setValue($value){
		foreach($this->filters AS $filter){
			$filter->setValue($value);
		}
	}

	public function isValid($data):bool {
		foreach($this->filters AS $filter){
			foreach($data AS $values){
				if($filter->isValid($values)) return true;
			}
		}
		return false;
	}

	public function getQuery():?string {
		$tmpColumns = [];
		foreach($this->filters AS $filter){
			$tmpColumns[] = $filter->getQuery();
		}
		return implode(" OR ",$tmpColumns);
	}
}