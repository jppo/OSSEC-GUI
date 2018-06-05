<!-- 
/*
 * Copyright (c) 2018 JP P
 * This program is free software; Distributed under the terms of the GNU GPL v3.
 */
-->
<html>
<head>
<!-- -->
	<?php>
	if ( 'DB_TYPE_O' = 'history' }
	{ 	print '<script src="./js/themes/dark.js" type="text/javascript"></script>';
	} else
	{ 	print '<script src="./js/themes/light.js" type="text/javascript"></script>';
	}&
	?>
<!-- -->
</head>
<?php

require_once '../db_ossec.php';

if ( isset($_GET['rule_id']) )
	{	$rule_id = $_GET['rule_id'];
    } else
	{	$rule_id = 9999;
    }
if ( isset($_GET['cat_id']) )
	{	$cat_id = $_GET['cat_id'];
    } else
	{	$cat_id = 9999;
    }
/* */
$pdo = new PDO('mysql:host=' . DB_HOST_O . ';dbname=' . DB_NAME_O . ';charset=utf8', DB_USER_O, DB_PASSWORD_O);
$query = "insert into signature_category_mapping (rule_id,cat_id)
 values (" . $rule_id . "," . $cat_id . ");";
try
{ 	$stmt  = $pdo->prepare($query);
	$stmt->execute();
} catch (Exception $e)
{	print("Sqlerror : " . $e );
	return 1;
}
/* */
print ("<script type=text/javascript>");
print ("alert('Creating link rule:" . $rule_id . " to categ : " . $cat_id . "')");
print ("</script>");

# error_log("Ecrit : (" . $rule_id . "/" . $cat_id . ")");
?>
<body>
</body>
</html>
