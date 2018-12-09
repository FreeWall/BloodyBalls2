<?php
namespace Core\Utils;

class DateUtil {

	private static $timeFormat = array(
		0 => array("Před","sek","min","hod","Včera",array("dny","dny")),
		1 => array("Za","sek","min","hod","Zítra",array("dní","dny"))
	);

	public static function date($timestamp = 0){
		if($timestamp == 0) $timestamp = time();
		return date("j.n.Y",$timestamp);
	}

	public static function dateTime($timestamp = 0,$format = "j.n.Y H:i"){
		if($timestamp == 0) $timestamp = time();
		return date($format,$timestamp);
	}

	public static function lastTime($timestamp = 0,$lower = false,$prefix = array("Před","Za")){
		$date = "";
		if($timestamp == 0) return "<span class='lasttime'>Nikdy</span>";
		$time = time()-$timestamp;
		$past = ($time >= 0 ? true : false);
		if($time == 0) return "<span class='lasttime' title='".date("d.m.Y \\v H:i",$timestamp)."'>Právě teď</span>";
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
		return $date;
	}
}