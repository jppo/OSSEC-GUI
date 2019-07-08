<?php
/*
 * Copyright 2019 JP P
 * This program is free software; Distributed under the terms of the GNU GPL v3.
 * Added to replace "optimise" in Database cleanup every time
 * you run it.
*/
include './amilogged.php';
include './header_management.php';
include './run_his.php';
if ( ! $ISADMIN )
{	print("<html><body><br><br><br><h3>You are not admin</h3></br></br></br></body></html>");
	exit("fatal");
}
?>
<link href="./css/style.css" rel="stylesheet" type="text/css"/>
<link href="./css/sticky-footer.css" rel="stylesheet">
<script src="./js/amcharts.js" type="text/javascript"></script>
<script src="./js/serial.js" type="text/javascript"></script>
<script src="./js/themes/light.js" type="text/javascript"></script>

<div class="contents"  >
<br>
Reorganisation de la base de données
</div>
<br>
<div text-align: center;>
<H2>Reorganizing database, this could take time</H2>
</div>

<?php
require_once './db_ossec.php';
require './config.php';

ini_set('display_errors',1);
error_reporting(E_ALL);

$ret = ReorgTable('alert');
if ( DB_TYPE_O == 'history' )
   {	print '<H2> "History" database, "alert" is the only relevant table</H2>';
   } else
   { 	$ret = ReorgTable('agent');
		$ret = ReorgTable('category');
		$ret = ReorgTable('location');
		$ret = ReorgTable('server');
		$ret = ReorgTable('signature');
		$ret = ReorgTable('signature_category_mapping');
   }


function ReorgTable($table)
{
$result = '';
$query = "OPTIMIZE TABLE ".$table.";";
$pdo = new PDO('mysql:host=' . DB_HOST_O . ';dbname=' . DB_NAME_O . ';charset=utf8', DB_USER_O, DB_PASSWORD_O);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$stmt   = $pdo->prepare($query);
$zex    = $stmt->execute();
$result = $stmt->fetch();
$affiche = $result[3];
$stmt->closeCursor();
$res = strtoupper($result[2]);
#	print "--------".$res."--------";
if ( $res  == 'ERROR'	)
	{	print "<H2>Erreur ".$result[3]." </H2>";
		return 1;
	} 
print "<H3> Table ".$table." : reorganized <br>Note: ".$affiche." </H3>";
	
return 0;

}
include './footer.php';
?>
