<html>
<head>
TEST
</head>
<body>
<?php
include './amilogged.php';
print ("<br> r=" . $ISRUNNING);
print ("<br> h=" . $ISHISTORY);
print ("<br> z=" . $ZZZ);
if  (       ( $ISEDITOR and $ISRUNNING == 1  ) 
		or  ( $ISADMIN ) 
	)
{ 	print("<br>DEL OK");
} else
{ 	print("<br>DEL KO");
}
print("<br>User=(" . $_SESSION['myname'] . ")");
if ( $ISADMIN ) { print("<br> ADMIN"); }
if ( ! $ISADMIN ) { print("<br>Not ADMIN"); }
if ( $ISEDITOR ) { print("<br> EDITOR"); }
?>
</body>
</html
