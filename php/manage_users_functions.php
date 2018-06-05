<?php
set_include_path(".:./php:./:../");
include 'db_auth.php';
include '../amilogged.php';
if ( ! isset($_SESSION) )
{   exit("1!<h3>Session error</h3>");
}
if ( ! $ISADMIN )
{   exit("2!<h3>Error : you are not ADMIN</h3> ");
}
$auth = new \Delight\Auth\Auth($pda);
$mode = "";
if ( !isset($_GET['mode']))
{   $MSG = "Unknown call mode";
    exit("2!" . $MSG);
} else
{   $mode = $_GET['mode'];
}
$ret = "999!No convenient mode";
//error_log("BEGIN MODE=(" . $mode . ")",0);
switch ($mode)
{
	case "addgroup" :
		$ret = ADD_GROUP($auth, $pda);
		break;
    case "create"   :
        $ret = CREATE_USER($auth,$pda);
        break;
    case "change"   :
        $ret = CHANGE_PASS($auth,$pda);
        break;
	case "listuser" :
		$ret = LIST_USERS($auth,$pda);
		break;
	case "showgroups" :
		$ret = SHOW_GROUPS($auth, $pda);
		break;
	case "deleteuser" :
		$ret = DELETE_USER($auth, $pda);
		break;
	case "dropgroupfromuser" :
		$ret = DROP_GROUP_FROM_USER($auth, $pda);
		break;
	case "addgroupfromuser" :
		$ret = ADD_GROUP_FROM_USER($auth, $pda);
		break;
	default :
		$ret = "99|Mode inconnu --> abort";
		break;
}
exit($ret);
/* --------------------------------------- */
function ADD_GROUP_FROM_USER($auth, $pda)
{
	if ( !isset($_GET['userid']) )
	{   $MSG = "No userid given";
    	return(70 . "!" . $MSG);
	} else
	{   $userid = $_GET['userid'];
	}
	if ( !isset($_GET['group']) )
	{   $MSG = "No group given";
    	return(71 . "!" . $MSG);
	} else
	{   $group  = $_GET['group'];
	}
/*	Create Group (or Role)   */
 	$ROLE = constant("\Delight\Auth\Role::" . $group);
try { 	$auth->admin()->addRoleForUserById($userid,$ROLE);
	} 	catch ( \Delight\Auth\UnknownIdException $e ) 
	{	$MSG = "Err registering group, no such user";
		exit(42 . "!" . $MSG);
	} 	catch ( Exception $e)
	{	$MSG = "Unknown error : " . $e;
		exit (43 . "!" . $MSG);
	}
/*	display group screen again */
	$ret = SHOW_GROUPS($auth, $pda);
	return($ret);
}
/* --------------------------------------- */
function DROP_GROUP_FROM_USER($auth,$pda)
{
$ERR = 1;
$MSG = "Entree drop_group";
	if ( !isset($_GET['userid']) )
	{   $MSG = "No userid given";
    	return(60 . "!" . $MSG);
	} else
	{   $userid = $_GET['userid'];
	}
	if ( !isset($_GET['group']) )
	{   $MSG = "No group given";
    	return(61 . "!" . $MSG);
	} else
	{   $group  = $_GET['group'];
	}
$ROLE = constant("\Delight\Auth\Role::" . $group);
try { 	$auth->admin()->removeRoleForUserById($userid,$ROLE );
	} catch (\Delight\Auth\UnknownIdException $e) 
	{ // unknown user ID
		$ERR = 62;
		$MSG = "Error dropping group : unknown userid ?";
		return($ERR . "!" . $MSG);
	}
$ERR = 0;
$MSG = "Group dropped ";
$ret = SHOW_GROUPS($auth, $pda);
return($ret);
}
/* --------------------------------------- */
function ADD_GROUP($auth, $pda)
{
	$MSG = " ";
	if ( !isset($_GET['userid']) )
	{   $MSG = "No userid given";
    	exit(40 . "!" . $MSG);
	} else
	{   $userid = $_GET['userid'];
	}
	if ( !isset($_GET['group']) )
	{   $MSG = "No group given";
    	exit(41 . "!" . $MSG);
	} else
	{   $group  = $_GET['group'];
	}
 	$ROLE = constant("\Delight\Auth\Role::" . $group);
try { 	$auth->admin()->addRoleForUserById($userid,$ROLE);
	} 	catch ( \Delight\Auth\UnknownIdException $e ) 
	{	$MSG = "Err registering group, no such user";
		exit(42 . "!" . $MSG);
	} 	catch ( Exception $e)
	{	$MSG = "Unknown error : " . $e;
		exit (43 . "!" . $MSG);
	}
$MSG = "Group " . $group . " added to userid " . $userid;
error_log("OSSEC-GUI " . $MSG,0);
return("0!" . $MSG);
}
/* --------------------------------------- */

function SHOW_GROUPS($auth, $pda)
{
//error_log("SHOW_GROUPS 1",0);
	if ( !isset($_GET['userid']))
	{   $ERR = 20;
		$MSG = "No userid given";
    	exit($ERR . "!" . $MSG);
	} else
	{   $userid = $_GET['userid'];
	}
$email = "????????";
$MSG = "Unknown error";
$ERR = 0;
$ii  = 0;
//error_log("SHOW_GROUPS 2",0);
$query = "select  id,email,username,roles_mask  from users where id = ". $userid ;
try {   $stmt = $pda->prepare($query) ;
		$stmt->execute();
	} catch (Exception $e)
	{	$ERR = 21;
		$MSG = "SHOW_GROUPS Error preparing query " . $e;
		exit($ERR . "!" . $MSG);
	}
try {  foreach ( $stmt->fetchALL() as $row )
		{ 	$userid = $row['id'];
			$usern  = $row['username'];
			$email  = $row['email'];
			$roles  = $row['roles_mask'];
			$ii = 1;
		} 
	} catch (Exception $e)
	{	$ERR = 22;
		$MSG = "SHOW_GROUPS Sqlerror during query : " . $e;
		exit($ERR . "!" . $MSG);
	}
//error_log("SHOW_GROUPS 3 ii=" . $ii,0);
if ( $ii == 0 )
	{	$ERR = 23;
		$MSG = "SHOW_GROUPS : query get 0 row";
		exit($ERR . "!" . $MSG);
	}
//error_log("SHOW_GROUPS 4 ",0);
		
$mainstring  = "<div class='display_user'> ";
$mainstring .= "Userid : <b>" . $userid  . "</b>  Name : <b>" . $usern . "</b> Mail : <b>" . $email . "</b>";
$mainstring .= "</b></div><br>";

$mainstring .= GET_ROLES_LIST($auth,$userid);
$mainstring .= "<div id=button_drop style=visibility:hidden>";
$mainstring .= "<input type=text id=userid value=" . $userid . " style=visibility:hidden>"; 
$mainstring .= "<br>";
$mainstring .= "<button type=button class=button_or onclick=DROP_GROUP_FROM_USER()>Drop from user</button>"; 
$mainstring .= "<br>";
$mainstring .= GROUP_LIST_BOX($auth, $pda);

$mainstring	.= "<button type=button class=button    onclick=ADD_GROUP_FROM_USER()>Add group</button>";
$mainstring .= "</div>";
return("0!" . $mainstring);
}
/* --------------------------------------- */
function GET_ROLES_LIST($auth,$userid)
{
try {	 $userroles = $auth->admin()->getRolesForUserById($userid);
	} catch (\Delight\Auth\UnknownIdException $e)
	{	$MSG = "GET_ROLES_LIST Unknowd userid : " . $userid;
		$ERR = 1;
		exit($ERR . "!" . $MSG);
	}
$mainstring  = "<div class='display_groups' id='display_groups' ";
$mainstring .= "<form onclick=DROP_AFFICHE();>";
$ii = 0;
$jj = 0;
foreach ( $userroles as $role )
	{	$ii +=1;
		$jj +=1;
		if ( $ii > 5 )
		{	$mainstring .= "<br>";
			$ii = 0;
		}
		$mainstring .= " <input type='radio' name=Groups id=Groups value='" . $role ;
		$mainstring .= " class='checkbox-inline>" . " " . $role;
	}
if ( $jj == 0 )
	{	$mainstring .= "<p class=_groups_err>User with no group</p>";
	} 
$mainstring .= "</form></div>";
return($mainstring);
}
/* --------------------------------------- */
function LIST_USERS($auth,$pda)
{
if ( !isset($_GET['username']))
	{   $ERR = 20;
		$MSG = "No username given";
    	exit($ERR . "!" . $MSG);
	} else
	{   $usern = $_GET['username'];
	}

$MSG = "Unknown error";
$ERR = 0;
$query = "select id as userid, email,username  from users where username like '%" . $usern . "%' order by username;";
try {   $stmt = $pda->prepare($query) ;
		$stmt->execute();
	} catch (Exception $e)
	{	$ERR = 21;
		$MSG = "Error preparing query " . $e;
		exit($ERR . "!" . $MSG);
	}
$mainstring  = "<h3>Users list</h3>";
$mainstring .= "<form name=zuserlist>\n";
$mainstring .= "<select name='UserList' id='UserList'>\n"; 
$mainstring .= "<option value='0' selected>--";
try {  foreach ( $stmt->fetchALL() as $row )
	   { 	$id     = $row['userid'];
			$email  = $row['email'] ;
			$usern  = $row['username'];
			$lng    = strlen($usern);
			$spaces = "";
			for ( $ii = 0 ; $ii < ( 20 - $lng); $ii++)
			{ 	$spaces .= "&nbsp;";
			}
			$mainstring .= "<option value=" . $id . ">" . $usern . $spaces . " / " . $email;
	   }
    } catch (Exception $e)
    {   $MSG = "Error reading users " . $e;
        error_log($MSG,0);
        exit(20 . "!" . $MSG);
    }
$mainstring .= "<div style=height:79px>";
$mainstring .= "</select>\n";
$mainstring .= "</form>";
/*	add buttons        */
$mainstring	.= "<button type=button class=button    onclick=SHOW_GROUPS()>Show groups</button>";
$mainstring	.= "<button type=button class=button_or onclick=DELETE_USER()>Delete User</button>";
$mainstring	.= "<button type=button class=button    onclick=ADD_GROUP_AFFICHE()>Add new group  </button>";
$mainstring .= "<br>";
$mainstring .= "</div>";
/* 	add list of groups */
/*
$arra = \Delight\Auth\Role::getMap();
$mainstring .= "<br><div id=possible_groups style=visibility:hidden> ";
$mainstring .= "<form name=zgroups>\n";
$mainstring .= "<select name=grouplist id=GroupList>";
$mainstring .= "<option value=-->--<br>";;

foreach ( $arra as $role )
	{	$mainstring .= "<option value=" . $role . ">" . $role . "<br>";
	}
$mainstring .= "</select></form>";
*/
$mainstring .= GROUP_LIST_BOX($auth, $pda);
$mainstring	.= "<button type=button class=button_or onclick=ADD_GROUP()>Ok to add</button>";
$mainstring .= "</div>";
exit(0 . "!" . $mainstring);
}
/* --------------------------------------- */
function GROUP_LIST_BOX($auth,$pda)
{
$arra = \Delight\Auth\Role::getMap();
$mainstring  = "<br><div id=possible_groups style=visibility:hidden> ";
$mainstring .= "<form name=zgroups>\n";
$mainstring .= "<select name=grouplist id=GroupList>";
$mainstring .= "<option value=-->--<br>";;

foreach ( $arra as $role )
	{	$mainstring .= "<option value=" . $role . ">" . $role . "<br>";
	}
$mainstring .= "</select></form>";
return($mainstring);
}
/* --------------------------------------- */
function CHANGE_PASS($auth,$pda)
{
if ( !isset($_GET['username']))
	{   $ERR = 100;
		$MSG = "No username given";
    	exit($ERR . "!" . $MSG);
	} else
	{   $usern = $_GET['username'];
	}
if ( !isset($_GET['password']))
    {   $ERR = 101;
		$MSG = "No password given";
        exit($ERR . "!" . $MSG);
    } else
    {   $passw = $_GET['password'];
    }
$ERR = 0;
$id = 0;
$query = "select id as userid from users where username = '" . $usern . "';";
try {   $stmt = $pda->prepare($query) ;
		$stmt->execute();
	} catch (Exception $e)
	{	$ERR = 10;
		$MSG = "Error preparing query " . $e;
		return($ERR . "!" . $MSG);
	}

try {  foreach ( $stmt->fetchALL() as $row )
	   {   $id = $row['userid'];
	   }
    } catch (Exception $e)
    {   $ERR = 11;
		$MSG = "Error reading user " . $e;
        error_log("OSSEC_GUI " . $MSG,0);
        exit($ERR . "!" . $MSG);
    }
$MSG = ": id=" . $id;
/* change password */
$ERR = 0;
try {
    $auth->admin()->changePasswordForUserById($id, $passw);
	} catch (\Delight\Auth\UnknownIdException $e) 
	{ // unknown ID
		$ERR = 11;
		$MSG = "Unknown ID ???";
	} catch (\Delight\Auth\InvalidPasswordException $e) 
	{ // invalid password
		$ERR = 12;
		$MSG = "Password invalide / too easy";
	}
if ( ! ($ERR == 0 ) )
    {	 return($ERR . "!" . $MSG);
    }
return("0!<h3>Password changed</h3>");
}
/* --------------------------------------- */
function DELETE_USER($auth, $pda)
{
	if ( !isset($_GET['userid']) )
	{   $MSG = "No userid given";
    	return(90 . "!" . $MSG);
	} else
	{   $userid = $_GET['userid'];
	}
try { $auth->admin()->deleteUserById($userid);
}
catch (\Delight\Auth\UnknownIdException $e) 
	{ // unknown ID
		$MSG = "DELETE_USER : unknown userid ";
		$ERR = 91;
		return($ERR . "!" . $MSG);
	}
return("0!User " . $userid . " deleted");
}
/* --------------------------------------- */
function CREATE_USER($auth,$pda)
{
if ( !isset($_GET['username']))
	{   $ERR = 110;
		$MSG = "No username given";
    	exit($ERR . "!" . $MSG);
	} else
	{   $usern = $_GET['username'];
	}
if ( !isset($_GET['password']))
    {   $ERR = 111;
		$MSG = "No password given";
        exit($ERR . '!' . $MSG);
    } else
    {   $passw = $_GET['password'];
    }
if ( ! isset($_GET['email']) )
    {	$ERR = 112;
		$MSG = "No email address giver";
	   	exit($ERR . "!" . $MSG);
    } else
    {	$email = $_GET['email'];
    }
/*  */
$MSG   = "??????????";
$ERR   = 0;
$userid = 0;

try {   $userid = $auth->admin()->createUser($email, $passw, $usern);
        $MSG   = 'All is OK, user created';
    } catch (\Delight\Auth\InvalidEmailException $e) 
    {    // invalid email address
        $MSG = 'Invalid email';
        $ERR = 1;
    } catch (\Delight\Auth\InvalidPasswordException $e) 
    {    // invalid password
        $MSG = 'Invalid password';
        $ERR = 2;
    } catch (\Delight\Auth\UserAlreadyExistsException $e) 
    {    // user already exists
        $MSG = 'User (or email) already exists';
        $ERR = 3;
    }
/* */
if ( ! ($ERR == 0 ) )
    {	 return($ERR . "!" . $MSG);
    }
/*  	Give the "default" group COLLABORATOR  */
$DEFAULTG = "COLLABORATOR";
$ROLE = constant("\Delight\Auth\Role::" . $DEFAULTG);
try { 	$auth->admin()->addRoleForUserById($userid,$ROLE);
	} 	catch ( \Delight\Auth\UnknownIdException $e ) 
	{	$MSG = "Err registering default group, no such user";
		exit(4 . "!" . $MSG);
	} 	catch ( Exception $e)
	{	$MSG = "Unknown error creating default group : " . $e;
		exit (5 . "!" . $MSG);
	}
	
return("0!User created with default group : " . $DEFAULTG);
}
?>
