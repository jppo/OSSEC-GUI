<?php
/*
 * Copyright (c) 2017 António 'Tó' Godinho <to@isec.pt>.
 * Copyright (c) 2018 JP P
 * This program is free software; Distributed under the terms of the GNU GPL v3.
 */

# This code is taken directly from detail.php (but may be a bit behind)

require_once './top.php';
$where = "";

# input<var> = the raw GET
# filter<var> = for repopulating the filter toolbar
# where = the cumulative sql command

## filter criteria 'levelmin' and 'levelmax' 
if (isset($_GET['levelmin']) && preg_match("/^[0-9]+$/", $_GET['levelmin'])) {
    $inputlevelmin = $_GET['levelmin'];
    $where .= " AND AL.level>=" . $inputlevelmin . " ";
} else {
    $inputlevelmin = "";
    $where .= "";
}
if (isset($_GET['levelmax']) && preg_match("/^[0-9]+$/", $_GET['levelmax'])) {
    $inputlevelmax = $_GET['levelmax'];
    $where .= " AND AG.level<=" . $inputlevelmax . " ";
} else {
    $inputlevelmax = "";
    $where .= "";
}


## filter from
if (isset($_GET['from']) && preg_match("/^[0-9\ ]+$/", $_GET['from'])) {
    $inputfrom = $_GET['from'];
    $filterfrom = $inputfrom;
    $f = explode(" ", $inputfrom);
    $sqlfrom = mktime(substr($f[0], 0, 2), substr($f[0], 2, 4), 0, substr($f[1], 2, 2), substr($f[1], 0, 2), substr($f[1], 4, 2));
    $where .= " AND AL.timestamp>=" . $sqlfrom . " ";
} else {
    $sqlfrom = "";
    $inputfrom = "";
    $filterfrom = $inputfrom;
    $where .= "";
}


## filter to
if (isset($_GET['to']) && preg_match("/^[0-9\ ]+$/", $_GET['to'])) {
    $inputto = $_GET['to'];
    $filterto = $inputto;
    $t = split(" ", $inputto);
    $sqlto = mktime(substr($t[0], 0, 2), substr($t[0], 2, 4), 0, substr($t[1], 2, 2), substr($t[1], 0, 2), substr($t[1], 4, 2));
    $lastgraphplot = $sqlto;
    $where .= "AND AL.timestamp<=" . $sqlto . " ";
} else {
    $sqlto = "";
    $inputto = "";
    $filterto = $inputto;
    $where .= "";
}

## filter criteria 'source'
if (isset($_GET['source']) && strlen($_GET['source']) > 0) {
    $inputsource = $_GET['source'];
    $where .= "AND LO.name like '" . $inputsource . "%' ";
} else {
    $inputsource = "";
    $where .= "";
}

## filter criteria 'path'
if (isset($_GET['path']) && strlen($_GET['path']) > 0) {
    $inputpath = $_GET['path'];
    $where .= "AND LO.name like '" . $inputpath . "%' ";
} else {
    $inputpath = "";
    $where .= "";
}


## filter rule_id
if (isset($_GET['rule_id']) && preg_match("/^[0-9,\ ]+$/", $_GET['rule_id'])) {
    $inputrule_id = $_GET['rule_id'];
    $filterule_id = $inputrule_id;

    $inputrule_id_array = preg_split('/,/', $inputrule_id);

    $where .= "AND ( 1 = 0 ";
    $noterule_id = "";
    foreach ($inputrule_id_array as $value) {
        if (strlen($value) > 0) {
            $where .= "OR AL.rule_id = " . $value . " ";
        }

        $query = "select signature.description from signature where rule_id=" . $value;
		$stmt = $pdo->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $noterule_id .= "<span style='font-weight:bold;' >Rule " . $value . "</span>: " . $row['description'] . "<br/>";
    }
    $where .= ")";

} else {
    $inputrule_id = "";
    $filterule_id = $inputrule_id;
    $where .= "";
    $noterule_id = "";
}


### filter input 'datamatch'
# Current opinion is that this does not have to be 'safe' as we trust users who can access this
if (isset($_GET['datamatch']) && strlen($_GET['datamatch']) > 0) {
    $inputdatamatch = $_GET['datamatch'];
    $filterdatamatch = $inputdatamatch;
    $where .= "AND AL.full_log like '%" . quote_smart($inputdatamatch) . "%' ";
} else {
    $inputdatamatch = "";
    $filterdatamatch = $inputdatamatch;
}

### filter input 'dataexclude'
# Current opinion is that this does not have to be 'safe' as we trust users who can access this
if (isset($_GET['dataexclude']) && strlen($_GET['dataexclude']) > 0) {
    $inputdataexclude = $_GET['dataexclude'];
    $filterdataexclude = $inputdataexclude;
    $where .= "AND AL.full_log not like '%" . quote_smart($inputdataexclude) . "%' ";
} else {
    $inputdataexclude = "";
    $filterdataexclude = $inputdataexclude;
}


### filter input 'datamatch'
if (isset($_GET['ipmatch']) && preg_match("/^[0-9\.]*$/", $_GET['ipmatch'])) {
    $inputipmatch = $_GET['ipmatch'];
    $filteripmatch = $inputipmatch;
    $where .= " AND AL.src_ip like '%" . quote_smart($inputipmatch) . "%' ";
} else {
    $inputipmatch = "";
    $filteripmatch = $inputipmatch;
}

### filter input 'rulematch'
# Current opinion is that this does not have to be 'safe' as we trust users who can access this
if (isset($_GET['rulematch']) && strlen($_GET['rulematch']) > 0) {
    $inputrulematch = $_GET['rulematch'];
    $filterrulematch = $inputrulematch;
    $where .= "AND SG.description like '%" . quote_smart($inputrulematch) . "%' ";
} else {
    $inputrulematch = "";
    $filterrulematch = $inputrulematch;

}

### filter limit
if (isset($_GET['limit']) && is_numeric($_GET['limit']) && $_GET['limit'] < 1000) {
    $inputlimit = $_GET['limit'];
} else {
    $inputlimit = $glb_detailtablelimit;
}

# No more "data" table
$querytable = "SELECT AL.id as id, AL.rule_id as rule, SG.level as lvl, AL.timestamp as timestamp, LO.name as loc, AL.full_log as data, AL.src_ip as src_ip
FROM alert AL, location LO, signature SG
WHERE 1 = 1
and AL.location_id = LO.id
and AL.rule_id = SG.rule_id " . $where . "
ORDER BY AL.timestamp DESC ;";
$querytable = preg_replace('/\t/', ' ', $querytable);
$querytable = preg_replace('/\n/', ' ', $querytable);
try 
	{	$stmt = $pdo->prepare($querytable);
		$stmt->execute();
	} catch (Exception $e)
	{	print ("<br>Sql error : " . $e . ") <br>");
		print ("Query = (" . $querytable . ")");
		return;
	}

header("Content-type: text/csv");
header("Cache-Control: no-store, no-cache");
header('Content-Disposition: attachment; filename="Ossec_Wui_output_' . time() . '.csv"');
$counter = 0;
echo "Lineno;";
echo "DatabaseID;";
echo "Rule;";
echo "Level;";
echo "Timestamp;";
echo "Location;";
echo "IP;";
echo "Data;";
echo "\n";


try
{ 	foreach ($stmt->fetchALL() as $rowtable) 
	{ 	$counter += 1;
		echo $counter . "	;"; 
		echo htmlspecialchars($rowtable['id']) . ";";
    	echo htmlspecialchars($rowtable['rule']) . ";";
    	echo htmlspecialchars($rowtable['lvl']) . ";";
    	echo date($glb_detailtimestamp, $rowtable['timestamp']) . ";";
    	echo $rowtable['loc'] . ";";
		$src_ip = $rowtable['src_ip'];
		if ( $src_ip == "(null)" ) { $src_ip = "-";}
    	echo $src_ip . ";";
    	echo preg_replace('/\n/', ' ',$rowtable['data']). ";";
    	echo "\n";
	}
} catch (Exception $e)
{	print("Counter = " . $counter . "\n");
	print("SQLERROR : " . $e . "\n");
	print($querytable . "\n");
}
?>
