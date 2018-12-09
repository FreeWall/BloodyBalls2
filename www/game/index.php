<?php
define("APPDIR",__DIR__."/");
require_once __DIR__."/../../bootstrap.php";
$app = new \Apps\Game\GameApplication();
$app->run();