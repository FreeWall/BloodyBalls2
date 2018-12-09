<?php
namespace Core\Utils\Table\Sorter;

use Core\Application;

trait TableSorter {

	private $sort;
	private $sortType = TableSortType::DESC;

	private $sortColumns = [];

	public function loadSort($params = null){
		if($params === null) $params = Application::getHttpRequest()->getQuery();
		$this->sort = (!empty($params['sort']) ? ltrim($params['sort'],"-") : $this->sort);
		$this->sortType = (!empty($params['sort']) ? ($params['sort'][0] == "-" ? TableSortType::ASC : TableSortType::DESC) : $this->sortType);
	}

	public function getSort():string {
		return $this->sort;
	}

	public function getSortType():string {
		return $this->sortType;
	}

	public function getSortValue():string {
		return ($this->getSortType() == TableSortType::ASC ? "-" : "").$this->getSort();
	}

	public function setSort($sort,string $sortType){
		$this->sort = $sort;
		$this->sortType = $sortType;
	}

	public function setSortColumns(array $columns){
		$this->sortColumns = $columns;
	}

	public function getSortColumns():array {
		$columns = [];
		foreach($this->sortColumns AS $key => $column){
			$columns[$key] = [
				"name"  => ($this->getSort() == $key && $this->getSortType() == TableSortType::ASC ? "" : "-").$key,
				"class" => ($this->getSort() == $key ? " sorted ".strtolower($this->getSortType()) : "")
			];
		}
		return $columns;
	}

	public function getSortQuery():array {
		return [$this->sortColumns[$this->getSort()],$this->sortType];
	}

	public function sortItems(&$items,callable $sortDataCallback){
		if(isset($this->sortColumns[$this->getSort()])){
			usort($items,function($entry1,$entry2) use ($sortDataCallback){
				$data = $sortDataCallback($entry1,$entry2);
				return ($data[0][$this->getSort()] > $data[1][$this->getSort()] ? -1 : 1);
			});
		}
	}
}