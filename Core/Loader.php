<?php
namespace Core;

class Loader {

	const NAMESPACE_SEPARATOR = "\\";

	private $dirs = [];

	public function __construct(){
		spl_autoload_register([$this,"loadClass"]);
		$this->addDirectory(BASEDIR);
		$this->addDirectory(BASEDIR."/Core/");
		$this->addDirectory(BASEDIR."/Core/Dibi/");
		$this->addDirectory(BASEDIR."/Core/Latte/");
		$this->addDirectory(BASEDIR."/Core/Utils/");
	}

	public function addDirectory($path){
		$this->dirs[] = $path;
	}

	public function loadClass($className){
		//echo "<br>";print_r($className);
		$fileName = "";
		if(false !== ($lastNsPos = strripos($className,self::NAMESPACE_SEPARATOR))){
			$namespace = substr($className,0,$lastNsPos);
			$className = substr($className,$lastNsPos+1);
			$fileName = str_replace(self::NAMESPACE_SEPARATOR,DIRECTORY_SEPARATOR,$namespace).DIRECTORY_SEPARATOR;
		}
		$fileName .= str_replace('_',DIRECTORY_SEPARATOR,$className).".php";
		foreach($this->dirs AS $dir){
			if(file_exists($dir.$fileName)){
				require_once $dir.$fileName;
				break;
			}
		}
	}
}
new Loader();