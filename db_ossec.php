<?php
/*
 * Copyright (c) 2017 António 'Tó' Godinho <to@isec.pt>.
 * Copyright (c) 2018 JP P
 * This program is free software; Distributed under the terms of the GNU GPL v3.
 */

if ( ! defined('DB_USER_O') )
	{	define ('DB_USER_O', 'ossec');
		define ('DB_PASSWORD_O', 'User_Ossec_1234');
		define ('DB_HOST_O', '192.168.2.2');
		define ('DB_NAME_O', 'ossec_base');
		define ('DB_TYPE_O', 'running');
#		define ('DB_TYPE_O', 'history');	
	}
$VERSION = "V3.0";
try {
    $pdo = new PDO('mysql:host=' . DB_HOST_O . ';dbname=' . DB_NAME_O . ';charset=utf8', DB_USER_O, DB_PASSWORD_O);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage();
    exit();
}
