<?php
namespace Core\Router\Routes;

use Core\Application;
use Core\Request;

abstract class Route {

	private static $matchTypes = [
		'i'  => '[0-9]++',
		'a'  => '[0-9A-Za-z_]++',
		'h'  => '[0-9A-Fa-f]++',
		's'  => '[0-9A-Za-z-_]++',
		'*'  => '.+?',
		'**' => '.++',
		''   => '[^/\.]++'
	];

	private $mask;
	private $type;
	private $presenter;
	private $name;
	private $fullname;
	private $redirect;
	private $source;

	public function __construct($mask,$type,$presenter,$name = null,$redirect = null,$prefix = null){
		$this->mask = ($prefix ? $prefix."/" : null).$mask;
		$this->type = $type;
		$this->presenter = $presenter;
		$this->name = ($name == null ? str_replace('/','#',$this->presenter) : $name);
		$this->fullname = $this->type.":".$this->name;
		$this->redirect = ($redirect != null ? $this->type.":".$redirect : null);
	}

	public function getType(){
		return $this->type;
	}

	public function getMask():string {
		return $this->mask;
	}

	public function getPresenter():string {
		return $this->presenter;
	}

	public function getName():string {
		return $this->name;
	}

	public function getFullName():string {
		return $this->fullname;
	}

	public function getRedirect():?string {
		return $this->redirect;
	}

	public function getSource(){
		return $this->source;
	}

	public function setSource($source){
		$this->source = $source;
	}

	public abstract function getBaseUrl():string;

	public function match(\Core\Http\Request $request){
		$requestUrl = $request->getUrl()->getPath();
		if(substr($requestUrl,0,1) === '/') $requestUrl = substr($requestUrl,1,strlen($requestUrl)-1);

		$match = null;
		$params = array();

		if($this->getMask() === '*'){
			$match = true;
		} elseif(isset($this->getMask()[0]) && $this->getMask()[0] === '@'){
			$pattern = '`'.substr($this->getMask(),1).'`u';
			$match = preg_match($pattern,$requestUrl,$params) === 1;
		} elseif(($position = strpos($this->getMask(),'[')) === false){
			$match = strcmp($requestUrl,$this->getMask()) === 0;
		} else {
			if(strncmp($requestUrl,$this->getMask(),$position) !== 0){
				return null;
			}
			$regex = self::compileRoute($this->getMask());
			$match = preg_match($regex,$requestUrl,$params) === 1;
		}

		if($match){
			if($params){
				foreach($params as $key => $value){
					if(is_numeric($key)) unset($params[$key]);
				}
			}
			return new Request(
				$request,
				$this,
				$this->presenter,
				$params
			);
		}
		return null;
	}

	private function compileRoute($route){
		if(preg_match_all('`(/|\.|)\[([^:\]]*+)(?::([^:\]]*+))?\](\?|)`',$route,$matches,PREG_SET_ORDER)){

			$matchTypes = self::$matchTypes;
			foreach($matches as $match){
				list($block,$pre,$type,$param,$optional) = $match;

				if(isset($matchTypes[$type])){
					$type = $matchTypes[$type];
				}
				if($pre === '.'){
					$pre = '\.';
				}

				$optional = $optional !== '' ? '?' : null;

				$pattern = '(?:'
					. ($pre !== '' ? $pre : null)
					. '('
					. ($param !== '' ? "?P<$param>" : null)
					. $type
					. ')'
					. $optional
					. ')'
					. $optional;

				$route = str_replace($block,$pattern,$route);
			}

		}
		return "`^$route$`u";
	}

	public function generate(array $params = null,$base = true):string {
		$url = ($base && $this->type != Application::getType() ? $this->getBaseUrl() : null)."/".$this->getMask();
		if (preg_match_all('`(/|\.|)\[([^:\]]*+)(?::([^:\]]*+))?\](\?|)`',$this->getMask(),$matches,PREG_SET_ORDER)){
			foreach($matches as $index => $match){
				list($block,$pre,,$param,$optional) = $match;

				if($pre){
					$block = substr($block,1);
				}

				if(isset($params[$param])){
					$url = str_replace($block,$params[$param],$url);
				} elseif($optional && $index != 0) {
					$url = str_replace($pre.$block,'',$url);
				} else {
					$url = str_replace($block,'',$url);
				}
			}
		}
		return $url;
	}

	public function __toString(){
		return $this->generate();
	}
}