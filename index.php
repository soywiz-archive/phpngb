<?php
namespace phpngb;

require_once(__DIR__ . '/class/Core.php');

// Change Config path:
// Config::$path = __DIR__;

echo Db::query("SELECT now();");
