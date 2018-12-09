<?php
namespace Core\Http;

class Request {

	private $method;
	private $url;
	private $post;
	private $files;
	private $cookies;
	private $headers;
	private $remoteAddress;
	private $remoteHost;

	public function __construct(Url $url,array $post = null,array $files = null,array $cookies = null,array $headers = null,string $method = null,string $remoteAddress = null,string $remoteHost = null){
		$this->url = $url;
		$this->post = (array)$post;
		$this->files = (array)$files;
		$this->cookies = (array)$cookies;
		$this->headers = array_change_key_case((array)$headers,CASE_LOWER);
		$this->method = $method ?: 'GET';
		$this->remoteAddress = $remoteAddress;
		$this->remoteHost = $remoteHost;
	}

	public function getUrl():Url{
		return clone $this->url;
	}

	public function getQuery(string $key = null){
		if(func_num_args() === 0){
			return $this->url->getQueryParameters();
		} elseif(func_num_args() > 1) {
			trigger_error(__METHOD__.'() parameter $default is deprecated, use operator ??',E_USER_DEPRECATED);
		}
		return $this->url->getQueryParameter($key);
	}

	public function getPost(string $key = null){
		if(func_num_args() === 0){
			return $this->post;
		} elseif(func_num_args() > 1) {
			trigger_error(__METHOD__.'() parameter $default is deprecated, use operator ??',E_USER_DEPRECATED);
		}
		return $this->post[$key] ?? null;
	}

	public function getFile(string $key){
		return $this->files[$key] ?? null;
	}

	public function getFiles():array{
		return $this->files;
	}

	public function getCookie(string $key){
		if(func_num_args() > 1){
			trigger_error(__METHOD__.'() parameter $default is deprecated, use operator ??',E_USER_DEPRECATED);
		}
		return $this->cookies[$key] ?? null;
	}

	public function getCookies():array{
		return $this->cookies;
	}

	public function getMethod():string{
		return $this->method;
	}

	public function isMethod(string $method):bool{
		return strcasecmp($this->method,$method) === 0;
	}

	public function getHeader(string $header):string{
		if(func_num_args() > 1){
			trigger_error(__METHOD__.'() parameter $default is deprecated, use operator ??',E_USER_DEPRECATED);
		}
		$header = strtolower($header);
		return $this->headers[$header] ?? null;
	}

	public function getHeaders():array{
		return $this->headers;
	}

	public function getReferer():Url{
		return isset($this->headers['referer']) ? new Url($this->headers['referer']) : null;
	}

	public function isSecured():bool{
		return $this->url->getScheme() === 'https';
	}

	public function isSameSite():bool{
		return isset($this->cookies['nette-samesite']);
	}

	public function isAjax():bool{
		return $this->getHeader('X-Requested-With') === 'XMLHttpRequest';
	}

	public function getRemoteAddress():string{
		return $this->remoteAddress;
	}

	public function getRemoteHost():string{
		if($this->remoteHost === null && $this->remoteAddress !== null){
			$this->remoteHost = gethostbyaddr($this->remoteAddress);
		}
		return $this->remoteHost;
	}

	public function detectLanguage(array $langs):string{
		$header = $this->getHeader('Accept-Language');
		if(!$header){
			return null;
		}
		$s = strtolower($header);  // case insensitive
		$s = strtr($s,'_','-');  // cs_CZ means cs-CZ
		rsort($langs);             // first more specific
		preg_match_all('#('.implode('|',$langs).')(?:-[^\s,;=]+)?\s*(?:;\s*q=([0-9.]+))?#',$s,$matches);
		if(!$matches[0]){
			return null;
		}
		$max = 0;
		$lang = null;
		foreach($matches[1] as $key => $value){
			$q = $matches[2][$key] === '' ? 1.0 : (float)$matches[2][$key];
			if($q > $max){
				$max = $q;
				$lang = $value;
			}
		}
		return $lang;
	}
}