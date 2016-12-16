<?php
ini_set("display_errors", 0);

if (!function_exists('apc_exists')) {
	function apc_exists($name) { return isset($_SESSION[$name]); }
}

if (!function_exists('apc_store')) {
	function apc_store($name, $value) { return $_SESSION[$name] = $value; }
}

if (!function_exists('apc_fetch')) {
	function apc_fetch($name) { return $_SESSION[$name]; }
}
