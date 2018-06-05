<?php
/*
 * Copyright (c) 2018 JP P
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
# filter<var> = for repopulating the filter toolbar
# where = the cumulative sql command
## filter criteria 'levelmin' and 'levelmax' 
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
    <title>OSSEC WUI</title>
    <?php
	require_once './db_ossec.php';
	require_once './config.php';
	require_once './run_his.php';
    require_once './page_refresh.php';
	require_once './header_management.php';
	$zzz = 0;
    ?>
    <link href="./css/style.css" rel="stylesheet" type="text/css"/>
    <link href="./css/sticky-footer.css" rel="stylesheet">
    <script src="./js/sortable.js" type="text/javascript"></script>
    <script src="./js/jquery-3.3.1.js"></script>
<!--  Local functions  -->
    <script type="text/javascript">
	function GET_CATEG(sign_id) 
	{	// alert("GET_CATEG début");
		$.ajaxSetup({async: false});
		url = "./php/manage_signature_list_categories.php?sign_id=" + sign_id;
		// alert("GET_CATEG url=" + url);
		$.get(url,function(data,status)
			{	
				if ( status != "success" )
				{	alert("No data found or error : " +data);
				} else
				{	// alert("GET_CATEG avant replace");
					jQuery("#catlist").replaceWith(data);
				}
			}
			);
		// alert("GET_CATEG ");
	}
	var zzz = 0;
	</script>
<!-- -->
<script type="text/javascript">
function GET_SIGNATURES() 
{	
	var rule_id  = document.getElementById('rule_id').value;
	var descr    = document.getElementById("descr").value;
	var err      = 0;
	if ( rule_id == "" && descr == "" )
	{	alert("Neither Rule ID nor part of Description given");
	} else
	{ 	url = "./php/manage_signature_list_signatures.php?rule_id=" + rule_id +"&descr=" + descr;
		$.ajaxSetup({async: false});
		$.get(url,function(data,status)
			{	if ( status != "success" )
				{	alert("No data found or error : " +data);
				} else
				{	jQuery("#signature").replaceWith(data);
				}
			}
			);
	}
}
</script>
<!-- -->
    <script type="text/javascript">
	function ZDelId(cat_id) 
	{ 	$.ajaxSetup({async: false});
		url = "./php/manage_categ_relink_signatures.php?mode=Unlink";
		url += "&orig_id=" + cat_id;
		url += "&dest_id=0";
		var ptr = document.getElementById("signature");
		var rule_id  = ptr.options[ptr.selectedIndex].value;
		url += "&sign_id=" + rule_id;
		$.get(url,function(data,status) {
				if ( status != "success" )
					{	alert("No data found or error : " + data);
					} else
					{	GET_CATEG(rule_id);
					}
 			} ) ;
		$.ajaxSetup({async: true});
	}
	</script>
<!-- -->
	<script type="text/javascript">
	function LINK_SIGNATURE(mode)
	{	orig_id = 0;
		el      = document.getElementById("dest_category");
		dest_id = el.options[el.selectedIndex].value;
		el      = document.getElementById("signature");
		sign_id = el.options[el.selectedIndex].value;
		var url = "./php/manage_categ_relink_signatures.php?mode=" + mode + "&orig_id=" + orig_id + "&dest_id=" + dest_id + "&sign_id=" + sign_id;
  
		var err = 0;
		var MSG = "";
 		if ( dest_id == "" || dest_id < 1 )
		{	err += 1;
			MSG += "No destination categ defined \n"
		}
 		if ( sign_id == "" || sign_id < 0 )
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
					{	GET_CATEG(sign_id);
					}
				}
			);
		}
	}
	</script>
<!--                                      -->
<?php
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
<!-- --------------------------------- -->
<div class="row">
  <div class="col-lg-12">
    <ul class="nav nav-pills" role="tablist" style="width: 100%;">
      <li role="presentation" class="active" style="width: 100%;">
		<a href="#" style="font-weight: 800">Define filter to find a signature</a>
      </li>
    </ul>
  </div>
</div>

<div class='row' align="center">
<div class="col-lg-2 form-group"> <label>Rule id</label></div>
<div class="col-lg-3 form-group"> <label>Or part of signature description</label></div>
</div>

<div class='row' align="center">
<div class="col-lg-2 form-group">
	<input type="text" size=6  name="rule_id" id="rule_id">
</div>
<div class="col-lg-3 form-group">
	<input type="text" size=24 name="descr"   id="descr">  
</div>
<div class="col-lg-2 form-group">
	<input type='submit' value='..Go' class="btn btn-success" onclick="GET_SIGNATURES();">
</div>
</div>
<!-- --------------------------------- -->
<div class="row">
  <div class="col-lg-12">
    <ul class="nav nav-pills" role="tablist" style="width: 100%;">
      <li role="presentation" class="active" style="width: 100%;">
		<a href="#" style="font-weight: 800">Choose a signature</a>
      </li>
    </ul>
  </div>
</div>
<br>

<div class='row'></div>
<div class="col-lg-5 form-group">
<select name='signature' id='signature' class='form-control input-sm' onchange=GET_CATEG(this.value)>
<!-- <option value=''>--</option> -->
</select>
</div>
<div class="row">
  <div class="col-lg-12">
    <ul class="nav nav-pills" role="tablist" style="width: 100%;">
      <li role="presentation" class="active" style="width: 100%;">
		<a href="#" style="font-weight: 800">Categories already mapped to signature</a>
      </li>
    </ul>
  </div>
</div>
<br>
<!-- Place holder for displaying list of categories for choosen signature -->
<div id=catlist name=catlist>
<br>
</div>
<!-- List box for categories to add      -->

<!-- --------------------------------- -->
<!-- --------------------------------- -->
<!-- --------------------------------- -->
<div class="row">
  <div class="col-lg-12">
    <ul class="nav nav-pills" role="tablist" style="width: 100%;">
      <li role="presentation" class="active" style="width: 100%;">
		<a href="#" style="font-weight: 800">Choose a category to map to selected signature</a>
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

<!-- </div> -->

<hr>
<br>

<div class='row'></div>
<div class="col-lg-1 vc">
<!-- <form id='click2go' name='click2go' method='GET' action='./manage_signature.php' class="form-inline"> -->
	<input type='submit' value='.. Link (add)' class="btn btn-warning" onclick="LINK_SIGNATURE('Link');"/>
    <br>
<!-- </form> -->
</div>

<br>
<?php
require_once './config.php';
include './footer.php';
?>
</body>
</html>
