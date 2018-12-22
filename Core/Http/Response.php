<?php
namespace Core\Http;

class Response {

	const
		S100_CONTINUE = 100,
		S101_SWITCHING_PROTOCOLS = 101,
		S102_PROCESSING = 102,
		S200_OK = 200,
		S201_CREATED = 201,
		S202_ACCEPTED = 202,
		S203_NON_AUTHORITATIVE_INFORMATION = 203,
		S204_NO_CONTENT = 204,
		S205_RESET_CONTENT = 205,
		S206_PARTIAL_CONTENT = 206,
		S207_MULTI_STATUS = 207,
		S208_ALREADY_REPORTED = 208,
		S226_IM_USED = 226,
		S300_MULTIPLE_CHOICES = 300,
		S301_MOVED_PERMANENTLY = 301,
		S302_FOUND = 302,
		S303_SEE_OTHER = 303,
		S303_POST_GET = 303,
		S304_NOT_MODIFIED = 304,
		S305_USE_PROXY = 305,
		S307_TEMPORARY_REDIRECT = 307,
		S308_PERMANENT_REDIRECT = 308,
		S400_BAD_REQUEST = 400,
		S401_UNAUTHORIZED = 401,
		S402_PAYMENT_REQUIRED = 402,
		S403_FORBIDDEN = 403,
		S404_NOT_FOUND = 404,
		S405_METHOD_NOT_ALLOWED = 405,
		S406_NOT_ACCEPTABLE = 406,
		S407_PROXY_AUTHENTICATION_REQUIRED = 407,
		S408_REQUEST_TIMEOUT = 408,
		S409_CONFLICT = 409,
		S410_GONE = 410,
		S411_LENGTH_REQUIRED = 411,
		S412_PRECONDITION_FAILED = 412,
		S413_REQUEST_ENTITY_TOO_LARGE = 413,
		S414_REQUEST_URI_TOO_LONG = 414,
		S415_UNSUPPORTED_MEDIA_TYPE = 415,
		S416_REQUESTED_RANGE_NOT_SATISFIABLE = 416,
		S417_EXPECTATION_FAILED = 417,
		S421_MISDIRECTED_REQUEST = 421,
		S422_UNPROCESSABLE_ENTITY = 422,
		S423_LOCKED = 423,
		S424_FAILED_DEPENDENCY = 424,
		S426_UPGRADE_REQUIRED = 426,
		S428_PRECONDITION_REQUIRED = 428,
		S429_TOO_MANY_REQUESTS = 429,
		S431_REQUEST_HEADER_FIELDS_TOO_LARGE = 431,
		S451_UNAVAILABLE_FOR_LEGAL_REASONS = 451,
		S500_INTERNAL_SERVER_ERROR = 500,
		S501_NOT_IMPLEMENTED = 501,
		S502_BAD_GATEWAY = 502,
		S503_SERVICE_UNAVAILABLE = 503,
		S504_GATEWAY_TIMEOUT = 504,
		S505_HTTP_VERSION_NOT_SUPPORTED = 505,
		S506_VARIANT_ALSO_NEGOTIATES = 506,
		S507_INSUFFICIENT_STORAGE = 507,
		S508_LOOP_DETECTED = 508,
		S510_NOT_EXTENDED = 510,
		S511_NETWORK_AUTHENTICATION_REQUIRED = 511;


	/** @var string The domain in which the cookie will be available */
	public $cookieDomain = '';
	/** @var string The path in which the cookie will be available */
	public $cookiePath = '/';
	/** @var bool Whether the cookie is available only through HTTPS */
	public $cookieSecure = true;
	/** @var bool Whether the cookie is hidden from client-side */
	public $cookieHttpOnly = true;
	/** @var bool Whether warn on possible problem with data in output buffer */
	public $warnOnBuffer = true;
	/** @var bool  Send invisible garbage for IE 6? */
	private static $fixIE = true;
	/** @var int HTTP response code */
	private $code = self::S200_OK;
	public function __construct()
	{
		if (is_int($code = http_response_code())) {
			$this->code = $code;
		}
	}
	/**
	 * Sets HTTP response code.
	 * @return static
	 * @throws Nette\InvalidArgumentException  if code is invalid
	 * @throws Nette\InvalidStateException  if HTTP headers have been sent
	 */
	public function setCode(int $code, string $reason = null)
	{
		if ($code < 100 || $code > 599) {
			throw new \InvalidArgumentException("Bad HTTP response '$code'.");
		}
		self::checkHeaders();
		$this->code = $code;
		static $hasReason = [ // hardcoded in PHP
			100, 101,
			200, 201, 202, 203, 204, 205, 206,
			300, 301, 302, 303, 304, 305, 307, 308,
			400, 401, 402, 403, 404, 405, 406, 407, 408, 409, 410, 411, 412, 413, 414, 415, 416, 417, 426, 428, 429, 431,
			500, 501, 502, 503, 504, 505, 506, 511,
		];
		if ($reason || !in_array($code, $hasReason, true)) {
			$protocol = $_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.1';
			header("$protocol $code " . ($reason ?: 'Unknown status'));
		} else {
			http_response_code($code);
		}
		return $this;
	}
	/**
	 * Returns HTTP response code.
	 */
	public function getCode(): int
	{
		return $this->code;
	}
	/**
	 * Sends a HTTP header and replaces a previous one.
	 * @return static
	 * @throws Nette\InvalidStateException  if HTTP headers have been sent
	 */
	public function setHeader(string $name,string $value = null)
	{
		self::checkHeaders();
		if ($value === null) {
			header_remove($name);
		} elseif (strcasecmp($name, 'Content-Length') === 0 && ini_get('zlib.output_compression')) {
			// ignore, PHP bug #44164
		} else {
			header($name . ': ' . $value, true, $this->code);
		}
		return $this;
	}
	/**
	 * Adds HTTP header.
	 * @return static
	 * @throws Nette\InvalidStateException  if HTTP headers have been sent
	 */
	public function addHeader(string $name, string $value)
	{
		self::checkHeaders();
		header($name . ': ' . $value, false, $this->code);
		return $this;
	}
	/**
	 * Sends a Content-type HTTP header.
	 * @return static
	 * @throws Nette\InvalidStateException  if HTTP headers have been sent
	 */
	public function setContentType(string $type, string $charset = null)
	{
		$this->setHeader('Content-Type', $type . ($charset ? '; charset=' . $charset : ''));
		return $this;
	}
	/**
	 * Redirects to a new URL. Note: call exit() after it.
	 * @throws Nette\InvalidStateException  if HTTP headers have been sent
	 */
	public function redirect(string $url, int $code = self::S302_FOUND)
	{
		$this->setCode($code);
		$this->setHeader('Location', $url);
		exit();
	}
	/**
	 * Sets the time (like '20 minutes') before a page cached on a browser expires, null means "must-revalidate".
	 * @return static
	 * @throws Nette\InvalidStateException  if HTTP headers have been sent
	 */
	public function setExpiration(string $time = null)
	{
		$this->setHeader('Pragma', null);
		if (!$time) { // no cache
			$this->setHeader('Cache-Control', 's-maxage=0, max-age=0, must-revalidate');
			$this->setHeader('Expires', 'Mon, 23 Jan 1978 10:00:00 GMT');
			return $this;
		}
		$time = DateTime::from($time);
		$this->setHeader('Cache-Control', 'max-age=' . ($time->format('U') - time()));
		$this->setHeader('Expires', Helpers::formatDate($time));
		return $this;
	}
	/**
	 * Checks if headers have been sent.
	 */
	public function isSent(): bool
	{
		return headers_sent();
	}
	/**
	 * Returns value of an HTTP header.
	 */
	public function getHeader(string $header):string
	{
		if (func_num_args() > 1) {
			trigger_error(__METHOD__ . '() parameter $default is deprecated, use operator ??', E_USER_DEPRECATED);
		}
		$header .= ':';
		$len = strlen($header);
		foreach (headers_list() as $item) {
			if (strncasecmp($item, $header, $len) === 0) {
				return ltrim(substr($item, $len));
			}
		}
		return null;
	}
	/**
	 * Returns a associative array of headers to sent.
	 */
	public function getHeaders(): array
	{
		$headers = [];
		foreach (headers_list() as $header) {
			$a = strpos($header, ':');
			$headers[substr($header, 0, $a)] = (string) substr($header, $a + 2);
		}
		return $headers;
	}
	public function __destruct()
	{
		if (
			self::$fixIE
			&& isset($_SERVER['HTTP_USER_AGENT'])
			&& strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE ') !== false
			&& in_array($this->code, [400, 403, 404, 405, 406, 408, 409, 410, 500, 501, 505], true)
			&& preg_match('#^text/html(?:;|$)#', $this->getHeader('Content-Type'))
		) {
			//echo Nette\Utils\Random::generate(2000, " \t\r\n"); // sends invisible garbage for IE
			self::$fixIE = false;
		}
	}
	/**
	 * Sends a cookie.
	 * @param  string|int|\DateTimeInterface $time  expiration time, value 0 means "until the browser is closed"
	 * @return static
	 * @throws Nette\InvalidStateException  if HTTP headers have been sent
	 */
	public function setCookie(string $name, string $value, $time, string $path = null, string $domain = null, bool $secure = null, bool $httpOnly = null, string $sameSite = null)
	{
		self::checkHeaders();
		$options = [
			'expires' => $time ? (int) time()+date('U',$time) : 0,
			'path' => $path === null ? $this->cookiePath : $path,
			'domain' => $domain === null ? $this->cookieDomain : $domain,
			'secure' => $secure === null ? $this->cookieSecure : $secure,
			'httponly' => $httpOnly === null ? $this->cookieHttpOnly : $httpOnly,
			'samesite' => $sameSite,
		];
		if (PHP_VERSION_ID >= 70300) {
			setcookie($name, $value, $options);
		} else {
			setcookie(
				$name,
				$value,
				$options['expires'],
				$options['path'] . ($sameSite ? "; SameSite=$sameSite" : ''),
				$options['domain'],
				$options['secure'],
				$options['httponly']
			);
		}
		return $this;
	}
	/**
	 * Deletes a cookie.
	 * @throws Nette\InvalidStateException  if HTTP headers have been sent
	 */
	public function deleteCookie(string $name, string $path = null, string $domain = null, bool $secure = null)
	{
		$this->setCookie($name, '', 0, $path, $domain, $secure);
	}
	private function checkHeaders()
	{
		if (PHP_SAPI === 'cli') {
		} elseif (headers_sent($file, $line)) {
			throw new Nette\InvalidStateException('Cannot send header after HTTP headers have been sent' . ($file ? " (output running at $file:$line)." : '.'));
		} elseif ($this->warnOnBuffer && ob_get_length() && !array_filter(ob_get_status(true), function ($i) { return !$i['chunk_size']; })) {
			trigger_error('Possible problem: you are sending a HTTP header while already having some data in output buffer. Try Tracy\OutputDebugger or start session earlier.');
		}
	}
}