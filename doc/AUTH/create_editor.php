<?php
require_once './db_auth.php';
require_once './auth/vendor/autoload.php';

$auth = new \Delight\Auth\Auth($pda);
/* First time create editor user
   Caution : Email validation is hard,, eg : "root@localhost' is not a valid email.
*/

$email = 'paul.doe@example.org';
$usern = 'editor';
$passw = 'editor';
$MSG   = "??????????";
$ERR   = 0;
try {
    $userId = $auth->admin()->createUser($email, $passw, $usern);
    // we have signed up a new user with the ID `$userId`
	$MSG   = 'All is OK, user created';
} catch (\Delight\Auth\InvalidEmailException $e) {
    // invalid email address
	$MSG = 'Invalid email';
	$ERR = 1;
} catch (\Delight\Auth\InvalidPasswordException $e) {
    // invalid password
	$MSG = 'Invalid password';
	$ERR = 2;
} catch (\Delight\Auth\UserAlreadyExistsException $e) {
    // user already exists
	$MSG = 'User already exists';
	$ERR = 3;
}
$MSG2 = "";
/* */
if ( $ERR == 0 ) 
{ 
	try {
    $auth->admin()->addRoleForUserByUsername($usern, \Delight\Auth\Role::EDITOR);
	$MSG2 = " Assigned to role EDITOR";
	} catch (\Delight\Auth\UnknownUsernameException $e) {
    // unknown username
		$MSG2 = "Can't assign user to role EDITOR, unknown user";
		$ERR = 4;
	} catch (\Delight\Auth\AmbiguousUsernameException $e) {
    // ambiguous username
		$MSG2 = "Ambiguous username";
		$ERR = 5;
	}
}
/*  */
?>
<html>
<head>
<title>Init AUTH</title>
</head>
<body>
Login
<?php 
print('<br>MSG=' . $ERR . " : " . $MSG . " " . $MSG2 . '<br>');
?>
</body>
</html>
