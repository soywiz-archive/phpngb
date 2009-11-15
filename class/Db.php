<?php
namespace phpngb;

class DbStatment implements \ArrayAccess, \IteratorAggregate, \Countable {
	public $pdo_stat;
	public $crows = null;
	public $query, $params;
	protected $cursorActive = false;
	protected $db;

	public function __construct(DbObject $db, $query, &$params) {
		$this->db = $db;
		$this->params = $params;

		$this->pdo_stat = $db->pdo->prepare($this->query = $query);
		
		if (!($this->pdo_stat instanceof \PDOStatement)) throw(new \Exception("Can't prepare statment"));
		
		$this->pdo_stat->setFetchMode(\PDO::FETCH_ASSOC);
		$this->restart(true);
	}
	
	public function execute() {
		$time = microtime(true);
		{
			$this->pdo_stat->execute($this->params);
		}
		$this->db->log($this->query, $this->params, microtime(true) - $time);
	}

	// ArrayAccess.
	public function offsetExists($offset)         { $this->_offsetPrepare(); return isset($this->crows[$offset]); }
	public function offsetGet   ($offset)         { $this->_offsetPrepare(); return isset($this->crows[$offset]) ? $this->crows[$offset] : false; }
	public function offsetSet   ($offset, $value) { throw(new \Exception("Can't update a DbStatment")); }
	public function offsetUnset ($offset)         { throw(new \Exception("Can't update a DbStatment")); }
	public function _offsetPrepare()              { if ($this->crows === null) $this->crows = iterator_to_array($this); }
	
	public function toArray() { $this->_offsetPrepare(); return $this->crows; }

	// Countable.
	public function count() { $this->_offsetPrepare(); return count($this->crows); }
	
	protected function restart($set = false) {
		if (!$this->cursorActive) {
			$this->pdo_stat->closeCursor();
			$this->cursorActive = false;
			$this->execute();
		}
		$this->cursorActive = $set;
	}
	
	public function fetchAllGrouped() {
		$this->restart();
		return $this->pdo_stat->fetchAll(\PDO::FETCH_ASSOC | \PDO::FETCH_GROUP);
	}
	
	public function getIterator() {
		if ($this->crows !== null) return new \ArrayIterator($this->crows);
		$this->restart();
		return new \IteratorIterator($this->pdo_stat);
	}
	
	public function single($column = 0) {
		if (count($this) < 1) return false;
		$row = array_values($this[0]);
		return $row[$column];
	}
	
	public function fetchAllSingle($column = 0) {
		$r = array();
		foreach ($this as $row) { $row = array_values($row);
			$r[] = $row[$column];
		}
		return $r;
	}
	
	public function __toString() {
		//return $this->query;
		return $this->single();
	}
}

class DbObject {
	public $pdo;
	public $name;
	public $queries = array();
	public $time = 0;

	public function __construct(array $i = array())
	{
		if (!isset($i['dsn'])) throw(new \Exception("Database configuration require 'dsn' field"));
		$this->pdo = new \PDO($i['dsn'], $i['user'], $i['pass']);
		if (preg_match('/^sqlite[23]?:/', $i['dsn'])) {
			$this->pdo->sqliteCreateFunction('NOW', 'time', 0);
		}
		unset($i['pass']);

		$this->pdo->query("SET NAMES 'UTF8';");
		$this->pdo->query("SET CHARACTER SET UTF8;");
		
		$this->name = implode(':', $i);

		$this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

	}

	public function __call($method, $params) {
		return call_user_func_array(array($this->pdo, $method), $params);
	}

	public function query($sql, $params = array()) {
		return new DbStatment($this, $sql, $params);
	}

	public function log($sql, $params, $time = 0) {
		if (!Core::debug()) return;
		$this->queries[] = array($time, $sql, $params);
		$this->time += $time;
	}
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
			$db = new DbObject(static::$configs[$profile]);
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