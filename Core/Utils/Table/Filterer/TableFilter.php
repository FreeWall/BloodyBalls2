<?php
namespace Core\Utils\Table\Filterer;

use Core\Application;
use Core\Http\SessionSection;

trait TableFilter {

	/** @var ITableFilterParam[] */
	private $filters = [];
	private $filtersData = [];
	private $filterKey;
	private $session;

	private function getFiltersSession():SessionSection {
		if(!$this->session) $this->session = Application::getSession()->getSection("filter_".$this->filterKey);
		return $this->session;
	}

	/** @return ITableFilterParam[] */
	public function getFilters(){
		return $this->filters;
	}

	public function hasFilters():bool {
		foreach($this->filters AS $filter){
			if($filter->getValue() != null) return true;
		}
		return false;
	}

	public function setFilters($filterKey,array $filters){
		$this->filterKey = $filterKey;
		$this->filters = $filters;
		foreach($this->filters AS $idx => $filter){
			if(is_array($filter)) $this->filters[$idx] = new TableFilterParamGroup($filter);
			if(isset($this->getFiltersSession()[$this->filterKey][$idx])){
				$this->filters[$idx]->setValue($this->getFiltersSession()[$this->filterKey][$idx]);
			}
		}
	}

	public function loadFilters(?array $params){
		foreach($this->filters AS $idx => $filter){
			if(isset($params[$idx])){
				$this->filters[$idx]->setValue($params[$idx]);
			} else {
				$this->filters[$idx]->setValue(null);
			}
		}
		$filters = [];
		foreach($this->filters AS $idx => $filter){
			$filters[$idx] = $filter->getValue();
		}
		$this->getFiltersSession()[$this->filterKey] = $filters;
	}

	public function setFiltersData(array $data){
		$this->filtersData = $data;
	}

	public function isFiltersValid(array $data = null):bool {
		if($data) $this->filtersData = $data;
		foreach($this->filters AS $idx => $filter){
			if(isset($this->filtersData[$idx]) && !$filter->isValid($this->filtersData[$idx])) return false;
		}
		return true;
	}

	public function getFilterQuery(){
		$wheres = [];
		foreach($this->filters AS $filter){
			$query = $filter->getQuery();
			if($query !== null) $wheres[] = [$query];
		}
		return $wheres;
	}
}