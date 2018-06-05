<html lang=fr>
<!-- 
/*
 * Copyright (c) 2018 JP P
 * This program is free software; Distributed under the terms of the GNU GPL v3.
 */
-->
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">
    <title>OSSEC WUI</title>
	<script src="../js/jquery-3.3.1.js"></script>
<?php
	require_once './run_his_php.php';
?>

    <script type="text/javascript">
	function WRITELINK(rule_id) 
	{	// alert("Begin write");
		var ptr = document.getElementById('category');
		var cat_id = ptr.options[ptr.selectedIndex].value;
		//alert("Writelink : " + rule_id + "/" + cat_id + "/");
		if ( isNaN(parseFloat(cat_id)) )
		{	alert("Category is not set");
		} else
		{	$.ajaxSetup({async: false});
			url = "./manage_categ_map_write.php?rule_id=" + rule_id + "&cat_id=" + cat_id;
			$.get(url,function(data,status) {
					alert("Rule : " + rule_id + " linked to category " + cat_id + " Status: " + status); } );
			zzz = 0;
			$.ajaxSetup({async: true});
		// alert("After call");
			window.location.href = "../manage_categ_map.php"; 
		}
	}
	</script>
    <script type="text/javascript">
	function ZCANCEL()
	{ 	$.ajaxSetup({async: true});
		url = "./manage_categ_map_write.php";
		$.get(url,function(data,status) {
				alert(rule_id + " Canceled status: " + status); } );
		zzz = 0;
		$.ajaxSetup({async: true});
		window.location.href = "../manage_categ_map.php"; 
	}
	</script>
</head>

<body>
<?php

require_once '../db_ossec.php';

if ( isset($_GET['id']) )
	{	$id = $_GET['id'];
    } else
	{	$id = 0;
    }
if ( isset($_GET['rule_id'] ) )
	{	$rule_id = $_GET['rule_id'];
	} else
	{	$rule_id = 0;
	}
if ( isset($_GET['description'] ) )
	{	$description = $_GET['description'];
	} else
	{	$description = "";
	}
if ( isset($_GET['level'] ) )
	{	$level = $_GET['level'];
	} else
	{	$level = 0;
	}

$pdo = new PDO('mysql:host=' . DB_HOST_O . ';dbname=' . DB_NAME_O . ';charset=utf8', DB_USER_O, DB_PASSWORD_O);
$query = "select cat_id, cat_name from category order by cat_name";
try
{ 	$stmt  = $pdo->prepare($query);
	$stmt->execute();
} catch (Exception $e)
{	print("SQLERROR : " . $z);
	print ("<br>SQL=(" . $query);
	return;
}
$selectcat = "";
while ($row = $stmt->fetch(PDO::FETCH_ASSOC) )
{	$selected = "";
	$selectcat .= "<option value='" . $row['cat_id'] . "'" . $selected . ">" . $row['cat_name'] . "</option>";
#	print("// " . $row['cat_name'] );
}
?>
<div>
<p style=align:center;>
<h2>Table : signature_category_mapping </h2>
<h4>Link signature to category</h4>
</div>
<hr>
<div>
<h4> <b>
<table width=600px cellpadding=5px border=1px bgcolor=lightgrey>
<tr>	<td width=200px>ID</td>          
		<td> 
			<?php print($id); 
			?> 
		</td>
</tr> <tr>
		<td>Rule ID </td>    
		<td> 
			<?php print($rule_id); 
			?>
		</td>
</tr> <tr>
		<td>Level </td>
		<td> 
			<?php print($level); 
			?>
		</td>
</tr>
</tr> <tr>
		<td>Description </td>
		<td> 
			<?php print($description); 
			?>
		</td>
</tr>
</table>
</h4></b>
<br>
<hr>
<p> <b>Choose the category</b>
<select name='category' id='category' class="form-control input-sm">
	<option value=''>--</option>
	<?php echo $selectcat; ?>
</select>
</p>

<?php
#print("<form id='clickreturn' method='GET' action='../manage_categ_map.php' class='form-inline'>");
print("<input type=submit value='..go' class='btn btn-success' onclick=WRITELINK(" . $rule_id . ")>");
# print("</form>");
# print("<form id='clickcancel' method='GET' action='../manage_categ_map.php' >");
print("<input  type=submit value='..cancel' class='btn btn-warning' onclick=ZCANCEL()>");
#print("</form>");
?>

</div>

</body>
</html>
