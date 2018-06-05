<?php
# require_once './db_auth.php';

session_start();

?>
<html>
<head>
<title>OSSEC-WUI login</title>
</head>
<link href="./css/login.css" rel="stylesheet" type="text/css">
<script src="./js/jquery-3.3.1.js"></script>
<script type="text/javascript">
function TEXEC(data,status)
{
	var tab = data.split("/");
	var codret  = tab[0];
	var messerr = tab[1];
//	alert("Codret=" + codret);
	if ( codret != 0 )
	{	var ptr = document.getElementById("lemessage") ;
		ptr.innerHTML = messerr;
		if ( codret < 10 )
		{	// goon.replaceWith("Try again"); 
			var ptr = document.getElementById("goon") ;
			ptr.innerHTML = "Try again";
		} else
		{ 	// goon.replaceWith("Really ! Disconnect and retry later")
			var ptr = document.getElementById("goon") ;
			ptr.innerHTML = "Really ! Disconnect and retry later";
		}
	} else
	{	var ptr = document.getElementById("lemessage") ;
		ptr.innerHTML = "Super you have got it";
		var ptr = document.getElementById("goon") ;
		var referrer = document.referrer;
		ptr.innerHTML = referrer;
//		alert("Goto " + referrer);
		window.location.href = referrer;
		
	}
}
function TLOGIN()
{	
	var username  = document.getElementById("username").value;;
	var zpassword = document.getElementById("passw").value;
	var url = './tlogin.php?usern=' + username ; 
	url     = url + '&passw=' + zpassword;
	$.ajaxSetup({async: false});
	$.get(url,function(data,status) 
	{ 	if ( status != "success" )
		{	alert("No data found or error : " + status + " / " + data);
		} else
		{	
			TEXEC(data,status);
		}
	} ) ;

	$.ajaxSetup({async: true});

}
</script>
<body>
<?php
if ( isset( $_SERVER['HTTP_REFERER']) )
{	$REFER = $_SERVER['HTTP_REFERER'];
} else
{	$REFER = "";
}
if ( $REFER == "" )
{	
	print("<div class=zone style=height:80px>");
	print("<h3 align=center>Do not call me directly</h3>");
	exit(0);
} else
{
print('<div class="zone">');
print('<div class=message name=message id=message>');
print('<p id=lemessage name=lemessage>');
print('Please login with your username and password');
print('</p>');
print('<p id=goon name=goon>');
print('&nbsp;');
print('</p>');
print('</div>');
print('<table class="login" >');
print('<tr>	<td>Username </td>');
		print('<td><input type=text id=username size=16 required placeholder="username"> </td>');
print('</tr>');
print('<tr> 	<td>Password </td>');
print('<td><input type=password id=passw  size=16 required placeholder="password"></td>');
print('</tr>');
print('<th colspan=2> <button type=button class="button" onclick="TLOGIN()">Login</button>');
print('</th>');
print('</table>');
print('<!-- -->');
print('</div>');
}
?>
</body>
</html>
