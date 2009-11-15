<?php
namespace phpngb;

class Config {
	static public $path;
	static protected $cachedConfigs = array();
	
	static public function &get($profile) {
		$config = &static::$cachedConfigs[$profile];
		if (!isset($config)) {
			$file = static::$path . '/' . basename($profile) . '.config.php';
			$config = include($file);
		}
		return $config;
	}
}

Config::$path = __DIR__ . '/../config';