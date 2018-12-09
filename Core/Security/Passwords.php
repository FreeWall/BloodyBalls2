<?php
namespace Core\Security;

class Passwords {

	const ALGO = PASSWORD_BCRYPT;

	public static function hash(string $password,$options = ["cost" => 10]):string {
		return password_hash($password,self::ALGO,$options);
	}

	public static function verify(string $password,string $hash):bool {
		return password_verify($password,$hash);
	}

	public static function needsRehash(string $hash,$options = ["cost" => 10]):bool {
		return password_needs_rehash($hash,self::ALGO,$options);
	}
}