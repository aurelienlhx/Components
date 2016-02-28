<?php

spl_autoload_register(function($class_name) {
	//basename use for namespace notation
	$path = realpath(dirname(__DIR__).'/src/' . basename($class_name) . '.php');
    if(is_file($path))
    	require_once($path);
});

//vfsStream
require dirname(__DIR__).'/vendor/autoload.php';