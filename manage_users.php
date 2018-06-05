<?php
/*
 * Copyright (c) 2018 JP P
 * This program is free software; Distributed under the terms of the GNU GPL v3.
 */
include "./amilogged.php";
require_once "./header_management.php";
require_once "./run_his.php";
if ( ! $ISADMIN )
{	print("<html><body><br><br><br><h3>You are not admin</h3></br></br></br></body></html>");
	exit("fatal");
}
$MYPATH = $_SERVER['REQUEST_URI'];
$_SESSION['MYPATH'] = $MYPATH;
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
	require_once './db_auth.php';
	require_once './config.php';
	include './amilogged.php';
    ?>
<link href="./css/style.css" rel="stylesheet" type="text/css"/>
<link href="./css/sticky-footer.css" rel="stylesheet">
<link href="./css/login.css" rel="stylesheet" type="text/css">
<script src="./js/sortable.js" type="text/javascript"></script>
<script src="./js/jquery-3.3.1.js"></script>
</head>
<body >
<div class="container-fluid" style="padding-top: 60px;">
<h3>Users management</h3>
</div>
<!--    -->
<div class="row toggle" id='action'>
<!-- Title "ACTION" -->
  <div class="col-lg-12">
    <ul class="nav nav-pills" role="tablist" style="width: 100%;">
      <li role="presentation" class="active" style="width: 100%;">
	  <a href="#" style="font-weight: 800">Choose Action</a>
      </li>
    </ul>
  </div>
</div>
<!-- Choose an action -->
<div class="action" >
<table>
<tr><td>&nbsp;&nbsp;</td>
<td>
 <form class='primary_box'>
  <input type='radio' name='fieldc' value='create'  class="checkbox-inline"/> Create user
  <input type='radio' name='fieldc' value='change'  class="checkbox-inline"/> Change password
  <input type='radio' name='fieldc' value='list'    class="checkbox-inline"/> List users
 </form>
</td>
<td>
<button type=button class="button" onclick="ACTION()">..Go</button>
</td></tr>
</table>
</div>
<!-- end ACTION -->

<!--  Work to do -->
<div class="worktodo" id=worktodo >

<!-- end Wort to do -->
</div>

</body>
<script type=text/javascript>
function ACTION()
{
var radioc = document.getElementsByName('fieldc');
var action = "";
var ii = 0;
var length = radioc.length;
for (ii = 0; ii < length; ii++)
  { 	if (radioc[ii].checked)
 	{ 	action = radioc[ii].value;
 	}
  }
if ( action == "" )
  {	MSG = "<H3>No action selected !</H3>"
	var ptr = document.getElementById("worktodo");
	ptr.innerHTML = MSG;
	return;
  }
switch (action) 
  {	case "create" :
				MSG = "CREATE USER";
				CREATE_AFFICHE();
				break;
	case "change" :
				MSG = "CHANGE PASSWORD";
				CHANGE_PASS_AFFICHE();
				break;
	case "list"   :
				MSG = "Users list";
				LIST_USERS_AFFICHE();
				break;
	case "delete" :
				MSG = "DELETE USER";
				DELETE_USER();
				break;
	default :
				MSG = "<H3>No action !!!!!</H3>";
				ptr.innerHTML = MSG;
  }
}
</script>

<script type=text/javascript>
// --------------------------------------------------------------------
function CALL_UPD(url,mode)
{
// alert("CALL_UPD url=" + url);
	$.ajaxSetup({async: false});
	$.get(url,function(data,status) 
	{ 	if ( status != "success" )
		{	alert("No data found or error : " + status + " / " + data);
		} else
		{	var ZZZ = data.split("!");
			ERR = ZZZ[0];
			MSG = ZZZ[1];
			var ptr = document.getElementById("worktodo");
			if ( ! ( ERR == 0 ) )
			{ // 	alert("CALL_UPD ERR=" + ERR);
				ptr.innerHTML = "<H4> Errorcode : " + ERR + " " + MSG + "</H4>";
			} else
			{	
//switch(mode)
//				{	// case "listuser" :
//					//	MANAGE_USERS_AFFICHE("<h5>" + MSG + "</h5>");
//					//	break;
//					case "showgroups" :
						ptr.innerHTML = MSG ;
//						break;
//					case "addgroup" :
//						break;
//					default :
//						ptr.innerHTML = "<h3>" + MSG + "</h3>" ;
//				}
			}
		}
	} ) ;

	$.ajaxSetup({async: true});
}
// -------------------------------------------------------------------- 
function CREATE_USER()
{	var username = document.getElementById("username").value;
	var password = document.getElementById("password").value;
	var email    = document.getElementById("email").value;
	
	var url = "./php/manage_users_functions.php?mode=create";
	url += "&username=" + username;
	url += "&password=" + password;
	url += "&email= " + email;
	CALL_UPD(url,"user");
	
}
//
function CREATE_AFFICHE()
{
	var dis_zone = ""
	dis_zone += "<H3>Create a new user</H3>";
	dis_zone += "<br>";
	dis_zone += "Username : <input type=text id=username size=16 required placeholder=Username>";
	dis_zone += "<br>";
	dis_zone += "Password : <input type=password id=password size=16 required placeholder=Password>";
	dis_zone += "<br>";
	dis_zone += "Amail : <input type=text id=email size=32 required placeholder=john.doe@nowehere.com>";
	dis_zone += "<br>";
	dis_zone += "<button type=button class=button onclick=CREATE_USER()>Action !</button>";
	var ptr = document.getElementById("worktodo");
	ptr.innerHTML = dis_zone;	
}
//-------------------------------------------------------------------- 
function CHANGE_PASS()
{	var username = document.getElementById("username").value;
	var password = document.getElementById("password").value;
	
	var url = "./php/manage_users_functions.php?mode=change";
	url += "&username=" + username;
	url += "&password=" + password;
	CALL_UPD(url,"change");
}
//
function CHANGE_PASS_AFFICHE()
{
	var dis_zone = ""
	dis_zone += "<H3>Change a password</H3>";
	dis_zone += "<br>";
	dis_zone += "Username : <input type=text id=username size=16 required placeholder=Username>";
	dis_zone += "<br>";
	dis_zone += "Password : <input type=password id=password size=16 required placeholder=Password>";
	dis_zone += "<br>";
	dis_zone += "<button type=button class=button onclick=CHANGE_PASS()>Action !</button>";
	var ptr = document.getElementById("worktodo");
	ptr.innerHTML = dis_zone;	
}
//-------------------------------------------------------------------- 
function LIST_USERS()
{	var username = document.getElementById("username").value;
	
	var url = "./php/manage_users_functions.php?mode=listuser";
	url += "&username=" + username;
	CALL_UPD(url,"listuser");
}
//
function LIST_USERS_AFFICHE()
{
	var dis_zone = ""
	dis_zone += "<H3>Users list</H3>";
	dis_zone += "<br>";
	dis_zone += "Username (or SQL pattern) : <input type=text id=username size=16 required placeholder=Username/pattern>";
	dis_zone += "<br><br>";
	dis_zone += "<button type=button class=button onclick=LIST_USERS()>Action !</button>";
	var ptr = document.getElementById("worktodo");
	ptr.innerHTML = dis_zone;	
}
//----------------------------------------------------------------------
function MANAGE_USERS_AFFICHE(MSG)
{
	var ptr = document.getElementById("worktodo");
	ptr.innerHTML = MSG;
}
function RESHOW_GROUPS()
{
// alert("RESHOW_GROUPS");
	var userid = document.getElementById('userid');
	var url = "./php/manage_users_functions.php?mode=showgroups&userid=" + userid;
	CALL_UPD(url,"showgroups");
	

}
function SHOW_GROUPS()
{
// alert("SHOW_GROUPS");
	var ptu    = document.getElementById("UserList");
	var userid = ptu.options[ptu.selectedIndex].value;
	var work   = ptu.options[ptu.selectedIndex].text;
	var arra   = work.split("/");
	var usern  = arra[0];
	var email  = arra[1];
	if ( userid < 1 )
	{	alert("No user selected");
		return;
	}
	var ptr = document.getElementById("worktodo");
	var url = "./php/manage_users_functions.php?mode=showgroups&userid=" + userid;
// alert("URL=" + url);
	CALL_UPD(url,"showgroups");
	document.getElementById("possible_groups").style.visibility = "visible";
}
//----------------------------------------------------------------------
function ADD_GROUP_FROM_USER()
{
// alert("ADD_GROUP_FROM_USER");
	var userid = document.getElementById("userid").value;
	var ptv    = document.getElementById("GroupList");
	var group  = ptv.options[ptv.selectedIndex].value;
 
	yyy = parseInt(userid);
	var zzz = Number.isInteger(yyy);
	if ( ! zzz )
	{	alert("Userid not numeric !!!!!");
		return;
	}
	if ( group.length < 3 )
	{	alert("No group selected ! ");
		return;
	}
	userid  = yyy;
	var url = "./php/manage_users_functions.php?mode=addgroupfromuser";
	url    += "&userid=" + userid + "&group=" + group;
// alert(url);
	CALL_UPD(url,"addgroupfromuser");
	document.getElementById("possible_groups").style.visibility = "visible";
	return;
}
//----------------------------------------------------------------------
function ADD_GROUP_AFFICHE()
{
//alert("ADD_GROUP_AFFICHE");
	var ptu    = document.getElementById("UserList");
	var userid = ptu.options[ptu.selectedIndex].value;
	var work   = ptu.options[ptu.selectedIndex].text;
	if ( userid == 0 )
	{	alert("No user selected");
		return;
	}
	document.getElementById("possible_groups").style.visibility = "visible";

	return;
}
function ADD_GROUP()
{
//alert("ADD_GROUP");
	var ptu    = document.getElementById("UserList");
	var userid = ptu.options[ptu.selectedIndex].value;
	var work   = ptu.options[ptu.selectedIndex].text;
	var ptv    = document.getElementById("GroupList");
	var group  = ptv.options[ptv.selectedIndex].value;
	if ( userid == 0 )
	{	alert("No user selected");
		return;
	}
	if ( group.length < 4 )
	{	alert("No group selected");
		return;
	}
	var url = "./php/manage_users_functions.php?mode=addgroup";
	url    += "&userid=" + userid;
	url    += "&group=" + group;
	CALL_UPD(url);
	RESHOW_GROUPS();
}
function DELETE_USER()
{
//alert ("Delete user");
	var ptu    = document.getElementById("UserList");
	var userid = ptu.options[ptu.selectedIndex].value;
	if ( userid < 1 )
	{	MSG = "No user selected !"
		alert(MSG);
		return;
	}
	var url  = "./php/manage_users_functions.php?mode=deleteuser";
	url     += "&userid=" + userid;

//alert("Url = " + url);
	CALL_UPD(url,"deleteuser");
}
function DROP_AFFICHE()
{
document.getElementById("button_drop").style.visibility = "visible";
}
function DROP_GROUP_FROM_USER()
{
alert("DROP_GROUP_FROM_USER");
	var userid = document.getElementById('userid').value;
	if ( userid < 1 )
	{	MSG = "No user selected !"
		alert(MSG);
		return;
	}
	var radioc = document.getElementsByName('Groups');
	var group  = "";
	var ii     = 0;
	var lng    = radioc.length;
	var work   = "";
	for (ii = 0; ii < lng; ii++)
		{ 	if (radioc[ii].checked)
 			{ 	work  = radioc[ii].value;
 			}
		}
	if ( work.length < 2 )
	{	alert("No group selected");
		return;
	}
	var aqwx  = work.split(" ");
	var group = aqwx[0];
	var url = "./php/manage_users_functions.php?mode=dropgroupfromuser";
	url += "&userid=" + userid + "&group=" + group;
	CALL_UPD(url,"dropgroupfromuser");
	document.getElementById("possible_groups").style.visibility = "visible";
}
</script>
</html>
