<?php
namespace phpngb;

require_once(__DIR__ . '/class/Core.php');

// Change Config path:
// Config::$path = __DIR__;

$rows = Db::query("SELECT 1;");
foreach ($rows as $row) {
	print_r($row);
}