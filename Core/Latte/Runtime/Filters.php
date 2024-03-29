<?php

/**
 * This file is part of the Latte (https://latte.nette.org)
 * Copyright (c) 2008 David Grudl (https://davidgrudl.com)
 */

namespace Latte\Runtime;

use Latte;
use Latte\Engine;


/**
 * Template filters. Uses UTF-8 only.
 * @internal
 */
class Filters
{
	/** @deprecated */
	public static $dateFormat = 'j.n.Y';

	/** @internal @var bool  use XHTML syntax? */
	public static $xhtml = false;


	/**
	 * Escapes string for use inside HTML.
	 * @param  mixed  plain text
	 * @return string HTML
	 */
	public static function escapeHtml($s)
	{
		return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
	}


	/**
	 * Escapes string for use inside HTML.
	 * @param  mixed  plain text or IHtmlString
	 * @return string HTML
	 */
	public static function escapeHtmlText($s)
	{
		return $s instanceof IHtmlString || $s instanceof \Forms\Utils\IHtmlString
			? $s->__toString(true)
			: htmlspecialchars((string) $s, ENT_NOQUOTES, 'UTF-8');
	}


	/**
	 * Escapes string for use inside HTML attribute value.
	 * @param  string plain text
	 * @return string HTML
	 */
	public static function escapeHtmlAttr($s, $double = true)
	{
		$double = $double && $s instanceof IHtmlString ? false : $double;
		$s = (string) $s;
		if (strpos($s, '`') !== false && strpbrk($s, ' <>"\'') === false) {
			$s .= ' '; // protection against innerHTML mXSS vulnerability nette/nette#1496
		}
		return htmlspecialchars($s, ENT_QUOTES, 'UTF-8', $double);
	}


	/**
	 * Escapes HTML for use inside HTML attribute.
	 * @param  mixed  HTML text
	 * @return string HTML
	 */
	public static function escapeHtmlAttrConv($s)
	{
		return self::escapeHtmlAttr($s, false);
	}


	/**
	 * Escapes string for use inside HTML attribute name.
	 * @param  string plain text
	 * @return string HTML
	 */
	public static function escapeHtmlAttrUnquoted($s)
	{
		$s = (string) $s;
		return preg_match('#^[a-z0-9:-]+$#i', $s)
			? $s
			: '"' . self::escapeHtmlAttr($s) . '"';
	}


	/**
	 * Escapes string for use inside HTML comments.
	 * @param  string plain text
	 * @return string HTML
	 */
	public static function escapeHtmlComment($s)
	{
		$s = (string) $s;
		if ($s && ($s[0] === '-' || $s[0] === '>' || $s[0] === '!')) {
			$s = ' ' . $s;
		}
		$s = str_replace('--', '- - ', $s);
		if (substr($s, -1) === '-') {
			$s .= ' ';
		}
		return $s;
	}


	/**
	 * Escapes string for use inside XML 1.0 template.
	 * @param  string plain text
	 * @return string XML
	 */
	public static function escapeXml($s)
	{
		// XML 1.0: \x09 \x0A \x0D and C1 allowed directly, C0 forbidden
		// XML 1.1: \x00 forbidden directly and as a character reference,
		//   \x09 \x0A \x0D \x85 allowed directly, C0, C1 and \x7F allowed as character references
		return htmlspecialchars(preg_replace('#[\x00-\x08\x0B\x0C\x0E-\x1F]+#', '', (string) $s), ENT_QUOTES, 'UTF-8');
	}


	/**
	 * Escapes string for use inside XML attribute name.
	 * @param  string plain text
	 * @return string XML
	 */
	public static function escapeXmlAttrUnquoted($s)
	{
		$s = (string) $s;
		return preg_match('#^[a-z0-9:-]+$#i', $s)
			? $s
			: '"' . self::escapeXml($s) . '"';
	}


	/**
	 * Escapes string for use inside CSS template.
	 * @param  string plain text
	 * @return string CSS
	 */
	public static function escapeCss($s)
	{
		// http://www.w3.org/TR/2006/WD-CSS21-20060411/syndata.html#q6
		return addcslashes((string) $s, "\x00..\x1F!\"#$%&'()*+,./:;<=>?@[\\]^`{|}~");
	}


	/**
	 * Escapes variables for use inside <script>.
	 * @param  mixed  plain text
	 * @return string JSON
	 */
	public static function escapeJs($s)
	{
		if ($s instanceof IHtmlString || $s instanceof \Forms\Utils\IHtmlString) {
			$s = $s->__toString(true);
		}

		$json = json_encode($s, JSON_UNESCAPED_UNICODE);
		if ($error = json_last_error()) {
			throw new \RuntimeException(PHP_VERSION_ID >= 50500 ? json_last_error_msg() : 'JSON encode error', $error);
		}

		return str_replace(["\xe2\x80\xa8", "\xe2\x80\xa9", ']]>', '<!'], ['\u2028', '\u2029', ']]\x3E', '\x3C!'], $json);
	}


	/**
	 * Escapes string for use inside iCal template.
	 * @param  string plain text
	 * @return string
	 */
	public static function escapeICal($s)
	{
		// https://www.ietf.org/rfc/rfc5545.txt
		return addcslashes(preg_replace('#[\x00-\x08\x0B\x0C-\x1F]+#', '', (string) $s), "\";\\,:\n");
	}


	/**
	 * Escapes CSS/JS for usage in <script> and <style>..
	 * @param  string CSS/JS
	 * @return string HTML RAWTEXT
	 */
	public static function escapeHtmlRawText($s)
	{
		return preg_replace('#</(script|style)#i', '<\\/$1', (string) $s);
	}


	/**
	 * Converts HTML to plain text.
	 * @param
	 * @param  string HTML
	 * @return string plain text
	 */
	public static function stripHtml(FilterInfo $info, $s)
	{
		if (!in_array($info->contentType, [null, 'html', 'xhtml', 'htmlAttr', 'xhtmlAttr', 'xml', 'xmlAttr'], true)) {
			trigger_error('Filter |stripHtml used with incompatible type ' . strtoupper($info->contentType), E_USER_WARNING);
		}
		$info->contentType = Engine::CONTENT_TEXT;
		return html_entity_decode(strip_tags((string) $s), ENT_QUOTES, 'UTF-8');
	}


	/**
	 * Removes tags from HTML (but remains HTML entites).
	 * @param
	 * @param  string HTML
	 * @return string HTML
	 */
	public static function stripTags(FilterInfo $info, $s)
	{
		if (!in_array($info->contentType, [null, 'html', 'xhtml', 'htmlAttr', 'xhtmlAttr', 'xml', 'xmlAttr'], true)) {
			trigger_error('Filter |stripTags used with incompatible type ' . strtoupper($info->contentType), E_USER_WARNING);
		}
		return strip_tags((string) $s);
	}


	/**
	 * Converts ... to ...
	 * @return string
	 */
	public static function convertTo(FilterInfo $info, $dest, $s)
	{
		$source = $info->contentType ?: Engine::CONTENT_TEXT;
		if ($source === $dest) {
			return $s;
		} elseif ($conv = self::getConvertor($source, $dest)) {
			$info->contentType = $dest;
			return $conv($s);
		} else {
			trigger_error('Filters: unable to convert content type ' . strtoupper($source) . ' to ' . strtoupper($dest), E_USER_WARNING);
			return $s;
		}
	}


	/**
	 * @return callable|null
	 */
	public static function getConvertor($source, $dest)
	{
		static $table = [
			Engine::CONTENT_TEXT => [
				'html' => 'escapeHtmlText', 'xhtml' => 'escapeHtmlText',
				'htmlAttr' => 'escapeHtmlAttr', 'xhtmlAttr' => 'escapeHtmlAttr',
				'htmlAttrJs' => 'escapeHtmlAttr', 'xhtmlAttrJs' => 'escapeHtmlAttr',
				'htmlAttrCss' => 'escapeHtmlAttr', 'xhtmlAttrCss' => 'escapeHtmlAttr',
				'htmlAttrUrl' => 'escapeHtmlAttr', 'xhtmlAttrUrl' => 'escapeHtmlAttr',
				'htmlComment' => 'escapeHtmlComment', 'xhtmlComment' => 'escapeHtmlComment',
				'xml' => 'escapeXml', 'xmlAttr' => 'escapeXml',
			],
			Engine::CONTENT_JS => [
				'html' => 'escapeHtmlText', 'xhtml' => 'escapeHtmlText',
				'htmlAttr' => 'escapeHtmlAttr', 'xhtmlAttr' => 'escapeHtmlAttr',
				'htmlAttrJs' => 'escapeHtmlAttr', 'xhtmlAttrJs' => 'escapeHtmlAttr',
				'htmlJs' => 'escapeHtmlRawText', 'xhtmlJs' => 'escapeHtmlRawText',
				'htmlComment' => 'escapeHtmlComment', 'xhtmlComment' => 'escapeHtmlComment',
			],
			Engine::CONTENT_CSS => [
				'html' => 'escapeHtmlText', 'xhtml' => 'escapeHtmlText',
				'htmlAttr' => 'escapeHtmlAttr', 'xhtmlAttr' => 'escapeHtmlAttr',
				'htmlAttrCss' => 'escapeHtmlAttr', 'xhtmlAttrCss' => 'escapeHtmlAttr',
				'htmlCss' => 'escapeHtmlRawText', 'xhtmlCss' => 'escapeHtmlRawText',
				'htmlComment' => 'escapeHtmlComment', 'xhtmlComment' => 'escapeHtmlComment',
			],
			Engine::CONTENT_HTML => [
				'htmlAttr' => 'escapeHtmlAttrConv',
				'htmlAttrJs' => 'escapeHtmlAttrConv',
				'htmlAttrCss' => 'escapeHtmlAttrConv',
				'htmlAttrUrl' => 'escapeHtmlAttrConv',
				'htmlComment' => 'escapeHtmlComment',
			],
			Engine::CONTENT_XHTML => [
				'xhtmlAttr' => 'escapeHtmlAttrConv',
				'xhtmlAttrJs' => 'escapeHtmlAttrConv',
				'xhtmlAttrCss' => 'escapeHtmlAttrConv',
				'xhtmlAttrUrl' => 'escapeHtmlAttrConv',
				'xhtmlComment' => 'escapeHtmlComment',
			],
		];
		return isset($table[$source][$dest]) ? [__CLASS__, $table[$source][$dest]] : null;
	}


	/**
	 * Sanitizes string for use inside href attribute.
	 * @param  string plain text
	 * @return string plain text
	 */
	public static function safeUrl($s)
	{
		$s = (string) $s;
		return preg_match('~^(?:(?:https?|ftp)://[^@]+(?:/.*)?|mailto:.+|[/?#].*|[^:]+)\z~i', $s) ? $s : '';
	}


	/**
	 * Replaces all repeated white spaces with a single space.
	 * @param
	 * @param  string text|HTML
	 * @return string text|HTML
	 */
	public static function strip(FilterInfo $info, $s)
	{
		return in_array($info->contentType, [Engine::CONTENT_HTML, Engine::CONTENT_XHTML], true)
			? trim(self::spacelessHtml($s))
			: trim(self::spacelessText($s));
	}


	/**
	 * Replaces all repeated white spaces with a single space.
	 * @param  string HTML
	 * @param  int output buffering phase
	 * @param  bool stripping mode
	 * @return string HTML
	 */
	public static function spacelessHtml($s, $phase = null, &$strip = true)
	{
		if ($phase & PHP_OUTPUT_HANDLER_START) {
			$s = ltrim($s);
		}
		if ($phase & PHP_OUTPUT_HANDLER_FINAL) {
			$s = rtrim($s);
		}
		return preg_replace_callback(
			'#[ \t\r\n]+|<(/)?(textarea|pre|script)(?=\W)#si',
			function ($m) use (&$strip) {
				if (empty($m[2])) {
					return $strip ? ' ' : $m[0];
				} else {
					$strip = !empty($m[1]);
					return $m[0];
				}
			},
			$s
		);
	}


	/**
	 * Replaces all repeated white spaces with a single space.
	 * @param  string text
	 * @return string text
	 */
	public static function spacelessText($s)
	{
		return preg_replace('#[ \t\r\n]+#', ' ', $s);
	}


	/**
	 * Indents the content from the left.
	 * @param
	 * @param  string text|HTML
	 * @param  int
	 * @param  string
	 * @return string text|HTML
	 */
	public static function indent(FilterInfo $info, $s, $level = 1, $chars = "\t")
	{
		if ($level < 1) {
			// do nothing
		} elseif (in_array($info->contentType, [Engine::CONTENT_HTML, Engine::CONTENT_XHTML], true)) {
			$s = preg_replace_callback('#<(textarea|pre).*?</\\1#si', function ($m) {
				return strtr($m[0], " \t\r\n", "\x1F\x1E\x1D\x1A");
			}, $s);
			if (preg_last_error()) {
				throw new Latte\RegexpException(null, preg_last_error());
			}
			$s = preg_replace('#(?:^|[\r\n]+)(?=[^\r\n])#', '$0' . str_repeat($chars, $level), $s);
			$s = strtr($s, "\x1F\x1E\x1D\x1A", " \t\r\n");
		} else {
			$s = preg_replace('#(?:^|[\r\n]+)(?=[^\r\n])#', '$0' . str_repeat($chars, $level), $s);
		}
		return $s;
	}

	/**
	 * Inflect string by number
	 * @param  int
	 * @param  string
	 * @param  string
	 * @param  string
	 * @return string text
	 */
	public static function inflect($number, $inflect1, $inflect2, $inflect3)
	{
		if(intval($number) == 1) return $inflect1;
		else if(intval($number) >= 2 && intval($number) <= 4) return $inflect2;
		return $inflect3;
	}


	/**
	 * Array to JSON
	 * @param  array
	 * @return string text
	 */
	public static function json($s)
	{
		return json_encode($s);
	}


	/**
	 * String to base64
	 * @param  string
	 * @return string text
	 */
	public static function base64($s)
	{
		return base64_encode($s);
	}


	/**
	 * Repeats text.
	 * @param
	 * @param  string
	 * @param  int
	 * @return string plain text
	 */
	public static function repeat(FilterInfo $info, $s, $count)
	{
		return str_repeat((string) $s, $count);
	}


	/**
	 * Date/time formatting.
	 * @param  string|int|\DateTime|\DateTimeInterface|\DateInterval
	 * @param  string
	 * @return string|null
	 */
	public static function date($time, $format = null)
	{
		if ($time == null) { // intentionally ==
			return null;
		}

		if (!isset($format)) {
			$format = self::$dateFormat;
		}

		if ($time instanceof \DateInterval) {
			return $time->format($format);

		} elseif (is_numeric($time)) {
			$time = new \DateTime('@' . $time);
			$time->setTimeZone(new \DateTimeZone(date_default_timezone_get()));

		} elseif (!$time instanceof \DateTime && !$time instanceof \DateTimeInterface) {
			$time = new \DateTime($time);
		}
		return strpos($format, '%') === false
			? $time->format($format) // formats using date()
			: strftime($format, $time->format('U') + 0); // formats according to locales
	}


	/**
	 * Date/time formatting.
	 * @param  string|int|\DateTime|\DateTimeInterface|\DateInterval
	 * @param  string
	 * @return string|null
	 */
	public static function dateTime($time, $format = null)
	{
		return self::date($time,$format ?? "j.n.Y H:i");
	}


	private static $timeFormat = array(
		0 => array("Před","sek","min","hod","Včera",array("dny","dny")),
		1 => array("Za","sek","min","hod","Zítra",array("dní","dny"))
	);
	/**
	 * Last time formatting.
	 * @param  string|int
	 * @param  bool lower
	 * @param  array
	 * @return Html
	 */
	public static function lastTime($timestamp = 0,$lower = false,$prefix = array("Před","Za")){
		$date = "";
		if($timestamp == 0) return new Html("<span class='lasttime'>Nikdy</span>");
		$time = time()-$timestamp;
		$past = ($time >= 0 ? true : false);
		if($time == 0) return new Html("<span class='lasttime' title='".date("d.m.Y \\v H:i",$timestamp)."'>Právě teď</span>");
		$format = ($time >= 0 ? self::$timeFormat[0] : self::$timeFormat[1]);
		$prefix = ($time >= 0 ? $prefix[0] : $prefix[1]);
		$time = abs($time);
		if($time < 3600){
			$min = floor($time/60)%60;
			$sec = ceil($time%60);
			if($min == 0) $date = $prefix." ".$sec." ".$format[1];
			else if($min > 0) $date = $prefix." ".$min." ".$format[2];
		}
		else if($time < 86400*2){
			if($past == true){
				$yesterday = strtotime("yesterday");
				if($timestamp >= $yesterday && $timestamp < $yesterday+86400) $date = $format[4]." v ".date("H:i",$timestamp);
				else {
					$hod = floor($time/3600);
					if($hod < 24) $date = $prefix." ".$hod." ".$format[3];
					else $date = $prefix." 2 dny";
				}
			} else {
				$tomorrow = strtotime("tomorrow");
				if($timestamp >= $tomorrow && $timestamp < $tomorrow+86400) $date = $format[4]." v ".date("H:i",$timestamp);
				else {
					$hod = floor($time/3600);
					if($hod < 24) $date = $prefix." ".$hod." ".$format[3];
					else $date = $prefix." 2 dny";
				}
			}
		}
		else {
			$day = floor($time/86400);
			$date = $prefix." ".$day." ".$format[5][($day < 5 ? 1 : 0)];
		}
		$date = "<span class='lasttime' title='".date("d.m.Y \\v H:i",$timestamp)."'>".($lower ? mb_strtolower($date) : $date)."</span>";
		return new Html($date);
	}


	/**
	 * Converts to human readable file size.
	 * @param  float
	 * @param  int
	 * @return string plain text
	 */
	public static function bytes($bytes, $precision = 2)
	{
		$bytes = round($bytes);
		$units = ['B', 'kB', 'MB', 'GB', 'TB', 'PB'];
		foreach ($units as $unit) {
			if (abs($bytes) < 1024 || $unit === end($units)) {
				break;
			}
			$bytes = $bytes / 1024;
		}
		return round($bytes, $precision) . ' ' . $unit;
	}


	/**
	 * Performs a search and replace.
	 * @param
	 * @param  string
	 * @param  string
	 * @param  string
	 * @return string
	 */
	public static function replace(FilterInfo $info, $subject, $search, $replacement = '')
	{
		return str_replace($search, $replacement, (string) $subject);
	}


	/**
	 * Perform a regular expression search and replace.
	 * @param  string
	 * @param  string
	 * @return string
	 */
	public static function replaceRe($subject, $pattern, $replacement = '')
	{
		$res = preg_replace($pattern, $replacement, $subject);
		if (preg_last_error()) {
			throw new Latte\RegexpException(null, preg_last_error());
		}
		return $res;
	}


	/**
	 * The data: URI generator.
	 * @param  string plain text
	 * @param  string
	 * @return string plain text
	 */
	public static function dataStream($data, $type = null)
	{
		if ($type === null) {
			$type = finfo_buffer(finfo_open(FILEINFO_MIME_TYPE), $data);
		}
		return 'data:' . ($type ? "$type;" : '') . 'base64,' . base64_encode($data);
	}


	/**
	 * @param  string
	 * @return string
	 */
	public static function nl2br($value)
	{
		trigger_error('Filter |nl2br is deprecated, use |breaklines which correctly handles escaping.', E_USER_DEPRECATED);
		return nl2br($value, self::$xhtml);
	}


	/**
	 * @param  int
	 * @param  int
	 * @param  string
	 * @param  string
	 * @return string
	 */
	public static function number($value,int $decimals = 0,string $dec_point = ' ',string $thousands_sep = ' ')
	{
		return number_format($value,$decimals,$dec_point,$thousands_sep);
	}


	/**
	 * @param  string plain text
	 * @return Html
	 */
	public static function breaklines($s)
	{
		return new Html(nl2br(htmlspecialchars((string) $s, ENT_NOQUOTES, 'UTF-8'), self::$xhtml));
	}


	/**
	 * Returns a part of string.
	 * @param  string
	 * @param  int
	 * @param  int
	 * @return string
	 */
	public static function substring($s, $start, $length = null)
	{
		$s = (string) $s;
		if ($length === null) {
			$length = self::strLength($s);
		}
		if (function_exists('mb_substr')) {
			return mb_substr($s, $start, $length, 'UTF-8'); // MB is much faster
		}
		return iconv_substr($s, $start, $length, 'UTF-8');
	}


	/**
	 * Truncates string to maximal length.
	 * @param  string plain text
	 * @param  int
	 * @param  string plain text
	 * @return string plain text
	 */
	public static function truncate($s, $maxLen, $append = "\xE2\x80\xA6")
	{
		$s = (string) $s;
		if (self::strLength($s) > $maxLen) {
			$maxLen = $maxLen - self::strLength($append);
			if ($maxLen < 1) {
				return $append;

			} elseif (preg_match('#^.{1,' . $maxLen . '}(?=[\s\x00-/:-@\[-`{-~])#us', $s, $matches)) {
				return $matches[0] . $append;

			} else {
				return self::substring($s, 0, $maxLen) . $append;
			}
		}
		return $s;
	}

	/**
	 * Truncates string to length to the closest word.
	 * @param  string plain text
	 * @param  int
	 * @param  string plain text
	 * @return string plain text
	 */
	public static function wordtruncate($s, $maxLen, $append = "\xE2\x80\xA6"){
		$parts = preg_split('/([\s\n\r]+)/', $s, null, PREG_SPLIT_DELIM_CAPTURE);
		$parts_count = count($parts);
		$length = 0;
		$last_part = 0;
		for(;$last_part<$parts_count;++$last_part){
			$length += strlen($parts[$last_part]);
			if($length > $maxLen){break;}
		}
		return implode(array_slice($parts,0,$last_part)).($length > $maxLen ? $append : "");
	}


	/**
	 * Formats file size
	 * @param  string bytes
	 * @param  int precision
	 * @return string plain text
	 */
	public static function fileSize($size,$precision = 1){
		if($size == 0) return "0 B";
		if($size < 1024) return "$size B";
		$size /= 1024;
		if($size < 1024) return round($size,$precision)." kB";
		$size /= 1024;
		if($size < 1024) return round($size,$precision)." MB";
		$size /= 1024;
		return round($size,$precision)." GB";
	}


	/**
	 * Convert to lower case.
	 * @param  string plain text
	 * @return string plain text
	 */
	public static function lower($s)
	{
		return mb_strtolower((string) $s, 'UTF-8');
	}


	/**
	 * Convert to upper case.
	 * @param  string plain text
	 * @return string plain text
	 */
	public static function upper($s)
	{
		return mb_strtoupper((string) $s, 'UTF-8');
	}


	/**
	 * Convert first character to upper case.
	 * @param  string plain text
	 * @return string plain text
	 */
	public static function firstUpper($s)
	{
		$s = (string) $s;
		return self::upper(self::substring($s, 0, 1)) . self::substring($s, 1);
	}


	/**
	 * Capitalize string.
	 * @param  string plain text
	 * @return string plain text
	 */
	public static function capitalize($s)
	{
		return mb_convert_case((string) $s, MB_CASE_TITLE, 'UTF-8');
	}


	/**
	 * Returns length of string or iterable.
	 * @param  array|\Countable|\Traversable|string
	 * @return int
	 */
	public static function length($val)
	{
		if (is_array($val) || $val instanceof \Countable) {
			return count($val);
		} elseif ($val instanceof \Traversable) {
			return iterator_count($val);
		} else {
			return self::strLength($val);
		}
	}


	/**
	 * @param  string
	 * @return int
	 */
	private static function strLength($s)
	{
		return function_exists('mb_strlen') ? mb_strlen($s, 'UTF-8') : strlen(utf8_decode($s));
	}


	/**
	 * Strips whitespace.
	 * @param  string
	 * @param  string
	 * @return string
	 */
	public static function trim(FilterInfo $info, $s, $charlist = " \t\n\r\0\x0B\xC2\xA0")
	{
		$charlist = preg_quote($charlist, '#');
		$s = preg_replace('#^[' . $charlist . ']+|[' . $charlist . ']+\z#u', '', (string) $s);
		if (preg_last_error()) {
			throw new Latte\RegexpException(null, preg_last_error());
		}
		return $s;
	}


	/**
	 * Pad a string to a certain length with another string.
	 * @param  string plain text
	 * @param  int
	 * @param  string
	 * @return string
	 */
	public static function padLeft($s, $length, $pad = ' ')
	{
		$s = (string) $s;
		$length = max(0, $length - self::strLength($s));
		$padLen = self::strLength($pad);
		return str_repeat($pad, (int) ($length / $padLen)) . self::substring($pad, 0, $length % $padLen) . $s;
	}


	/**
	 * Pad a string to a certain length with another string.
	 * @param  string plain text
	 * @param  int
	 * @param  string
	 * @return string
	 */
	public static function padRight($s, $length, $pad = ' ')
	{
		$s = (string) $s;
		$length = max(0, $length - self::strLength($s));
		$padLen = self::strLength($pad);
		return $s . str_repeat($pad, (int) ($length / $padLen)) . self::substring($pad, 0, $length % $padLen);
	}


	/**
	 * Reverses string or array.
	 * @param  string|array|\Traversable
	 */
	public static function reverse($val, $preserveKeys = false)
	{
		if (is_array($val)) {
			return array_reverse($val, $preserveKeys);
		} elseif ($val instanceof \Traversable) {
			return array_reverse(iterator_to_array($val), $preserveKeys);
		} else {
			return iconv('UTF-32LE', 'UTF-8', strrev(iconv('UTF-8', 'UTF-32BE', (string) $val)));
		}
	}


	/**
	 * Returns element's attributes.
	 * @return string
	 */
	public static function htmlAttributes($attrs)
	{
		if (!is_array($attrs)) {
			return '';
		}

		$s = '';
		foreach ($attrs as $key => $value) {
			if ($value === null || $value === false) {
				continue;

			} elseif ($value === true) {
				if (static::$xhtml) {
					$s .= ' ' . $key . '="' . $key . '"';
				} else {
					$s .= ' ' . $key;
				}
				continue;

			} elseif (is_array($value)) {
				$tmp = null;
				foreach ($value as $k => $v) {
					if ($v != null) { // intentionally ==, skip nulls & empty string
						//  composite 'style' vs. 'others'
						$tmp[] = $v === true ? $k : (is_string($k) ? $k . ':' . $v : $v);
					}
				}
				if ($tmp === null) {
					continue;
				}

				$value = implode($key === 'style' || !strncmp($key, 'on', 2) ? ';' : ' ', $tmp);

			} else {
				$value = (string) $value;
			}

			$q = strpos($value, '"') === false ? '"' : "'";
			$s .= ' ' . $key . '=' . $q
				. str_replace(
					['&', $q, '<'],
					['&amp;', $q === '"' ? '&quot;' : '&#39;', self::$xhtml ? '&lt;' : '<'],
					$value
				)
				. (strpos($value, '`') !== false && strpbrk($value, ' <>"\'') === false ? ' ' : '')
				. $q;
		}
		return $s;
	}
}
