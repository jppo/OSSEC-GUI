<?php
/*
 * Copyright (c) 2018 JP P
 * This program is free software; Distributed under the terms of the GNU GPL v3.
 */
set_include_path(".:./php:./:../");

require_once 'auth/vendor/autoload.php';

if ( ! defined('AUTH_USER_O') )
	{	define ('AUTH_USER_O', 'ossec_auth');
		define ('AUTH_PASSWORD_O', 'Ossec_Auth_1234');
		define ('AUTH_HOST_O', '192.168.2.2');
		define ('AUTH_NAME_O', 'ossec_auth');
	}
try 
{ 	$pda = new PDO('mysql:host=' . AUTH_HOST_O . ';dbname=' . AUTH_NAME_O . ';charset=utf8', AUTH_USER_O, AUTH_PASSWORD_O);
	$pda->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage();
    exit("DB_AUTH ERROR");
}
$auth = new \Delight\Auth\Auth($pda);
?>
