<?php
/*
 * Copyright (c) 2017 António 'Tó' Godinho <to@isec.pt>.
 * Copyright (c) 2018 JP P
 * This program is free software; Distributed under the terms of the GNU GPL v3.
 */

require "./config.php";

// FILTER BEGIN
require './top.php';

## filter criteria 'level'
if (isset($_GET['level']) && preg_match("/^[0-9]+$/", $_GET['level'])) {
    $inputlevel = filter_var($_GET['level'],FILTER_VALIDATE_INT);
} else {
    $inputlevel = $glb_level;
}
$filterlevel = "";
$query = "SELECT distinct(level) FROM signature ORDER BY level";
try
{ 	$stmt = $pdo->prepare($query);
	$stmt->execute();
} catch (Exception $e)
{	$MSG = "Sqlerror " . $e;
	error_log($MSG,0);
	print ($MSG);
	$MSG = "Sql (" + $query . ")";
	error_log($MSG,0);
	print($MSG);
	return;
}
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $selected = "";
    if ($row['level'] == $inputlevel) {
        $selected = " SELECTED";
    }
    $filterlevel .= "<option value='" . $row['level'] . "'" . $selected . ">" . $row['level'] . " +</option>";
}

## filter from
if (isset($_GET['hours']) && preg_match("/^[0-9]+$/", $_GET['hours'])) {
    $inputhours = filter_var($_GET['hours'],FILTER_VALIDATE_INT);
} else {
    $inputhours = $glb_hours;
}

## filter category
if (isset($_GET['category']) && preg_match("/^[0-9]+$/", $_GET['category'])) {
    $inputcategory = $_GET['category'];
    $wherecategory = " AND category.cat_id=" . $inputcategory . " ";
} else {
    $inputcategory = "";
    $wherecategory = " ";
}
$query = "SELECT *
	FROM category
	ORDER BY cat_name";
$filtercategory = "";
$stmt = $pdo->prepare($query);
$stmt->execute();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $selected = "";
    if ($row['cat_id'] == $inputcategory) {
        $selected = " SELECTED";
    }
    $filtercategory .= "<option value='" . $row['cat_id'] . "'" . $selected . ">" . $row['cat_name'] . "</option>";
}


## filter
$radiosource = "";
$radiopath = "";
$radiolevel = "";
$radiorule_id = "";
if ( isset($_GET['field'] ) )
{	$field = $_GET['field'];
} else
{	$field = $glb_graphbreakdown;
}
switch($field)
{	case 'path' 	:	$radiopath = 'checked';
						break;
	case 'level' 	:	$radiolevel = 'checked';
						break;
	case 'rule_id' 	: 	$radiorule_id = 'checked';
						break;
	case 'source'  	: 	$radiosource = 'checked';
						break;
	default :			$radiosource = 'checked';
						break;
}

// FILTER END


if ($glb_debug == 1) {
    $starttime_indexchart = microtime();
    $startarray_indexchart = explode(" ", $starttime_indexchart);
    $starttime_indexchart = $startarray_indexchart[1] + $startarray_indexchart[0];
}

$mainstring = "// -------------------------------------------------";
$keyprepend = "";
$notrepresented = array();

# counting in hours/days may get slow on larger databases, so grouping is done in blocks of 10^x seconds
if ($inputhours < 4) {
    $substrsize = 8;
    $zeros = "00";
} elseif ($inputhours < 48) {
    $substrsize = 7;
    $zeros = "000";
} else {
    $substrsize = 6;
    $zeros = "0000";
}

# to make the graph plot position in the middle of the time field..
$halfperiod = intval("1" . $zeros) / 2;

# To filter on 'Category' (SSHD) extra table needs adding, but they slow down the query for other things, so lets only put them into the SQL if needed....
if (strlen($wherecategory) > 1) {
    $wherecategory_tables = ", signature_category_mapping, category";
    $wherecategory_and = "and alert.rule_id=signature_category_mapping.rule_id
        and signature_category_mapping.cat_id=category.cat_id";
} else {
    $wherecategory_tables = "";
    $wherecategory_and = "";
}

# The graph data 'series' can be broken down in several ways
# graphheightmultiplier is just a tweak as some fields are generally longer than others.
if ((isset($_GET['field']) && $_GET['field'] == 'path') || (!isset($_GET['field']) && $glb_graphbreakdown == "path")) {

    $graphheightmultiplier = 5;
    $keyprepend = "";
	$ZON1 = "(concat(substring(alert.timestamp, 1, $substrsize), '$zeros')+" . $halfperiod . ")";
	error_log($ZON1,0);
	exit(100);
    $querychart = "select (concat(substring(alert.timestamp, 1, $substrsize), '$zeros')+" . $halfperiod . ") as res_time, count(alert.id) as res_cnt, SUBSTRING_INDEX(location.name, '->', -1) as res_field
		from alert, location, signature " . $wherecategory_tables . "
		where signature.level>=$inputlevel
		and alert.location_id=location.id
		and alert.rule_id=signature.rule_id
		" . $wherecategory_and . "
		and alert.timestamp>" . (time() - ($inputhours * 3600)) . "
		" . $wherecategory . " 
		" . $glb_notrepresentedwhitelist_sql . "
		group by (concat(substring(alert.timestamp, 1, $substrsize), '$zeros')+" . $halfperiod . "),
		 SUBSTRING_INDEX(location.name, '->', -1)
		order by (concat(substring(alert.timestamp, 1, $substrsize), '$zeros')+" . $halfperiod . "),
		 SUBSTRING_INDEX(location.name, '->', -1)";
} elseif ((isset($_GET['field']) && $_GET['field'] == 'level') || (!isset($_GET['field']) && $glb_graphbreakdown == "level")) {
    $graphheightmultiplier = 2;
    $keyprepend = "Lvl: ";
    $querychart = "select (concat(substring(alert.timestamp, 1, $substrsize), '$zeros')+" . $halfperiod . ") as res_time, count(alert.id) as res_cnt, signature.level as res_field
		from alert, location, signature " . $wherecategory_tables . "
		where signature.level>=$inputlevel
		and alert.location_id=location.id
		and alert.rule_id=signature.rule_id
		" . $wherecategory_and . "
		and alert.timestamp>" . (time() - ($inputhours * 3600)) . "
		" . $wherecategory . " 
		" . $glb_notrepresentedwhitelist_sql . "
		group by (concat(substring(alert.timestamp, 1, $substrsize), '$zeros')+" . $halfperiod . "), signature.level
		order by (concat(substring(alert.timestamp, 1, $substrsize), '$zeros')+" . $halfperiod . "), signature.level";
} elseif ((isset($_GET['field']) && $_GET['field'] == 'rule_id') || (!isset($_GET['field']) && $glb_graphbreakdown == "rule_id")) {
    $graphheightmultiplier = 8;
    $keyprepend = "";
    $querychart = "select (concat(substring(alert.timestamp, 1, $substrsize), '$zeros')+" . $halfperiod . ") as res_time, count(alert.id) as res_cnt, CONCAT(alert.rule_id, ' ', signature.description) as res_field
		from alert, location, signature " . $wherecategory_tables . "
		where signature.level>=$inputlevel
		and alert.location_id=location.id
		and alert.rule_id=signature.rule_id
		" . $wherecategory_and . "
		and alert.timestamp>" . (time() - ($inputhours * 3600)) . "
		" . $wherecategory . " 
		" . $glb_notrepresentedwhitelist_sql . "
		group by (concat(substring(alert.timestamp, 1, $substrsize), '$zeros')+" . $halfperiod . "),
		 CONCAT(alert.rule_id, ' ', signature.description)
		order by (concat(substring(alert.timestamp, 1, $substrsize), '$zeros')+" . $halfperiod . "),
		 CONCAT(alert.rule_id, ' ', signature.description)";
} else {
    # Default is source

    $graphheightmultiplier = 1;
    $keyprepend = "";
    $querychart = "select (concat(substring(alert.timestamp, 1, $substrsize), '$zeros')+" . $halfperiod . ") as res_time, count(alert.id) as res_cnt, SUBSTRING_INDEX(SUBSTRING_INDEX(location.name, ' ', 1), '->', 1) as res_field
		from alert, location, signature " . $wherecategory_tables . "
		where signature.level>=$inputlevel
		and alert.location_id=location.id
		and alert.rule_id=signature.rule_id
		" . $wherecategory_and . "
		and alert.timestamp>" . (time() - ($inputhours * 3600)) . "
		" . $wherecategory . " 
		" . $glb_notrepresentedwhitelist_sql . "
		group by (concat(substring(alert.timestamp, 1, $substrsize), '$zeros')+" . $halfperiod . "),
		 SUBSTRING_INDEX(SUBSTRING_INDEX(location.name, ' ', 1), '->', 1)
		order by (concat(substring(alert.timestamp, 1, $substrsize), '$zeros')+" . $halfperiod . "),
		 SUBSTRING_INDEX(SUBSTRING_INDEX(location.name, ' ', 1), '->', 1)";
}
$query = preg_replace('/\t/', ' ', $query);
$query = preg_replace('/\n/', ' ', $query);
if ( $glb_debug == 1 )
{	print('<br>' + $query);
}
try
{ 	$stmt = $pdo->prepare($querychart);
	$stmt->execute();
} catch (Exception $e)
{	$MSG = "Sqlerror : " . $e . " on \n " . $querychart;
	error_log($MSG,0);
	exit("fatal error querychart");
}
$tmpdate = "";
$timegrouping = array();
$arraylocations = array();
$arraylocationsunique = array();

$mainstring = "var chartData = [\n";

$first = 0;
$datafound = 0;

## Informal note, I hate this section of code, it will be rewritten.
while ($rowchart = $stmt->fetch()) {

    $datafound = 1;

    # We have data, so empty the var on this load
    $glb_nodatastring = "";

    # XXX Compile a list of all hosts, maybe a better way to do this than have an array the size of the alert table
    $fieldname = substr(preg_replace($glb_hostnamereplace, "", $rowchart['res_field']), 0, 35);
    if (strlen($fieldname) == 35) {
        $fieldname .= "...";
    }

    array_push($arraylocations, $fieldname);


    # for the first run, this needs setting
    if ($first == 0) 
	{ 	$first = 1;
        $tmpdate = intval($rowchart['res_time']);
    }

    # This alert is a new time 'group'...
    if ($tmpdate != $rowchart['res_time'] && $rowchart['res_time'] > 1) 
	{ # ...so what we have compiled needs to go to 'mainstring' (remember to use tmpdate, not the latest row time)
        $mainstring .= "		{date: new Date(" . date("Y", $tmpdate) . ", " . (date("m", $tmpdate) - 1) . ", " . date("j", $tmpdate) . ", " . date("G", $tmpdate) . ", " . date("i", $tmpdate) . "), ";

        foreach ($timegrouping as $key => $val) 
		{ 	#append this location to array
            $mainstring .= "'" . $key . "': " . $val . ", ";
        }

        $mainstring = substr($mainstring, 0, -2);
        $mainstring .= "},
	";

        # clear the array we have used to collect counts for a specific time 'group'
        unset($timegrouping);

        # reset the working time 'group' so the next if will be fired and we start collecting for the next time 'group'
        $tmpdate = $rowchart['res_time'];
    }

    # Oh look, this alert matches the time 'group' we are collecting for.
    if ($rowchart['res_time'] == $tmpdate) {
        $timegrouping[$fieldname] = $rowchart['res_cnt'];
    }
}


# We have to run this cycle one more time to process the last row

if ($tmpdate > 1) {
    $mainstring .= "		{date: new Date(" . date("Y", $tmpdate) . ", " . (date("m", $tmpdate) - 1) . ", " . date("j", $tmpdate) . ", " . date("G", $tmpdate) . ", " . date("i", $tmpdate) . "), ";

    foreach ($timegrouping as $key => $val) {
        #append this location to array
        $mainstring .= "'" . $key . "': " . $val . ",";
    }

    # the last date point on the graph becomes the last data, so if no data the graph effectively stalls. Adding an empty entry at the end will keep the graph up to date.
    $timedown = time();
    $mainstring .= "},
			{date: new Date(" . date("Y", $timedown) . ", " . (date("m", $timedown) - 1) . ", " . date("j", $timedown) . ", " . date("G", $timedown) . ", " . date("i", $timedown) . "), 'now':1, ";

    # Clean the variable
    $mainstring = substr($mainstring, 0, -2);
    $mainstring .= "},
	";
}

# If no end date, presume now, make graph end at today instead of auto scaling, so add a value for today
# -1 months is a naughty workaround as javascript counts months from 0
#if(strlen($inputto)==0){
#	$mainstring.="		{date: new Date(".date("Y, n, j, G, i", strtotime('-1 month'))."), 'now': 1},  ";
#}
# dump what we have collected
if ( strlen($mainstring) > 20 )
{ 	$mainstring = substr($mainstring, 0, -3);
}
$mainstring .= "
		];";


$nochartdata = "";
if ($glb_debug == 1) {
    $nochartdata .= "<div style='font-size:24px; color:red;'>Debug</div>";
    $nochartdata .= $querychart;

    $endtime_indexchart = microtime();
    $endarray_indexchart = explode(" ", $endtime_indexchart);
    $endtime_indexchart = $endarray_indexchart[1] + $endarray_indexchart[0];
    $totaltime_indexchart = $endtime_indexchart - $starttime_indexchart;
    $nochartdata .= "<div>Took " . round($totaltime_indexchart, 1) . " seconds</div>";
} elseif ($datafound == 0) 
{ 	echo $mainstring;

    # See if there was data, if not then drop some test output to the main chartdiv, just for happiness
    # 1 mysql module isntalled?
    # 2 mysql connectable?
    # 3 database look like it has right schema?
    # 4 any data in there?

    $nochartdata = "";
    $problem = 0;

    if (extension_loaded('pdo_mysql')) 
	{ 	$sqlmodule = "yes";
    } else {
        $problem = 1;
        $sqlmodule = "no!<br/>";
        $sqlmodule .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Fix - https://www.google.pt/search?q=pdo_mysql";
    }
#
#	Test database format
	try
	{	$schag = checkSchema('agent');
		$schal = checkSchema('alert');
		$schca = checkSchema('category');
		$schlo = checkSchema('location');
		$schse = checkSchema('server');
		$schsi = checkSchema('signature');
		$schsc = checkSchema('signature_category_mapping');
		$databaseschema = "Ok";
	} catch (PDOException $e) 
	{ 	$databaseschema = "no!<br/>";
        $databaseschema .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Fix - Import the MySQL schema that comes with OSSEC";
   	}

#
#	Test tables content
    try 
	{ 	$pdo = new PDO('mysql:host=' . DB_HOST_O . ';dbname=' . DB_NAME_O . ';charset=utf8', DB_USER_O, DB_PASSWORD_O);
        $mysqlconnect = "yes";
        $retal = checktable('alert');
		$retlo = checktable('location'); 
		$retsi = checktable('signature'); 
		if ( $retal > 0  && $retlo > 0 )
		{ 	$anydata = "yes";
        } else 
		{ 	$anydata = "no!<br/>";
#            $anydata .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Fix - Ensure agents are logging data.";
        }
		# error_log("Anydata = ".$anydata);
		$problem = 0;
    	} catch (PDOException $e) 
		{ 	$problem = 1;
        	$mysqlconnect = "no!".$e."<br/>";
        	$mysqlconnect .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Fix - ";
    	}
#
        if ($retal == 0) 
		{ 	$nochartdata .= "<div>No data found, but everything checks out ok. Try broadening your search parameters.</div>";
        } else 
		{ 	$nochartdata = "
		<div style='font-size:24px; color:red;'>No Chart Data Found</div>
		<div style='padding-bottom:10px;'>There is no data available for this query, running diagnostics...</div>
		<div>Test 1 - Can PHP detect MySQL module? - " . $sqlmodule . "</div>
		<div>Test 2 - Can PHP connect to your MySQL? - " . $mysqlconnect . "</div>
		<div>Test 3 - Does your database have correct schema? - " . $databaseschema . "</div>
		<div>Test 4 - Is there any data in your database? - " . $anydata . "</div>";
        }
} else 
{
    echo htmlspecialchars($mainstring);
}

function checktable($table)
{
    $query = "SELECT count(*) from " . $table . ";";
    try 
	{ 	$pdo = new PDO('mysql:host=' . DB_HOST_O . ';dbname=' . DB_NAME_O . ';charset=utf8', DB_USER_O, DB_PASSWORD_O);
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
		# error_log("Checktable ".$table." OK");
        return 0;
   	} catch (PDOException $e) 
	{ 	errorlog("Check table ".$table." KO");
		return 1;
   	}
    return 0;
}

function checkSchema($table)
{
    try 
	{ 	$pdo = new PDO('mysql:host=' . DB_HOST_O . ';dbname=' . DB_NAME_O . ';charset=utf8', DB_USER_O, DB_PASSWORD_O);
    	$query = "SELECT count(*) from " . $table . ";";
        $stmt  = $pdo->prepare($query);
        $stmt->execute();
        $row   = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) 
	{ 	error_log("Erreur checkSchema (".$query." Err=".$e);
        return 1;
    }
	# error_log("checkSchema ".$query." OK");
    return 0;
}

$arraylocationsunique = array_unique($arraylocations);
asort($arraylocationsunique);

## Right now define each series of data with a name and settings
$graphcount = 0;
$graphlines = "";
$linecolour = "";
if ($datafound == 1) {
    foreach ($arraylocationsunique as $i => $location) {

        if (isset($_GET['field']) && $_GET['field'] == 'level') {
            # Get a colour based on the level
            $linecolour = "graph" . $i . ".lineColor = \"" . $levelcolours["level" . $location] . "\";";
        } elseif (isset($_GET['field']) && $_GET['field'] == 'source') 
		{	if ( isset($devicegroup[$location]) ) 
			{	if (isset($groupcolour[$devicegroup[$location]]) )
			  { 	if ($groupcolour[$devicegroup[$location]] <> '') 
				{ 	# Get a colour for specific servers
                    $linecolour = "graph" . $i . ".lineColor = \"" . $groupcolour[$devicegroup[$location]] . "\";";
                } else 
				{ # Get a colour for a server where you didn't specify one
                    $linecolour = "graph" . $i . ".lineColor = \"" . $randomcolour[array_rand($randomcolour)] . "\";";
                }
            }
			}
        } else 
		{ # Dont specify, let amcharts choose
            $linecolour = "";
        }

        $graphcount++;
        $graphlines .= '
			// GRAPHS
			// Graph ' . $i . '
			var graph' . $i . ' = new AmCharts.AmGraph();
			graph' . $i . '.title = "' . $keyprepend . $location . '";
			graph' . $i . '.valueField = "' . $location . '";
			graph' . $i . '.bullet = "round";
			graph' . $i . '.bulletSize = 10;
			graph' . $i . '.bulletBorderThickness = 10;
			graph' . $i . '.hideBulletsCount = 30;
			graph' . $i . '.balloonText = "' . $keyprepend . $location . ' : [[value]]";
			graph' . $i . '.lineThickness = 1;
			graph' . $i . '.dashLength = 3;
			' . $linecolour . '
			chart.addGraph(graph' . $i . ');
	';
        $notrepresented[$location] = 1;
    }
}

if ($glb_indexgraphkey == 1) {
    # Only run this if user wants a key, if no key and hundreds of items then dont just scale to a huge graph
    # As I cannot see a way for amcharts to be in a dynamic height graph.... lets use PHP to adjust it on page load...
    $graphheight = "
    $( document ).ready(function() {
        $('#chartdiv').css({ 'height': '" . ($glb_height_index + ($graphcount * $graphheightmultiplier)) . "px' });
    });";
}

## Lets colour out of hours in a nice shade of 'glb_outofhourscolour'!
$workinghoursguide = "";
$daysago = ceil($inputhours / 24);
for ($i = $daysago; $i >= 0; $i--) {
    $guidedate = date("j", strtotime('-' . $i . ' days'));
    $guidemonth = date("n", strtotime('-' . $i . ' days')) - 1;


    if (date('N', strtotime('-' . $i . ' days')) == 6 || date('N', strtotime('-' . $i . ' days')) == 7) {
        # If in here, then the day value (1-7) of $i days ago was a Sat or a Sun			
        $workinghoursguide .= "
		// GUIDE - Weekend
		{
		    id : 'guide . $i .',
		    date : new Date(2018, " . $guidemonth . ", " . $guidedate . ", 0, 0),
		    toDate : new Date(2018, " . $guidemonth . ", " . $guidedate . ", 23, 59),
		    fillColor : '" . $glb_outofhourscolour . "',
		    inside : false,
		    fillAlpha : 0.2,
		    lineAlpha : 0,
		    label : 'Weekend',
		    labelRotation : 90
		},
		";
    } else {
        # If in here then the day value indicates this is a weekday
        $workinghoursguide .= "
		// GUIDE - Non working hours
		// day value = " . date('N', strtotime('-' . $i . ' days')) . " am
		{
		    id : 'guide" . $i . "am',
		    date : new Date(2018, " . $guidemonth . ", " . $guidedate . ", 0, 1),
		    toDate : new Date(2018, " . $guidemonth . ", " . $guidedate . ", " . $glb_outofhours_daystart . ", 0),
		    fillColor : '" . $glb_outofhourscolour . "',
		    inside : false,
		    fillAlpha : 0.2,
		    lineAlpha : 0
		},
		{
		    id : 'guide" . $i . "pm',
		    date : new Date(2018, " . $guidemonth . ", " . $guidedate . ", " . $glb_outofhours_dayend . ", 0),
		    toDate : new Date(2018, " . $guidemonth . ", " . $guidedate . ", 23, 59),
		    fillColor : '" . $glb_outofhourscolour . "',
		    inside : false,
		    fillAlpha : 0.2,
		    lineAlpha : 0,
		    label : 'Nighttime',
		    labelRotation : 90
		},
		";
    }
}
?>
