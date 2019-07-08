<?php
/*
 * Copyright (c) 2019 JP P
 * This program is free software; Distributed under the terms of the GNU GPL v3.
 */

require_once '../db_ossec.php';

if ( ! isset($_GET['sign_id'])  )
{	print ("Error no sign_id \n");
	exit("error no sign_id ");
}	
$sign_id  = $_GET['sign_id'];

$query = "SELECT CT.cat_id, CT.cat_name FROM category CT 
where CT.cat_id in ( select MP.cat_id from signature_category_mapping MP
                     where MP.rule_id = " . $sign_id . ");";
/* */
$mainstring = "<div name=catlist class='newboxes toggled' id=catlist><table class='dump sortable' id='sortabletable'  style='width:50%' align='left'><tr>
                            <th align='left'>Del?</th><th align='left'>ID</th><th align='left'>Category name</th>
                            </tr>";
$mainstring .= "<option value=''>--</option>";


$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
try
{	$stmt = $pdo->prepare($query);
	$stmt->execute();
} catch (Exception $e)
{	print ("<br>Sqlerror $e" . "<br>");
	print("<br>" + $query + "<br>");
	error_log("Error sql : " . $e);
	error_log("Sql=" . $query);
	exit("Sqlerror " .$e);
}
while ( $row = $stmt->fetch(PDO::FETCH_ASSOC) )
{	$cat_id    = $row['cat_id'];
	$cat_name  = $row['cat_name'];

	$mainstring .= "<tr><td align left> <img src=./images/delete_icon.png width=18 height=16 onclick='ZDelId(" . $cat_id . ")'> </td>";
	$mainstring .= "<td align='left'> " . $cat_id . "</td>";
	$mainstring .= "<td align='left'>" . htmlspecialchars($cat_name) . "</td></tr>";
} 
$mainstring .= "</table> </div>";
print $mainstring;
/* */

?>
