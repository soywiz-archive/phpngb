<?php
namespace phpngb;

class Core {
	static public $startTime;

	static public function autoload($class) {
		if (substr($class, 0, 7) != 'phpngb\\') return;
		$file = __DIR__ . '/' . basename(substr($class, 7)) . '.php';
		//if (is_file($file))
		{
			require_once($file);
		}
	}

	static public function debug() {
		try { return (bool)Config::get('debug'); } catch (\Exception $e) { return false; }
	}
	
	static public function exceptionHandler(\Exception $e) {
		if (!static::debug()) return;
		echo '<div style="display:none;">--!></div></script>';
		echo '<pre style="display:block;visibility:visible;color:black;background:white;font: 12px Courier;">';
		echo htmlspecialchars($e->__toString());
		echo '</pre>';
	}
	
	static public function errorHandler($errno, $errstr, $errfile, $errline, array $errcontext) {
		echo '<div style="display:none;">--!></div></script>';
		echo '<pre style="display:block;visibility:visible;color:black;background:white;font: 12px Courier;">';
		printf("'%s'(%d) : '%s'(%d)\n\n", $errstr, $errno, $errfile, $errline);
		$traces = debug_backtrace();
		array_shift($traces);
		echo "Stack trace:\n";
		foreach ($traces as $n => $trace) {
			$func = '';
			if (isset($trace['function'])) $func = $trace['function'];

			$args = '';
			if (isset($trace['args'])) $args = implode(', ', ($trace['args']));
			printf(
				" #%d %s(%d): %s(%s)\n",
				$n,
				$trace['file'],
				$trace['line'],
				$func,
				$args
			);
		}
		//#0 C:\htdocs\phpngb\index.php(17): phpngb\a()

		//echo htmlspecialchars(print_r($trace, true));
		//debug_print_backtrace();
		echo "\nLocal variables:\n";
		foreach ($errcontext as $key => $value) {
			printf(" # '%s':'%s'\n", htmlspecialchars($key), $value);
		}
		echo '</pre>';
	}
	
	static public function shutdown() {
		if (!static::debug()) return;
		echo '<div style="display:none;">--!></div></script>';
		echo '<pre style="display:block;visibility:visible;color:black;background:white;font: 12px Courier;">';
		printf("\nTotal execution time: %.4f\n", microtime(true) - static::$startTime);
		echo '</pre>';
	}
}

// Start time.
Core::$startTime = microtime(true);

// Autoloader.
ini_set('unserialize_callback_func', 'spl_autoload_call');
spl_autoload_register(array('phpngb\\Core', 'autoload'));

// Error handlers.
set_exception_handler(array('phpngb\\Core', 'exceptionHandler'));
set_error_handler    (array('phpngb\\Core', 'errorHandler'), E_ALL | E_STRICT | E_DEPRECATED);

// Shutdown function.
register_shutdown_function(array('phpngb\\Core', 'shutdown'));

// Twig.
require_once(__DIR__ . '/Twig/Autoloader.php');