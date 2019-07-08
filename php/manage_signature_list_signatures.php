<?php
/*
 * Copyright (c) 2019 JP P
 * This program is free software; Distributed under the terms of the GNU GPL v3.
 */

require_once '../db_ossec.php';

if ( ! isset($_GET['rule_id'])  && ! isset($_GET['desc']) )
{	print ("Error no sign_id nor description\n");
	exit("error no sign_id nor description");
}	
$where = "";
if ( isset($_GET['rule_id'] ) )
{	$rule_id  = $_GET['rule_id'];
	$where    = " where SG.id = " . $rule_id;
} else
{	$rule_id  = 0;
}
if ( isset($_GET['descr']) )
{	$descr = $_GET['descr'];
	if ( $descr != "" )
	{ 	$where = " where SG.description like '%" . $descr . "%' ";
	}
} else
{	$descr  = "";
}
$query = "SELECT SG.id as sgid,SG.level,SG.description as sglib FROM signature SG " . $where . " order by SG.description;";
# error_log("(" . $query . ")");
/* */
$mainstring = "<select name='signature' id='signature' class='form-control input-sm' onchange=GET_CATEG(this.value)> > ";
$mainstring .= "<option value=''>--</option>";
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
try
{	$stmt = $pdo->prepare($query);
	$stmt->execute();
} catch (Exception $e)
{	print ("<br>Sqlerror $e" . "<br>");
	print("<br>" + $query + "<br>");
	error_log("Error sql : " . $e);
	exit("Sqlerror " .$e);
}
while ( $row = $stmt->fetch(PDO::FETCH_ASSOC) )
{	$sg_id    = $row['sgid'];
	$sg_lib   = $row['sglib'];
	$sg_level = $row['level'];
	$mainstring .= "<option value=" . $sg_id . " > ID=" . $sg_id . " level=" . $sg_level . " : " . $sg_lib . "</option>";
} 
print $mainstring;
print("</select>");
/* */

?>
