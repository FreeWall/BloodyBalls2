<?php
namespace Core\Http;

class Url implements \JsonSerializable {

	public static $defaultPorts = [
		'http' => 80,
		'https' => 443,
		'ftp' => 21,
		'news' => 119,
		'nntp' => 119,
	];
	private $scheme = '';
	private $user = '';
	private $password = '';
	private $host = '';
	private $port;
	private $path = '';
	private $query = [];
	private $fragment = '';

	public function __construct($url = null){
		if(is_string($url)){
			$p = @parse_url($url);
			if($p === false) throw new \RuntimeException("Malformed or unsupported URI '$url'.");
			$this->scheme = $p['scheme'] ?? '';
			$this->port = $p['port'] ?? null;
			$this->host = isset($p['host']) ? rawurldecode($p['host']) : '';
			$this->user = isset($p['user']) ? rawurldecode($p['user']) : '';
			$this->password = isset($p['pass']) ? rawurldecode($p['pass']) : '';
			$this->setPath($p['path'] ?? '');
			$this->setQuery($p['query'] ?? []);
			$this->fragment = isset($p['fragment']) ? rawurldecode($p['fragment']) : '';
		} elseif($url instanceof self) {
			foreach($this as $key => $val){
				$this->$key = $url->$key;
			}
		}
	}

	public function setScheme(string $value){
		$this->scheme = $value;
		return $this;
	}

	public function getScheme():string{
		return $this->scheme;
	}

	public function setUser(string $value){
		$this->user = $value;
		return $this;
	}

	public function getUser():string{
		return $this->user;
	}

	public function setPassword(string $value){
		$this->password = $value;
		return $this;
	}

	public function getPassword():string{
		return $this->password;
	}

	public function setHost(string $value){
		$this->host = $value;
		$this->setPath($this->path);
		return $this;
	}

	public function getHost():string{
		return $this->host;
	}

	public function getDomain(int $level = 2):string{
		$parts = ip2long($this->host) ? [$this->host] : explode('.',$this->host);
		$parts = $level >= 0 ? array_slice($parts,-$level) : array_slice($parts,0,$level);
		return implode('.',$parts);
	}

	public function setPort(int $value){
		$this->port = $value;
		return $this;
	}

	public function getPort():int{
		return $this->port ?: (self::$defaultPorts[$this->scheme] ?? null);
	}

	public function setPath(string $value){
		$this->path = $value;
		if($this->host && substr($this->path,0,1) !== '/'){
			$this->path = '/'.$this->path;
		}
		return $this;
	}

	public function getPath():string{
		return $this->path;
	}

	public function setQuery($value){
		$this->query = is_array($value) ? $value : self::parseQuery($value);
		return $this;
	}

	public function appendQuery($value){
		$this->query = is_array($value)
			? $value + $this->query
			: self::parseQuery($this->getQuery().'&'.$value);
		return $this;
	}

	public function getQuery():string{
		return http_build_query($this->query,'','&',PHP_QUERY_RFC3986);
	}

	public function getQueryParameters():array{
		return $this->query;
	}

	public function getQueryParameter(string $name){
		if(func_num_args() > 1){
			trigger_error(__METHOD__.'() parameter $default is deprecated, use operator ??',E_USER_DEPRECATED);
		}
		return $this->query[$name] ?? null;
	}

	public function setQueryParameter(string $name,$value){
		$this->query[$name] = $value;
		return $this;
	}

	public function setFragment(string $value){
		$this->fragment = $value;
		return $this;
	}

	public function getFragment():string{
		return $this->fragment;
	}

	public function getAbsoluteUrl():string{
		return $this->getHostUrl().$this->path
			.(($tmp = $this->getQuery()) ? '?'.$tmp : '')
			.($this->fragment === '' ? '' : '#'.$this->fragment);
	}

	public function getAuthority():string{
		return $this->host === ''
			? ''
			: ($this->user !== '' && $this->scheme !== 'http' && $this->scheme !== 'https'
				? rawurlencode($this->user).($this->password === '' ? '' : ':'.rawurlencode($this->password)).'@'
				: '')
			.$this->host
			.($this->port && (!isset(self::$defaultPorts[$this->scheme]) || $this->port !== self::$defaultPorts[$this->scheme])
				? ':'.$this->port
				: '');
	}

	public function getHostUrl():string{
		return ($this->scheme ? $this->scheme.':' : '')
			.(($authority = $this->getAuthority()) || $this->scheme ? '//'.$authority : '');
	}

	public function getBasePath():string{
		$pos = strrpos($this->path,'/');
		return $pos === false ? '' : substr($this->path,0,$pos + 1);
	}

	public function getBaseUrl():string{
		return $this->getHostUrl().$this->getBasePath();
	}

	public function getRelativeUrl():string{
		return substr($this->getAbsoluteUrl(),strlen($this->getBaseUrl()));
	}

	public function isEqual($url):bool{
		$url = new self($url);
		$query = $url->query;
		ksort($query);
		$query2 = $this->query;
		ksort($query2);
		$http = in_array($this->scheme,['http','https'],true);
		return $url->scheme === $this->scheme
			&& !strcasecmp($url->host,$this->host)
			&& $url->getPort() === $this->getPort()
			&& ($http || $url->user === $this->user)
			&& ($http || $url->password === $this->password)
			&& self::unescape($url->path,'%/') === self::unescape($this->path,'%/')
			&& $query === $query2
			&& $url->fragment === $this->fragment;
	}

	public function canonicalize(){
		$this->path = preg_replace_callback(
			'#[^!$&\'()*+,/:;=@%]+#',
			function($m){
				return rawurlencode($m[0]);
			},
			self::unescape($this->path,'%/')
		);
		$this->host = strtolower($this->host);
		return $this;
	}

	public function __toString():string{
		return $this->getAbsoluteUrl();
	}

	public function jsonSerialize():string{
		return $this->getAbsoluteUrl();
	}

	public static function unescape(string $s,string $reserved = '%;/?:@&=+$,'):string{
		// reserved (@see RFC 2396) = ";" | "/" | "?" | ":" | "@" | "&" | "=" | "+" | "$" | ","
		// within a path segment, the characters "/", ";", "=", "?" are reserved
		// within a query component, the characters ";", "/", "?", ":", "@", "&", "=", "+", ",", "$" are reserved.
		if($reserved !== ''){
			$s = preg_replace_callback(
				'#%('.substr(chunk_split(bin2hex($reserved),2,'|'),0,-1).')#i',
				function($m){
					return '%25'.strtoupper($m[1]);
				},
				$s
			);
		}
		return rawurldecode($s);
	}

	public static function parseQuery(string $s):array{
		$s = str_replace(['%5B','%5b'],'[',$s);
		$s = preg_replace('#&([^[&=]+)([^&]*)#','&0[$1]$2','&'.$s);
		parse_str($s,$res);
		return $res ? $res[0] : [];
	}
}