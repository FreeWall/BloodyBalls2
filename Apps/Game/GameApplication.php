<?php
namespace Apps\Game;

use Core\Application;
use Core\ApplicationType;
use Core\Config;
use Core\Less\Less;
use Core\Minify\Minify;
use Tracy\Debugger;

class GameApplication extends Application {

	public function __construct(){
		parent::__construct(ApplicationType::GAME);
		$this->loadStyles();
		$this->minifyJS();
	}

	public function loadStyles(){
		$less = new Less(Config::STATIC_DIR."css/styles.less",Config::STATIC_DIR."css/styles.css");
		$less->setLastModified(filemtime(Config::STATIC_DIR."css/styles.less"));
		try {
			$less->parse();
		} catch(\Exception $e){
			if(Debugger::isEnabled()) Debugger::exceptionHandler($e);
			$this->runErrorPresenter();
		}
	}

	public function minifyJS(){
		$files = json_decode(file_get_contents(Config::STATIC_DIR."js/client.json"),true);
		foreach($files AS $idx => $file) $files[$idx] = Config::STATIC_DIR."js/".$file;
		$minify = new Minify(Config::STATIC_DIR."js/client.min.js",$files);
		try {
			$minify->parse();
		} catch(\Exception $e){
			if(Debugger::isEnabled()) Debugger::exceptionHandler($e);
			$this->runErrorPresenter();
		}

		$files = json_decode(file_get_contents(Config::STATIC_DIR."js/server.json"),true);
		foreach($files AS $idx => $file) $files[$idx] = Config::STATIC_DIR."js/".$file;
		$minify = new Minify(Config::STATIC_DIR."js/server.min.js",$files);
		try {
			$minify->parse();
		} catch(\Exception $e){
			if(Debugger::isEnabled()) Debugger::exceptionHandler($e);
			$this->runErrorPresenter();
		}
	}
}