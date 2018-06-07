<?php
/*
 * Copyright (c) 2017 António 'Tó' Godinho <to@isec.pt>.
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

### Deleting Section
if (isset($_GET['action']) && $_GET['action'] == 'delete' && preg_match("/\/management.php/", $_SERVER['HTTP_REFERER'])) {
    # Yes I know the referer is fakable, but this is to help reduce CSRF attacks from remote links, and not to prevent malicious browsers

    $where = "";
    # delete ruleid
	if (isset($_GET['rule_id']) && is_numeric($_GET['rule_id']) && strlen($_GET['rule_id']) > 0)
	{ 	$where .= "alert.rule_id=" . filter_var($_GET['rule_id'],FILTER_SANITIZE_STRING) . " AND ";
	}

    # deletelevel
    if (isset($_GET['level']) && is_numeric($_GET['level']) && $_GET['level'] > 0) 
	{ 	$where .= "signature.level=" . filter_var($_GET['level'],FILTER_VALIDATE_INT) . " AND ";
    }

    # deletebefore
    if (isset($_GET['before']) && is_numeric($_GET['before']) && $_GET['before'] > 0) 	
	{ 	$where .= "alert.timestamp<" . $_GET['before'] . " AND ";
    }
    # delete source
    if (isset($_GET['source']) && strlen($_GET['source']) > 0) 
	{ 	$where .= "location.name like \"" . $_GET['source'] . "%\" AND ";
    }
    # delete path
    if (isset($_GET['path']) && strlen($_GET['path']) > 0) 
	{ 	$where .= "location.name like \"%" . $_GET['path'] . "\" AND ";
    }
    # delete data
    if (isset($_GET['datamatch']) && strlen($_GET['datamatch']) > 0) 
	{ 	$where .= "alert.full_log like \"%" . filter_var($_GET['datamatch'],FILTER_SANITIZE_STRING) . "%\" AND ";
    }

    $query = "";
    # Only run if paramters set, do NOT empty the database!
    if (strlen($where) > 0) 
	{
        # remove the last 'AND '
        $where = substr($where, 0, -4);
#	Table "data" suppressed
        $querydelete = "DELETE alert FROM alert
			LEFT JOIN signature ON alert.rule_id=signature.rule_id
			LEFT JOIN location ON alert.location_id=location.id
			WHERE " . $where;
### syntax error ### $resultdelete = mysql_query($querydelete, $db_ossec);
		$stmt = $pdo->prepare($querydelete);
		$stmt->execute();
#
	}
        if ($glb_detailsql == 1) 
	{
            #	For niceness show the SQL queries, just incase you want to dig deeper your self
		echo "<div class='clr' style='padding-bottom:20px;'></div>
		<div class='fleft top10header'>SQL (" . $resultdelete . ")</div>
		<div class='fleft tiny' style=''>" . htmlspecialchars($querydelete) . "</div>";
        }
    }

### Removing a location
if (isset($_GET['action']) && $_GET['action'] == 'removelocation' && isset($_GET['source']) && strlen($_GET['source']) > 0 && preg_match("/\/management.php/", $_SERVER['HTTP_REFERER'])) 
	{
    # Yes I know the referer is fakable, but this is to help reduce CSRF attacks from remote links, and not to prevent malicious browsers
    # Delete alert
    $querydelete = "DELETE alert FROM alert
		LEFT JOIN signature ON alert.rule_id=signature.rule_id
		LEFT JOIN location ON alert.location_id=location.id
		WHERE location.name like \"" . $_GET['source'] . "%\"";
#### syntax error ####    $resultdelete = mysql_query($querydelete, $db_ossec);
	$stmt = $pdo->prepare($querydelete);
	$stmt->execute();
#
    if ($glb_detailsql == 1) 
		{
        #For niceness show the SQL queries, just increase if you want to dig deeper your self
        	echo "<div class='fleft top10header'>SQL (" . $resultdelete . ")</div>
			<div class='fleft tiny' style=''>" . htmlspecialchars($querydelete) . "</div>
			<div class='clr' style='padding-bottom:20px;'></div>";
    	}

    # Delete location
    $querydelete = "DELETE FROM location
		WHERE location.name like \"" . $_GET['source'] . "%\"";
### syntax error ###    $resultdelete = mysql_query($querydelete, $db_ossec);
	$stmt = $pdo->prepare($querydelete);
	$stmt->execute();
#
    if ($glb_detailsql == 1) 
	{
        #For niceness show the SQL queries, just incase you want to dig deeper your self
        echo "<div class='fleft top10header'>SQL (" . $resultdelete . ")</div>
			<div class='fleft tiny' style=''>" . htmlspecialchars($querydelete) . "</div>
			<div class='clr' style='padding-bottom:20px;'></div>";
    }
}


### Oldest alert
$query = "SELECT alert.timestamp as age
	FROM alert
	ORDER BY timestamp
	LIMIT 1";
$stmt = $pdo->prepare($query);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$oldestalert = $row['age'];

# Get all clients for dropdown
$query = "SELECT distinct(substring_index(substring_index(name, ' ', 1), '->', 1)) as dname FROM location ORDER BY dname";
$stmt = $pdo->prepare($query);
$stmt->execute();
$filtersource = "";
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) 
{ 	$filtersource .= "<option value='" . $row['dname'] . "'>" . $row['dname'] . "</option>";
}

# Get paths for dropdown
$query = "SELECT distinct(substring_index(name,'->',-1)) as dname FROM location ORDER BY dname;";
$stmt = $pdo->prepare($query);
$stmt->execute();
$filterpath = "";
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) 
{ 	$filterpath .= "<option value='" . $row['dname'] . "'>" . $row['dname'] . "</option>";
}

# Get all levels for dropdowns
$query = "SELECT distinct(level) FROM signature ORDER BY level";
$stmt = $pdo->prepare($query);
$stmt->execute();
$filterlevel = "";
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) 
{ 	$filterlevel .= "<option value='" . $row['level'] . "'>" . $row['level'] . "</option>";
}

# Make dropdown 'Before'
$filterbefore = "";
for ($i = 0; $i < 48; $i++) 
{ 	$timestamp = mktime(0, 0, 0, date('n') - $i, 1);
    $filterbefore .= "<option value='" . $timestamp . "'>" . date("M Y", $timestamp) . "</option>";
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
    include "page_refresh.php";
	?>
	<?
	include "run_his.php";
    ?>
    <link href="./css/style.css" rel="stylesheet" type="text/css"/>
    <link href="./css/sticky-footer.css" rel="stylesheet">
    <script src="./js/amcharts.js" type="text/javascript"></script>
    <script src="./js/serial.js" type="text/javascript"></script>

<?php
	if ( DB_TYPE_O == 'history' )
    { print '<script src="./js/themes/chalk.js" type="text/javascript"></script>';
	} else
    { print '<script src="./js/themes/dark.js" type="text/javascript"></script>';
	}
?>

    <script type="text/javascript">
	function databasetest() 
	{
	<!--  If no data, alerts will be created in here  -->
		<?php include './databasetest.php' ?>
	}
    </script>
</head>

<body onload="databasetest(); ">
<!-- <?php include './header_management.php'; ?>
-->
<div class="container-fluid" style="padding-top: 60px;">

    <div>
        <ul class="nav nav-pills" role="tablist" style="width: 100%;">
            <li role="presentation" class="active" style="width: 100%;"><a href="#"
                                                                           style="font-weight: 800">Contents</a></li>
        </ul>
    </div>
    <div style="padding:10px;">
        <div class="contents"><a href='./management.php#intro'>Intro</a></div>
        <div class="contents"><a href='./management.php#agents'>Last Agent Alert</a></div>
        <div class="contents"><a href='./management.php#databasesummary'>Database Size Summary</a></div>
        <div class="contents"><a href='./management.php#databasecleanup'>Database cleanup</a></div>
        <div class="contents"><a href='./management.php#removelocation'>Remove Location (OSSEC client)</a></div>
<!--
        <div class="contents"><a href='./management.php#categorymapping'>Manage table signature_category_mapping</a></div>
-->
    </div>

    <a name="intro"></a>
    <div class="row">
        <div class="col-lg-4">
            <ul class="nav nav-pills" role="tablist" style="width: 100%; background-color: black; color: white;">
                <li role="presentation" class="active" style="width: 100%;"><a href="#"
                     style="font-weight: 800; background-color: black; color: white;">Intro</a>
                </li>
            </ul>
        </div>
    </div>
<!--    <br/> -->

    <div class="introbody">All of this reflects the data held in your SQL database and is not linked in anyway to the
        flat file logs written by OSSEC.
    </div>

    <div class="introbody">This page is to help manage your OSSEC database.</div>

    <div class="introbody">I advise you first look at 'Rule Tweaking', as prevention is better than a cure. This section
        will help identify which rules are taking the most space and might even help point 
		to areas where you can adapt the rules to your needs.
    </div>

    <div class="introbody">The section 'Database Size Summary' helps identify which 
		client is submitting the most data of a specific level.
    </div>

    <div class="introbody">The section 'Database Cleanup' should only be used when 
		the other sections have been exhausted.
        After tweaking your rules, and identifying where most space is used, 
		this section will allow you to PERMANENTLY DELETE data from your database.
    </div>


    <a name="agents"></a>
    <div class="row">
        <div class="col-lg-4">
            <ul class="nav nav-pills" role="tablist" style="width: 100%; background-color: black; color: white;">
                <li role="presentation" class="active" style="width: 100%;"><a href="#"
                    style="font-weight: 800; background-color: black; color: white;">Last Agent Alert</a>
				</li>
            </ul>
        </div>
    </div>
<!--    <br/> -->
    <div class="introbody">Looking for Agents that have no alerts in the last <span
                class='tw'><?php echo $glb_management_checkin; ?></span> Hours. If you have deleted Alerts this may give a misleading result. <br>This will NOT display agents that have NEVER connected.
    </div>
    <div style="padding:10px;">
        <?php include './php/management_agentcheckin.php'; ?>
    </div>

    <a name="ruletweaking"></a>
    <div class="row">
        <div class="col-lg-4">
            <ul class="nav nav-pills" role="tablist" style="width: 100%; background-color: black; color: white;">
                <li role="presentation" class="active" style="width: 100%;"><a href="#"
                   style="font-weight: 800; background-color: black; color: white;">Rule Tweaking</a>
				</li>
            </ul>
        </div>
    </div>
<!--    <br/> -->
    <div class="introbody">These are the <span class='tw'><?php echo $glb_managementtweaking; ?></span> most common rule hits, per system, in the database. Investigate to see if these rules can be further tuned to remove unnecessary alerting ?
    </div>

    <div style="padding:10px;">
        <?php include './php/management_commonrules.php' ?>
    </div>

    <a name="databasesummary"></a>
    <div class="row">
        <div class="col-lg-4">
            <ul class="nav nav-pills" role="tablist" style="width: 100%; background-color: black; color: white;">
                <li role="presentation" class="active" style="width: 100%;"><a href="#"
                  style="font-weight: 800; background-color: black; color: white;">Database Summary</a>
				</li>
            </ul>
        </div>
    </div>
<!--   -->
    <?php include 'php/management_databasesize.php'; ?>

    <a name="databasecleanup"></a>
    <div class="row">
        <div class="col-lg-4">
            <ul class="nav nav-pills" role="tablist" style="width: 100%; background-color: black; color: white;">
				<li role="presentation" class="active" style="width: 100%;"><a href="#"
                    style="font-weight: 800; background-color: black; color: white;">Database Cleanup</a>
				</li>
            </ul>
        </div>
    </div>
<!--    <br/> -->
    <div class="introbody">Use this section to clean the database of old/unimportant alerts. Examples:
<!-- </div>
    <div class="introbody"> -->
        <li>Delete alerts which are older than your retention requirements (6 months or older?)
        <li>Delete all alerts for 'Server XYZ'
        <li>Delete all alerts level 5 or below
        <li>Delete all rule 5104 that is older than 2 months
        <li>Delete all proxy logs that are older than 4 months
    </div>
    <div style="padding:10px;">
        <form method='GET' action='./management.php?action=delete'>
            <input type='hidden' name='action' value='delete'>
            <div class='fleft filters'>
                RuleID<br/>
                <input type='text' size='6' name='rule_id' value='' style='font-size:12px'/>
            </div>
            <div class='fleft filters'>
                Level<br/>
                <select name='level' style='font-size:12px'>
                    <option value='0'>--</option>
                    <?php echo $filterlevel ?>
                </select>
            </div>
            <div class='fleft filters'>
                Before <br/>
                <select name='before' style='font-size:12px'>
                    <option value=''>--</option>
                    <?php echo $filterbefore ?>
                </select>
            </div>
            <div class='fleft filters'>
                Source<br/>
                <select name='source' style='font-size:12px'>
                    <option value=''>--</option>
                    <?php echo $filtersource ?>
                </select>
            </div>
            <div class='fleft filters'>
                Path<br/>
                <select name='path' style='font-size:12px'>
                    <option value=''>--</option>
                    <?php echo $filterpath ?>
                </select>
            </div>
            <div class='fleft filters'>
                Data Match<br/>
                <input type='text' size='32' name='datamatch' value='' style='font-size:12px'/>
            </div>
            <div class='fleft filters'>
                <br/>
                <input type='submit' value='..delete'/>
            </div>
        </form>
    </div>

    <div class='clr' style="margin-top:10px;"></div>

    <a name="removelocation"></a>
    <div class="row">
        <div class="col-lg-4">
            <ul class="nav nav-pills" role="tablist" style="width: 100%; background-color: black; color: white;">
				<li role="presentation" class="active" style="width: 100%;">
					<a href="#" style="font-weight: 800; background-color: black; color: white;">Remove Location (OSSEC client)</a>
				</li>
            </ul>
        </div>
    </div>
<!--    <br/> -->
    <div class="introbody">Used to remove locations that no longer exist.</div>
    <div class="introbody">
        <li>This will remove ALL traces of this location from the database
        <li>This should only be used on locations that are will no longer connect to OSSEC
        <li>If you accidentally remove a location, you will have to restart the OSSEC service to re import, though all
            SQL data will be lost (flat file logs will not be affected)
    </div>
    <div style="padding:10px;">
        <form method='GET' action='./management.php?'>
            <input type='hidden' name='action' value='removelocation'>
            <div class='fleft filters'>
                Source<br/>
                <select name='source' style='font-size:12px'>
                    <option value=''>--</option>
                    <?php echo $filtersource; ?>
                </select>
            </div>
            <div class='fleft filters'>
                <br/>
                <input type='submit' value='..remove'/>
            </div>
        </form>
    </div>
    <div class='clr'></div>

    <?php
    include 'footer_mgt.php';
    ?>
    </script>
</body>
</html>
