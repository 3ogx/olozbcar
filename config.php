<?php
ini_set("display_errors", 0);

if (!function_exists('apc_exists')) {
	function apc_exists() { return ;}
}

if (!function_exists('apc_store')) {
	function apc_store() { return;}
}

if (!function_exists('apc_fetch')) {
	function apc_fetch() { return;}
}
