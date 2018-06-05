<html>
<head>
<title>logout</title>
<link href="./css/login.css" rel="stylesheet" type="text/css">
<body>
<p>
</p>
</body>
<div class=zone style=height:100px align=center>
<p >
<h2>Logout</h2>
</p>
<?php
require_once "./db_auth.php";
$ERR = 0;
try {
    $auth->logOutEverywhereElse();
}
catch (\Delight\Auth\NotLoggedInException $e) 
{ 	// not logged in
	print("<H4>Error : You were not logged in !</h4>");
	exit();
}
print("<H4>Success : You log out</H4>");
$IPADDR = $_SERVER['REMOTE_ADDR'];
$MSG    = "OSSEC-GUI Logout " . $_SESSION['myname'] . " from " . $IPADDR;
error_log($MSG,0);
session_destroy();
?>
</div>
</body>
</html>
