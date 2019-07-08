<?php
/*
 * Copyright (c) 2017 António 'Tó' Godinho <to@isec.pt>.
 * Copyright (c) 2019 JP P
 * This program is free software; Distributed under the terms of the GNU GPL v3.
 */

require_once '../db_ossec.php';

if ( ! isset($_GET['cat_id'] ) )
{	print ("Error no cat_id");
	exit("error no cat_id");
}	

$mainstring = "<select name='signature' id='signature' class='form-control input-sm' > ";
$cat_id = $_GET['cat_id'];
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$query = "SELECT SG.id as sgid,SG.description as sglib FROM signature SG
	where SG.rule_id in 
      (select MP.rule_id FROM signature_category_mapping MP
       where MP.cat_id = " . $cat_id . ") order by SG.description;";
try
{	$stmt = $pdo->prepare($query);
	$stmt->execute();
} catch (Exception $e)
{	print ("Sqlerror $e");
	error_log("Error sql : " . $e);
	exit("Sqlerror " .$e);
}
while ( $row = $stmt->fetch(PDO::FETCH_ASSOC) )
{	$sg_id  = $row['sgid'];
	$sg_lib = $row['sglib'];
	$mainstring .= "<option value=" . $sg_id . " >" . $sg_id . ' / ' . $sg_lib . "</option>";
}
print $mainstring;
print("</select>");
?>
