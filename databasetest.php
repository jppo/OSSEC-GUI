<?php
/*
 * Copyright (c) 2017 António 'Tó' Godinho <to@isec.pt>.
 * Copyright (c) 2018 JP P
 * This program is free software; Distributed under the terms of the GNU GPL v3.
 */

# see if database is populated correctly, if not then JS alert to user.

echo "var zzz = 0;";

$query = "SELECT count(id) as res_count FROM alert";
try 
{	$stmt = $pdo->prepare($query);
	$stmt->execute();
} catch (Exception $e)
{	echo "Alert checking database for informations \n" . $e ." !!";
	return;
}
if ($stmt->rowCount() > 0) 
{ 	$row = $stmt->fetch();
    if (!$row['res_count'] > 0) 
	{ 	echo "alert(\"Connected to database ok, but no alerts found. Ensure OSSEC is logging to your database.\");";
		return;
    }
} 

$query = "SELECT count(id) as res_count FROM location";
try 
{	$stmt = $pdo->prepare($query);
	$stmt->execute();
} catch (Exception $e)
{	echo "alert(\"Error on access to location : \" . $e)";
	return;
}

if ($stmt->rowCount() > 0) 
{ 	$row = $stmt->fetch();
    if (!$row['res_count'] > 0) 
	{ 	echo "alert(\"Connected to database ok, but no data found. Ensure OSSEC is logging to your database.\");";
    }
} else 
{ 	echo "alert(\"Problems checking database for information\");";
}
?>
