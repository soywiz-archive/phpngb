<?php
namespace phpngb;

class DbObject extends \PDO {
}

class Db {
	static public $db;
	static public $dbs = array();
	static public $configs = array();
	
	static public function prepare($profile, array $config = array()) {
		static::$configs[$profile] = $config;
	}
	
	static public function get($profile) {
		$db = &static::$dbs[$profile];
		if (!isset($db)) {
			if (!isset(static::$configs[$profile])) throw(new \Exception("Can't find profile '{$profile}' for database connection."));
			$c = static::$configs[$profile];
			$db = empty($c['user']) ? (new DbObject($c['dsn'])) : (new DbObject($c['dsn'], $c['user'], $c['pass']));
		}
		if (!isset(static::$db)) static::$db = $db;
		return $db;
	}

	static public function __callStatic($method, $params) {
		$db = &static::$db;
		if (!isset($db)) static::get('default');
		if (!($db instanceof DbObject)) throw(new \Exception("Invalid db class"));
		return call_user_func_array(array($db, $method), $params);
	}
}

Db::prepare('default', Config::get('Db'));