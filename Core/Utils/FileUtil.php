<?php
namespace Core\Utils;

class FileUtil {

	public static function getSize(int $size,$precision = 1):string {
		if($size == 0) return "0 B";
		if($size < 1024) return "$size B";
		$size /= 1024;
		if($size < 1024) return round($size,$precision)." kB";
		$size /= 1024;
		if($size < 1024) return round($size,$precision)." MB";
		$size /= 1024;
		return round($size,$precision)." GB";
	}

	public static function getSuffix(string $value):string {
		return pathinfo($value,PATHINFO_EXTENSION);
	}

	public static function getMimeType(string $path):string {
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$type = finfo_file($finfo,$path);
		finfo_close($finfo);
		return $type;
	}

	public static function getRemoteFile(string $url):string {
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,10);
		curl_setopt($ch,CURLOPT_TIMEOUT,10);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch,CURLOPT_FOLLOWLOCATION,true);
		curl_setopt($ch,CURLOPT_MAXREDIRS,3);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
		$content = curl_exec($ch);
		curl_close($ch);
		return $content;
	}

	public static function getRemoteSize(string $url):int {
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,10);
		curl_setopt($ch,CURLOPT_TIMEOUT,10);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch,CURLOPT_FOLLOWLOCATION,true);
		curl_setopt($ch,CURLOPT_MAXREDIRS,3);
		curl_setopt($ch,CURLOPT_NOBODY,true);
		curl_setopt($ch,CURLOPT_HEADER,true);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
		$content = curl_exec($ch);
		curl_close($ch);
		$length = -1;
		if($content){
			$status = null;
			if(preg_match("/^HTTP\/1\.[01] (\d\d\d)/",$content,$matches)){
				$status = (int)$matches[1];
			}
			if($status == 200 || ($status > 300 && $status <= 308)){
				if(preg_match("/Content-Length: (\d+)/",$content,$matches)){
					$length = (int)$matches[1];
				}
			}
		}
		return $length;
	}

	/**
	 * Creates a directory.
	 * @throws \Exception
	 */
	public static function createDir(string $dir, int $mode = 0777): void
	{
		if (!is_dir($dir) && !@mkdir($dir, $mode, true) && !is_dir($dir)) { // @ - dir may already exist
			throw new \Exception("Unable to create directory '$dir'. " . self::getLastError());
		}
	}


	/**
	 * Copies a file or directory.
	 * @throws \Exception
	 */
	public static function copy(string $source, string $dest, bool $overwrite = true): void
	{
		if (stream_is_local($source) && !file_exists($source)) {
			throw new \Exception("File or directory '$source' not found.");

		} elseif (!$overwrite && file_exists($dest)) {
			throw new \Exception("File or directory '$dest' already exists.");

		} elseif (is_dir($source)) {
			static::createDir($dest);
			foreach (new \FilesystemIterator($dest) as $item) {
				static::delete($item->getPathname());
			}
			foreach ($iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST) as $item) {
				if ($item->isDir()) {
					static::createDir($dest . '/' . $iterator->getSubPathName());
				} else {
					static::copy($item->getPathname(), $dest . '/' . $iterator->getSubPathName());
				}
			}

		} else {
			static::createDir(dirname($dest));
			if (($s = @fopen($source, 'r')) && ($d = @fopen($dest, 'w')) && @stream_copy_to_stream($s, $d) === false) { // @ is escalated to exception
				throw new \Exception("Unable to copy file '$source' to '$dest'. " . self::getLastError());
			}
		}
	}


	/**
	 * Deletes a file or directory.
	 * @throws \Exception
	 */
	public static function delete(string $path): void
	{
		if (is_file($path) || is_link($path)) {
			$func = DIRECTORY_SEPARATOR === '\\' && is_dir($path) ? 'rmdir' : 'unlink';
			if (!@$func($path)) { // @ is escalated to exception
				throw new \Exception("Unable to delete '$path'. " . self::getLastError());
			}

		} elseif (is_dir($path)) {
			foreach (new \FilesystemIterator($path) as $item) {
				static::delete($item->getPathname());
			}
			if (!@rmdir($path)) { // @ is escalated to exception
				throw new \Exception("Unable to delete directory '$path'. " . self::getLastError());
			}
		}
	}


	/**
	 * Renames a file or directory.
	 * @throws \Exception
	 * @throws \Exception() if the target file or directory already exist
	 */
	public static function rename(string $name, string $newName, bool $overwrite = true): void
	{
		if (!$overwrite && file_exists($newName)) {
			throw new \Exception("File or directory '$newName' already exists.");

		} elseif (!file_exists($name)) {
			throw new \Exception("File or directory '$name' not found.");

		} else {
			static::createDir(dirname($newName));
			if (realpath($name) !== realpath($newName)) {
				static::delete($newName);
			}
			if (!@rename($name, $newName)) { // @ is escalated to exception
				throw new \Exception("Unable to rename file or directory '$name' to '$newName'. " . self::getLastError());
			}
		}
	}


	/**
	 * Reads file content.
	 * @throws \Exception
	 */
	public static function read(string $file): string
	{
		$content = @file_get_contents($file); // @ is escalated to exception
		if ($content === false) {
			throw new \Exception("Unable to read file '$file'. " . self::getLastError());
		}
		return $content;
	}


	/**
	 * Writes a string to a file.
	 * @throws \Exception
	 */
	public static function write(string $file, string $content, ?int $mode = 0666): void
	{
		static::createDir(dirname($file));
		if (@file_put_contents($file, $content) === false) { // @ is escalated to exception
			throw new \Exception("Unable to write file '$file'. " . self::getLastError());
		}
		if ($mode !== null && !@chmod($file, $mode)) { // @ is escalated to exception
			throw new \Exception("Unable to chmod file '$file'. " . self::getLastError());
		}
	}


	/**
	 * Is path absolute?
	 */
	public static function isAbsolute(string $path): bool
	{
		return (bool) preg_match('#([a-z]:)?[/\\\\]|[a-z][a-z0-9+.-]*://#Ai', $path);
	}


	private static function getLastError(): string
	{
		return preg_replace('#^\w+\(.*?\): #', '', error_get_last()['message']);
	}
}