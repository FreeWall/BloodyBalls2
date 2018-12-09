<?php
namespace Core\Minify;

class Minify {

	private $parser;
	private $destination;
	private $imports;

	public function __construct(string $destination,array $imports){
		$this->destination = $destination;
		$this->imports = $imports;
		$this->parser = new MinifierJS();
	}

	/** @throws \Exception */
	public function parse():bool {
		if(!file_exists($this->destination) || $this->isChanged()){
			foreach($this->imports AS $file){
				$this->parser->add($file);
			}
			$this->parser->minify($this->destination);
			return true;
		}
		return false;
	}

	private function isChanged():bool {
		$modifications = [];
		foreach($this->imports AS $file){
			if(!file_exists($file)) throw new \Exception("File '".$file."' does not exists.");
			$modifications[] = @filemtime($file);
		}
		return max($modifications) >= @filemtime($this->destination);
	}
}