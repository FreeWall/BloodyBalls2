<?php
namespace Core\Templates;

use Core\Application;
use Latte\Engine;

class BaseTemplate implements ITemplate {

	private $latte;
	private $file;
	private $params = [];
	private $directory;

	public function __construct($file,array $params = []){
		$this->latte = new Engine();
		$this->file = $file;
		$this->params = $params;
		$this->directory = BASEDIR."Apps/".Application::getAppName()."/Templates/";
		$this->latte->setTempDirectory(BASEDIR."www/".strtolower(Application::getAppName())."/cache/");
	}

	public function getLatte():Engine {
		return $this->latte;
	}

	public function getFile():string {
		return $this->file;
	}

	public function getDirectory():string {
		return $this->directory;
	}

	public function setDirectory(string $directory){
		$this->directory = $directory;
	}

	public function getParams():array {
		return $this->params;
	}

	public function addParams(array $params){
		$this->params = array_merge($this->params,$params);
	}

	public function addFilter(string $name,callable $callback){
		$this->latte->addFilter($name,$callback);
		return $this;
	}

	public function render(){
		$this->latte->render($this->directory.DIRECTORY_SEPARATOR.strtolower($this->file),$this->params);
	}

	public function renderToString():string {
		return $this->latte->renderToString($this->directory.DIRECTORY_SEPARATOR.strtolower($this->file),$this->params);
	}
}