<?php
/*
 * Copyright (c) 2017 António 'Tó' Godinho <to@isec.pt>.
 * Copyright (c) 2019 JP P
 * This program is free software; Distributed under the terms of the GNU GPL v3.
 */
include "./amilogged.php";
require_once "./header_management.php";
require_once './top.php';
require_once "./run_his.php";
if ( ! $ISADMIN )
{	print("<html><body><br><br><br><h3>You are not admin</h3></br></br></br></body></html>");
	exit("fatal");
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">
    <title>OSSEC GUI</title>
    <?php
	require_once './db_ossec.php';
	require_once './config.php';
	require_once './run_his.php';
	$zzz = 0;
    ?>
    <link href="./css/style.css" rel="stylesheet" type="text/css"/>
    <link href="./css/sticky-footer.css" rel="stylesheet">
    <script src="./js/sortable.js" type="text/javascript"></script>
    <script src="./js/jquery-3.3.1.js"></script>
<!--  Local functions  -->
    <script type="text/javascript">
	function GET_SIGNATURES(cat_id) 
	{	$.ajaxSetup({async: false});
		url = "./php/manage_categ_list_signatures.php?cat_id=" + cat_id;
		$.get(url,function(data,status)
			{	
				if ( status != "success" )
				{	alert("No data found or error : " +data);
				} else
				{	jQuery("#signature").replaceWith(data);
				}
			}
			);
	}
	var zzz = 0;
	</script>
<!-- -->
	<script type="text/javascript">
	function LINK_SIGNATURE(mode)
	{	//		alert("LINK_SIGNATURE");
		var el  = document.getElementById("orig_category");
		orig_id = el.options[el.selectedIndex].value;
		el      = document.getElementById("dest_category");
		dest_id = el.options[el.selectedIndex].value;
		el      = document.getElementById("signature");
		sign_id = el.options[el.selectedIndex].value;
		var url = "./php/manage_categ_relink_signatures.php?mode=" + mode + "&orig_id=" + orig_id + "&dest_id=" + dest_id + "&sign_id=" + sign_id;
		var err = 0;
		var MSG = "";
 		if ( orig_id == "" || orig_id < 1 )
		{	err = 1;
			MSG = "No origin categ defined \n"
		}
 		if ( dest_id == "" || dest_id < 1 )
		{	err += 1;
			MSG += "No destination categ defined \n"
		}
 		if ( sign_id == "" || sign_id < 1 )
		{	err += 1;
			MSG += "No signature defined"
		}
		var done = 0;
		if ( err != 0 )
		{	alert(MSG);
		} else
		{	$.ajaxSetup({async: false});
			$.get(url,function(data,status)
				{	if ( status != "success" )
					{	alert("No data found or error : " +data);
					} else
					{	document.getElementById("click2go").submit();
					}
				}
			);
		}
	}
	</script>
<!--                                      -->
<?php
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$query = "SELECT CT.cat_id as cat_id,CT.cat_name as cat_name FROM category CT 
where cat_id in ( select MP.cat_id from signature_category_mapping MP group by MP.cat_id)
ORDER BY cat_name";
$filtercategory = "";
if ( isset($_GET['origcategory'] ) )
{	$origcategory = $_GET['origcategory'];
} else
{	$origcategory = "";
}
#	
try
{	$stmt = $pdo->prepare($query);
	$stmt->execute();
} catch (Exception $e)
{	error_log("Sqlerror : " . $e);
	print("<br><br><hr> Sqlerror " . $e);
	die;
}
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) 
{
    if ($row['cat_id'] == $origcategory) 
	{ 	$selected = " SELECTED";
    } else
	{	$selected = "";
	}
    $filtercategory .= "<option value='" . $row['cat_id'] . "'" . $selected . ">" . $row['cat_name'] . "</option>";
}
/* */  
$filtercategory2 = "";
/*	category dest */ 
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$query = "SELECT CT.cat_id as cat_id,CT.cat_name as cat_name FROM category CT 
ORDER BY cat_name";
if ( isset($_GET['destcategory'] ) )
{	$destcategory = $_GET['destcategory'];
} else
{	$destcategory = "";
}
#	
try
{	$stmt = $pdo->prepare($query);
	$stmt->execute();
} catch (Exception $e)
{	error_log("Sqlerror : " . $e);
	print("<br><br><hr> Sqlerror " . $e);
	die;
}
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) 
{
    if ($row['cat_id'] == $destcategory) 
	{ 	$selected = " SELECTED";
    } else
	{	$selected = "";
	}
    $filtercategory2 .= "<option value='" . $row['cat_id'] . "'" . $selected . ">" . $row['cat_name'] . "</option>";
}
/* -------------- */
?>

</head>
<body >
<div class="container-fluid" style="padding-top: 60px;">
<p align=middle><h3>Modify Signature <--> category links</h3></p>
<!-- ------------------------------------------------------------ -->
</div>
<!--   -->
<div class="row">
  <div class="col-lg-12">
    <ul class="nav nav-pills" role="tablist" style="width: 100%;">
      <li role="presentation" class="active" style="width: 100%;">
		<a href="#" style="font-weight: 800">Choose a source category to get signatures</a>
      </li>
    </ul>
  </div>
</div>
<br>

<!--   -->
<div class='row'></div>
<div class="col-lg-5 form-group">
<select name='orig_category' id='orig_category' class='form-control input-sm' onchange=GET_SIGNATURES(this.value)>
<option value=''>--</option>
<?php echo $filtercategory; ?>
</select>
</div>
<!-- --------------------------------- -->
<div class="row">
  <div class="col-lg-12">
    <ul class="nav nav-pills" role="tablist" style="width: 100%;">
      <li role="presentation" class="active" style="width: 100%;">
		<a href="#" style="font-weight: 800">Choose a signature to move</a>
      </li>
    </ul>
  </div>
</div>
<br>

<div class='row'></div>
<div class="col-lg-5 form-group">
<select name='signature' id='signature' class='form-control input-sm' >
<option value='0'>------------------------------------------------------------</option>
</select>
</div>
<!-- --------------------------------- -->
<div class="row">
  <div class="col-lg-12">
    <ul class="nav nav-pills" role="tablist" style="width: 100%;">
      <li role="presentation" class="active" style="width: 100%;">
		<a href="#" style="font-weight: 800">Choose a destination category to link signature</a>
      </li>
    </ul>
  </div>
</div>
<br>

<div class='row'></div>
<div class="col-lg-5 form-group">
<select name='dest_category' id='dest_category' class='form-control input-sm' >
<option value=''>--</option>
<?php echo $filtercategory2; ?>
</select>
</div>

</div>

<hr>
<br>

<div class='row'></div>
<div class="col-lg-1 vc">
<form id='click2go' name='click2go' method='GET' action='./manage_categ.php' class="form-inline">
	<input type='submit' value='.. Unlink (del)' class="btn btn-info" onclick="LINK_SIGNATURE('Unlink');"/>
    <br>
	<input type='submit' value='.. Link (add)' class="btn btn-warning" onclick="LINK_SIGNATURE('Link');"/>
    <br>
	<input type='submit' value='.. Relink (del + add)' class="btn btn-success" onclick="LINK_SIGNATURE('Relink');"/>
    <br>
</form>
</div>

<br>
<?php
require_once './config.php';
include './footer.php';
?>
</body>
</html>
