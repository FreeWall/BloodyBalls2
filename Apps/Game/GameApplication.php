<?php
namespace Apps\Game;

use Core\Application;
use Core\ApplicationType;

class GameApplication extends Application {

	public function __construct(){
		parent::__construct(ApplicationType::GAME);
		$this->loadStyles();
	}
}