<?php
namespace Core\Utils\Table\Paginator;

use Core\Application;
use Core\Utils\Strings;
use Latte\Runtime\Html;

trait TablePaginator {

	private $page = 1;
	private $limit = 20;
	private $count = 0;
	private $totalCount = 0;
	private $template;

	public function loadPage($params = null){
		if($params === null) $params = Application::getHttpRequest()->getQuery();
		$this->page = (empty($params['page']) ? 1 : $params['page']);
	}

	public function getPage(){
		return $this->page;
	}

	public function setPage($page){
		$this->page = $page;
	}

	public function setLimit($limit){
		$this->limit = $limit;
	}

	public function getLimitQuery():array {
		return [$this->limit,($this->page-1)*$this->limit];
	}

	public function slicePageItems(array &$items){
		$this->totalCount = count($items);
		$items = array_slice($items,($this->page-1)*$this->limit,$this->limit,true);
		$this->count = count($items);
	}

	public function getPages(?string $template = '<a href="?page=#page#"><div class="button #type#">#number#</div></a>',string $sort = null){
		if($sort !== null) $template = '<a href="?sort='.$sort.'&page=#page#"><div class="button #type#">#number#</div></a>';
		$this->template = $template;
		$content = "";
		if($this->totalCount <= $this->limit) return null;

		$lastPage = ceil($this->totalCount/$this->limit);
		if($lastPage == 0) $lastPage = 1;
		$prevPage = ($this->page-1 > 0 ? $this->page-1 : 1);
		$nextPage = ($this->page+1 > $lastPage ? $lastPage : $this->page+1);

		$content .= $this->getPrevButton($prevPage);
		if($this->page < 5){
			if($lastPage > 5) for($i=1;$i<6;$i++) $content .= $this->getPageButton($i);
			else for($i=1;$i<$lastPage+1;$i++) $content .= $this->getPageButton($i);
			if($lastPage > 6) $content .= $this->getPageButton().$this->getPageButton($lastPage);
			else if($lastPage > 5) $content .= $this->getPageButton($lastPage);
		}
		else if($this->page > $lastPage-4){
			$content .= $this->getPageButton(1).$this->getPageButton();
			for($i=$lastPage-4;$i<$lastPage+1;$i++) $content .= $this->getPageButton($i);
		} else {
			$content .= $this->getPageButton(1).$this->getPageButton();
			for($i=$this->page-1;$i<$this->page+2;$i++) $content .= $this->getPageButton($i);
			if($lastPage > $this->page+1) $content .= $this->getPageButton().$this->getPageButton($lastPage);
			else if($lastPage > $this->page+1) $content .= $this->getPageButton($lastPage);
		}
		$content .= $this->getNextButton($nextPage);
		return new Html($content);
	}

	private function getPageButton($i = -1){
		return ($i == -1 ? strtr($this->template,array("#type#" => "dots","#number#" => "..","#page#" => "#page#")) : ($i == $this->page ? strtr($this->template,array("#type#" => "page current","#number#" => $i,"#page#" => $i)) : strtr($this->template,array("#type#" => "page","#number#" => $i,"#page#" => $i))));
	}

	private function getPrevButton($i){
		return strtr($this->template,array("#type#" => "prev","#number#" => "<i class='fa fa-caret-left'></i>","#page#" => $i));
	}

	private function getNextButton($i){
		return strtr($this->template,array("#type#" => "next","#number#" => "<i class='fa fa-caret-right'></i>","#page#" => $i));
	}

	public function getCounts(){
		return new Html("<b>".$this->count."</b> ".Strings::inflect($this->count,array("položka","položky","položek")).($this->totalCount > 0 ? ", ".$this->totalCount." celkem" : ""));
	}

	public function setCounts(array $items,int $total = 0){
		$this->count = count($items);
		$this->totalCount = $total;
	}
}