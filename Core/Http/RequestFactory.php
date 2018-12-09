<?php
namespace Core\Http;

use Core\Utils\Strings;

class RequestFactory {

	const CHARS = '\x09\x0A\x0D\x20-\x7E\xA0-\x{10FFFF}';
	const FILTERS = [
		'path' => ['#/{2,}#' => '/'], // '%20' => ''
		'url' => [], // '#[.,)]\z#' => ''
	];

	/**
	 * Creates current HttpRequest object.
	 */
	public static function createHttpRequest():Request{
		// DETECTS URI, base path and script path of the request.
		$url = new Url;
		$url->setScheme(!empty($_SERVER['HTTPS']) && strcasecmp($_SERVER['HTTPS'],'off') ? 'https' : 'http');
		$url->setUser($_SERVER['PHP_AUTH_USER'] ?? '');
		$url->setPassword($_SERVER['PHP_AUTH_PW'] ?? '');
		// host & port
		if(
			(isset($_SERVER[$tmp = 'HTTP_HOST']) || isset($_SERVER[$tmp = 'SERVER_NAME']))
			&& preg_match('#^([a-z0-9_.-]+|\[[a-f0-9:]+\])(:\d+)?\z#i',$_SERVER[$tmp],$pair)
		){
			$url->setHost(strtolower($pair[1]));
			if(isset($pair[2])){
				$url->setPort((int)substr($pair[2],1));
			} elseif(isset($_SERVER['SERVER_PORT'])) {
				$url->setPort((int)$_SERVER['SERVER_PORT']);
			}
		}
		// path & query
		$requestUrl = $_SERVER['REQUEST_URI'] ?? '/';
		$requestUrl = preg_replace('#^\w++://[^/]++#','',$requestUrl);
		$requestUrl = Strings::replace($requestUrl,self::FILTERS['url']);
		$tmp = explode('?',$requestUrl,2);
		$path = Url::unescape($tmp[0],'%/?#');
		$path = Strings::fixEncoding(Strings::replace($path,self::FILTERS['path']));
		$url->setPath($path);
		$url->setQuery($tmp[1] ?? '');
		// GET, POST, COOKIE
		$useFilter = (!in_array(ini_get('filter.default'),['','unsafe_raw'],true) || ini_get('filter.default_flags'));
		$query = $url->getQueryParameters();
		$post = $useFilter ? filter_input_array(INPUT_POST,FILTER_UNSAFE_RAW) : (empty($_POST) ? [] : $_POST);
		$cookies = $useFilter ? filter_input_array(INPUT_COOKIE,FILTER_UNSAFE_RAW) : (empty($_COOKIE) ? [] : $_COOKIE);
		// remove invalid characters
		$reChars = '#^['.self::CHARS.']*+\z#u';
		$url->setQuery($query);
		// FILES and create FileUpload objects
		$files = [];
		$list = [];
		if(!empty($_FILES)){
			foreach($_FILES as $k => $v){
				if(
					!is_array($v)
					|| !isset($v['name'],$v['type'],$v['size'],$v['tmp_name'],$v['error'])
					|| (is_string($k) && (!preg_match($reChars,$k) || preg_last_error()))
				){
					continue;
				}
				$v['@'] = &$files[$k];
				$list[] = $v;
			}
		}
		foreach($list as &$v){
			if(!isset($v['name'])){
				continue;
			} elseif(!is_array($v['name'])) {
				if((!preg_match($reChars,$v['name']) || preg_last_error())){
					$v['name'] = '';
				}
				continue;
			}
			foreach($v['name'] as $k => $foo){
				if(is_string($k) && (!preg_match($reChars,$k) || preg_last_error())){
					continue;
				}
				$list[] = [
					'name' => $v['name'][$k],
					'type' => $v['type'][$k],
					'size' => $v['size'][$k],
					'tmp_name' => $v['tmp_name'][$k],
					'error' => $v['error'][$k],
				];
			}
		}
		// HEADERS
		if(function_exists('apache_request_headers')){
			$headers = apache_request_headers();
		} else {
			$headers = [];
			foreach($_SERVER as $k => $v){
				if(strncmp($k,'HTTP_',5) == 0){
					$k = substr($k,5);
				} elseif(strncmp($k,'CONTENT_',8)) {
					continue;
				}
				$headers[strtr($k,'_','-')] = $v;
			}
		}
		$remoteAddr = !empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;
		$remoteHost = !empty($_SERVER['REMOTE_HOST']) ? $_SERVER['REMOTE_HOST'] : null;
		// method, eg. GET, PUT, ...
		$method = $_SERVER['REQUEST_METHOD'] ?? null;
		if(
			$method === 'POST'
			&& isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])
			&& preg_match('#^[A-Z]+\z#',$_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])
		){
			$method = $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];
		}
		return new Request($url,$post,$files,$cookies,$headers,$method,$remoteAddr,$remoteHost);
	}
}