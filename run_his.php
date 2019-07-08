<?php
/*
 * Copyright (c) 2017 António 'Tó' Godinho <to@isec.pt>.
 * Copyright (c) 2019 JP P
 * This program is free software; Distributed under the terms of the GNU GPL v3.
 */
if ( ! defined('DB_TYPE_O') )
	{ require_once 'db_ossec.php';
	}
#
	if ( DB_TYPE_O == 'history' )
	{ print ' <link href="./css/bootstrap_his.css" rel="stylesheet">';
	} else
	{ print ' <link href="./css/bootstrap_run.css" rel="stylesheet">';
	}
?>
