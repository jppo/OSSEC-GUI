<?php
/*
 * Copyright (c) 2019 JP P
 * This program is free software; Distributed under the terms of the GNU GPL v3.
 */

require_once '../db_ossec.php';
if ( ! isset($_GET['mode'] ) )
{	print ("Error mode not set");
	error_log("Error mode not set ",0);
	$mode = "?????";
	exit("Error mode not set");
} else
{	$mode = $_GET['mode'];
}
if ( ! isset($_GET['orig_id'] ) )
{	print ("Error no orig_id");
	$orig_id = 0;
	exit("error no orig_id");
} else
{	$orig_id = $_GET['orig_id'];
}	
if ( ! isset($_GET['dest_id'] ) )
{	print ("Error no dest_id");
	$dest_id = 0;
	exit("error no dest_id");
} else	
{	$dest_id = $_GET['dest_id'];
}	
if ( ! isset($_GET['sign_id'] ) )
{	print ("Error no sign_id");
	exit("error no sign_id");
} else
{	$sign_id = $_GET['sign_id'];
}	
### $MSG = "Relink 5 mode:" . $mode . " orig: " . $orig_id . " sign: " . $sign_id . " dest:" . $dest_id;
### error_log($MSG,0);
### error_log("Relink 5 ",0);

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$query1 = "delete from signature_category_mapping where cat_id = " . $orig_id . " and rule_id = " . $sign_id . ";";
$query2 = "insert into signature_category_mapping (cat_id,rule_id) values (" . $dest_id . "," . $sign_id . ");";

### error_log("Q1=" . $query1,0);

$pdo->beginTransaction();
if ( $mode == "Relink" || $mode == "Unlink" )
{	### error_log("Relink / Unlink : delete",0);
	try
	{	$stmt = $pdo->prepare($query1);
		$stmt->execute();
	} catch (Exception $e)
	{	print ("Sqlerror $e");
		$pdo->rollBack();
		error_log("Error sql : " . $e,0);
		exit("Sqlerror " .$e);
	}
}

### error_log("Q2=" . $query2,0);

if ( $mode == "Link" || $mode == "Relink" )
{	### error_log("Unlink / Relink : insert",0);
	try
	{	$stmt = $pdo->prepare($query2);
		$stmt->execute();
	} catch (Exception $e)
	{	print ("Sqlerror $e");
		$pdo->rollBack();
		error_log("Error sql : " . $e,0);
		exit("Sqlerror " .$e);
	}
}
$pdo->commit();
print("success");
exit(0);
?>
