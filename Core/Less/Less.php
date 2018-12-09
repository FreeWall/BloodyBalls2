<?php
namespace Core\Less;

require_once __DIR__."/LessParser.php";

class Less {

	private $parser;
	private $source;
	private $destination;
	private $imports;
	private $lastModified;

	public function __construct(string $source,string $destination,array $imports = []){
		$this->source = $source;
		$this->destination = $destination;
		$this->imports = $imports;
		$this->parser = new \LessParser();
		$this->parser->setFormatter("compressed");
		$this->parser->SetImportDir([dirname($this->source)]);
	}

	public function setLastModified($lastModified){
		$this->lastModified = $lastModified;
	}

	/** @throws \Exception */
	public function parse(array $params = []):bool {
		if($this->isChanged()){
			$content = file_get_contents($this->source);
			if(empty($content)) return false;
			if(!empty($params)){
				$paramsContent = "";
				foreach($params AS $key => $value){
					$paramsContent .= "@".$key.":".$value.";".PHP_EOL;
				}
				$content = str_replace("/*[VARIABLES]*/",$paramsContent,$content);
			}
			$content = $this->parser->compile($content);
			file_put_contents($this->destination,$content);
			return true;
		}
		return false;
	}

	private function isChanged():bool {
		$modifications = [];
		foreach([$this->source]+$this->imports AS $file){
			if(!file_exists($file)) throw new \Exception("File '".$file."' does not exists.");
			$modifications[] = @filemtime($file);
		}
		return max($modifications) >= $this->lastModified;
	}
}