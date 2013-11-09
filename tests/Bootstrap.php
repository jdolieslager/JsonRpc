<?php
chdir(dirname(__FILE__));

spl_autoload_register(function($class) {
	$file = __DIR__ . '/' . str_replace('\\', '//', $class) . '.php';
	
	if (is_file($file)) {
		include $file;
	}
});

spl_autoload_register(function($class) {
	$file = __DIR__ . '/../src/' . str_replace('\\', '//', $class) . '.php';
	
	if (is_file($file)) {
		include $file;
	}
});