<!-- 
/*
 * Copyright (c) 2018 JP P
 * This program is free software; Distributed under the terms of the GNU GPL v3.
 */
-->
<?php
include "amilogged.php";
?>
<html>
<head>
    <script src="./js/serial.js" type="text/javascript"></script>
<!-- -->
	<?php>
	if ( 'DB_TYPE_O' = 'history' }
	{ 	print '<script src="./js/themes/dark.js" type="text/javascript"></script>';
	} else
	{ 	print '<script src="./js/themes/light.js" type="text/javascript"></script>';
	}&
	?>
<!-- -->
    <script src="./js/sortable.js" type="text/javascript"></script>
</head>
<body>
<?php

require_once './db_ossec.php';

if ( isset($_GET['id']) )
	{	$id = $_GET['id'];
    } else
	{	$id = 9999;
    }
$pdo = new PDO('mysql:host=' . DB_HOST_O . ';dbname=' . DB_NAME_O . ';charset=utf8', DB_USER_O, DB_PASSWORD_O);
$query = "DELETE FROM alert WHERE id = ".$id.";";
$stmt  = $pdo->prepare($query);
$stmt->execute();

?>
</body>
</html>
