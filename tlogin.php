<?php
/*
 * Copyright (c) 2019 JP P
 * This program is free software; Distributed under the terms of the GNU GPL v3.
 */
if ( ! isset($_SESSION) )
{   session_start();
}
if ( isset($_SESSION['MYPATH']) )
{   $MYPATH = $_SESSION['MYPATH'];
} else
{   $MYPATH = $_SERVER['REQUEST_URI'];
	$_SESSION['MYPATH'] = $MYPATH;
}
require_once './db_auth.php';

$ERR  = 0;
$MSG  = "";

if ( isset($_GET['usern']) )
	{	$usern = $_GET['usern'];
    } else
	{	$ERR = 1;
		$MSG = 'No username provided';
    }
if ( isset($_GET['passw']) )
	{	$passw = $_GET['passw'];
    } else
	{	$ERR = 2;
		$MSG = 'No password provided';
    }
if ( $ERR != 0 )
{	print ($ERR . "/" . $MSG);
	exit($ERR);
}
$ERR = 0;
$MSG = "";
$IPADDR = $_SERVER['REMOTE_ADDR'];
try 
{ 	$auth->loginWithUsername($usern,$passw);
	# $auth->loginWithUsername("admin","Admin1234");
} catch (\Delight\Auth\InvalidEmailException $e) 
{ // wrong email address
	$ERR = 1;
	$MSG = "Invalid email address : " . $usern;
} catch (\Delight\Auth\InvalidPasswordException $e) 
{ // invalid password
	$ERR = 2;
	$MSG = "Password does not match !";
} catch (\Delight\Auth\EmailNotVerifiedException $e) 
{ // email not verified	
	$ERR = 3;
	$MSG = "Email not verified ";
} catch (\Delight\Auth\UnknownUsernameException $e)
{ //  Unknown username 	
	$ERR = 4;
	$MSG = "Username not found : " . $usern;
} catch (\Delight\Auth\TooManyRequestsException $e) 
{ // too many requests
	$ERR = 10;
	$MSG = "Too many requests";
	$BAD = "Login : " . $usern . " " . $MSG . " from IP : " . $IPADDR;
	error_log($BAD,0);
}
if ( $ERR <> 0 )
{	print($ERR . "/" . $MSG);
	exit($ERR);
}
print ($ERR . "/" . $MSG);
$_SESSION['myname'] = $usern;
$MSG2 = "OSSEC-GUI User login " . $usern . " from " . $IPADDR; 
error_log($MSG2,0); 
exit(0);
?>
