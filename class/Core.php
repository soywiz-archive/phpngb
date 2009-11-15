<?php
namespace phpngb;

ini_set('unserialize_callback_func', 'spl_autoload_call');
spl_autoload_register(function($class) {
	if (substr($class, 0, 7) != 'phpngb\\') return;
	$file = __DIR__ . '/' . basename(substr($class, 7)) . '.php';
	//if (is_file($file))
	{
		include_once($file);
	}
});

require_once(__DIR__ . '/Twig/Autoloader.php');