<?php
/*
 * Copyright (c) 2017 António 'Tó' Godinho <to@isec.pt>.
 * Copyright (c) 2019 JP P
 * This program is free software; Distributed under the terms of the GNU GPL v3.
 */
include './amilogged.php';
#	Table "data" suppressed for ossec V2.9 replaced by "alert"
require './top.php';
###  Get the criteria from the URL, these are used to populate the graph, and to populate the filter options further down


$where = "";

# input<var> = the raw GET
# filter<var> = for repopulating the filter toolbar
# where = the cumulative sql command
## filter criteria 'levelmin' and 'levelmax' 
if (isset($_GET['levelmin']) && preg_match("/^[0-9]+$/", $_GET['levelmin'])) {
    $inputlevelmin = filter_var($_GET['levelmin'],FILTER_VALIDATE_INT);
    $where .= "AND signature.level>=" . $inputlevelmin . " ";
} else {
    $inputlevelmin = "";
    $where .= "";
}
if (isset($_GET['levelmax']) && preg_match("/^[0-9]+$/", $_GET['levelmax'])) {
    $inputlevelmax = filter_var($_GET['levelmax'],FILTER_VALIDATE_INT);
    $where .= "AND signature.level<=" . $inputlevelmax . " ";
} else {
    $inputlevelmax = "";
    $where .= "";
}
$query = "SELECT distinct(level) FROM signature ORDER BY level";
$stmt = $pdo->prepare($query);
$stmt->execute();
$filterlevelmin = "";
$filterlevelmax = "";
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $selectedmin = "";
    $selectedmax = "";
    if ($row['level'] == $inputlevelmin) {
        $selectedmin = " SELECTED";
    }
    if ($row['level'] == $inputlevelmax) {
        $selectedmax = " SELECTED";
    }
    $filterlevelmin .= "<option value='" . $row['level'] . "'" . $selectedmin . ">>=" . $row['level'] . "</option>";
    $filterlevelmax .= "<option value='" . $row['level'] . "'" . $selectedmax . "><=" . $row['level'] . "</option>";
}


## filter from
if (isset($_GET['from']) && preg_match("/^[0-9\ ]+$/", $_GET['from'])) {
    $inputfrom = $_GET['from'];
    $filterfrom = $inputfrom;
    $f = explode(" ", $inputfrom);
    $sqlfrom = mktime(substr($f[0], 0, 2), substr($f[0], 2, 4), 0, substr($f[1], 2, 2), substr($f[1], 0, 2), substr($f[1], 4, 2));
    $where .= "AND alert.timestamp>=" . $sqlfrom . " ";
    //echo "58 => " . print_r($inputfrom) . " " .print_r($sqlfrom); // Godinho
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
    $t = explode(" ", $inputto);
    $sqlto = mktime(substr($t[0], 0, 2), substr($t[0], 2, 4), 0, substr($t[1], 2, 2), substr($t[1], 0, 2), substr($t[1], 4, 2));
    $lastgraphplot = $sqlto;
    $where .= "AND alert.timestamp<=" . $sqlto . " ";
    //echo "74 - " . print_r($inputto) . " " .print_r($sqlto); // Godinho
} else {
    $sqlto = "";
    $inputto = "";
    $filterto = $inputto;
    $where .= "";
}


## filter criteria 'source'
if (isset($_GET['source']) && strlen($_GET['source']) > 0) {
    $inputsource = quote_smart($_GET['source']);
    $where .= "AND location.name like '%" . $inputsource . "%' ";
} else {
    $inputsource = "";
    $where .= "";
}
$query = "SELECT distinct(substring_index(substring_index(name, ' ', 1), '->', 1)) as dname FROM location ORDER BY dname";
$filtersource = "";

$stmt = $pdo->prepare($query);
$stmt->execute();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $selected = "";
    if ($row['dname'] == $inputsource) {
        $selected = " SELECTED";
    }
    $filtersource .= "<option value='" . $row['dname'] . "'" . $selected . ">" . $row['dname'] . "</option>";
}

## filter criteria 'path'
if (isset($_GET['path']) && strlen($_GET['path']) > 0) {
    $inputpath = quote_smart($_GET['path']);
    $where .= "AND location.name like '%" . $inputpath . "%' ";
} else {
    $inputpath = "";
    $where .= "";
}
$query = "SELECT distinct(substring_index(name,'->',-1)) as dname FROM location ORDER BY dname;";
$filterpath = "";
$stmt = $pdo->prepare($query);
$stmt->execute();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $selected = "";
    if ($row['dname'] == $inputpath) {
        $selected = " SELECTED";
    }
    $filterpath .= "<option value='" . $row['dname'] . "'" . $selected . ">" . $row['dname'] . "</option>";
}


## filter rule_id
if (isset($_GET['rule_id']) && preg_match("/^[0-9,\ ]+$/", $_GET['rule_id'])) {
    $inputrule_id = filter_var($_GET['rule_id'],FILTER_VALIDATE_INT);
    $filterule_id = $inputrule_id;

    $inputrule_id_array = preg_split('/,/', $inputrule_id);

    $where .= "AND (1=0 ";
    $noterule_id = "";
    foreach ($inputrule_id_array as $value) {
        if (strlen($value) > 0) {
            $where .= "OR alert.rule_id=" . $value . " ";
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
    $inputdatamatch = filter_var($_GET['datamatch'],FILTER_SANITIZE_STRING);
    $filterdatamatch = $inputdatamatch;
    $where .= "AND alert.full_log like '%" . quote_smart($inputdatamatch) . "%' ";
} else {
    $inputdatamatch = "";
    $filterdatamatch = $inputdatamatch;
}

### filter input 'dataexclude'
# Current opinion is that this does not have to be 'safe' as we trust users who can access this
if (isset($_GET['dataexclude']) && strlen($_GET['dataexclude']) > 0) {
    $inputdataexclude = filter_var($_GET['dataexclude'],FILTER_SANITIZE_STRING);
    $filterdataexclude = $inputdataexclude;
    $where .= "AND alert.full_log not like '%" . quote_smart($inputdataexclude) . "%' ";
} else {
    $inputdataexclude = "";
    $filterdataexclude = $inputdataexclude;
}


### filter input 'datamatch'
if (isset($_GET['ipmatch']) && preg_match("/^[0-9\.]*$/", $_GET['ipmatch'])) {
    $inputipmatch = $_GET['ipmatch'];
    $filteripmatch = $inputipmatch;
    $where .= "AND alert.src_ip like '" . quote_smart($inputipmatch) . "%' ";
} else {
    $inputipmatch = "";
    $filteripmatch = $inputipmatch;
}

### filter input 'rulematch'
# Current opinion is that this does not have to be 'safe' as we trust users who can access this
if (isset($_GET['rulematch']) && strlen($_GET['rulematch']) > 0) {
    $inputrulematch = filter_var($_GET['rulematch'],FILTER_SANITIZE_STRING);
    $filterrulematch = $inputrulematch;
    $where .= "AND signature.description like '%" . quote_smart($inputrulematch) . "%' ";
} else {
    $inputrulematch = "";
    $filterrulematch = $inputrulematch;
}


### filter limit
if (isset($_GET['limit']) && is_numeric($_GET['limit']) && $_GET['limit'] < 1000) {
    $inputlimit = filter_var($_GET['limit'],FILTER_VALIDATE_INT);
} else {
    $inputlimit = $glb_detailtablelimit;
}


### filter alert 'categories'
if (isset($_GET['category']) && preg_match("/^[0-9]+$/", $_GET['category'])) {
    $inputcategory = $_GET['category'];
    $filtercagetory = $inputcategory;
    $where .= " AND category.cat_id=" . $inputcategory . " ";
    $wherecategory_tables = ", signature_category_mapping, category";
    $wherecategory_and = "and alert.rule_id=signature_category_mapping.rule_id
        and signature_category_mapping.cat_id=category.cat_id";
} else {
    $inputcategory = "";
    $wherecategory = " ";
    $wherecategory_tables = "";
    $wherecategory_and = "";
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
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">
    <title>OSSEC GUI</title>
    <?php
	require_once './db_ossec.php';
	include './run_his.php';
    include './page_refresh.php';
    ?>
    <link href="./css/style.css" rel="stylesheet" type="text/css"/>
    <link href="./css/sticky-footer.css" rel="stylesheet">
    <script src="./js/amcharts.js" type="text/javascript"></script>
    <script src="./js/serial.js" type="text/javascript"></script>
    <script src="./js/themes/light.js" type="text/javascript"></script>
    <script src="./js/sortable.js" type="text/javascript"></script>

    <script src="./js/jquery-3.3.1.js"></script>

    <script type="text/javascript">
        function setCookie(c_name, value, exdays) {
            var exdate = new Date();
            exdate.setDate(exdate.getDate() + exdays);
            var c_value = escape(value) + ((exdays == null) ? "" : "; expires=" + exdate.toUTCString());
            document.cookie = c_name + "=" + c_value;
        }

        function get_cookies_array() {
            var cookies = {};
            if (document.cookie && document.cookie != '') {
                var split = document.cookie.split(';');
                for (var i = 0; i < split.length; i++) {
                    var name_value = split[i].split("=");
                    name_value[0] = name_value[0].replace(/^ /, '');
                    cookies[decodeURIComponent(name_value[0])] = decodeURIComponent(name_value[1]);
                }
            }
            return cookies;
        }

        function databasetest() {
            <!--  If no data, alerts will be created in here  -->
            <?php
            #includeme('./databasetest.php')
            include './databasetest.php'
            ?>
        }

        <?php
        include './php/detail_graph.php';
        ?>

        var chart = AmCharts.makeChart("chartdiv", {
            type: 'serial',
            theme: 'light',
            dataProvider: chartData,
            categoryField: 'date',
            startDuration: 0.5,
            balloon: {
                color: '#000000'
            },
            zoomOutOnDataUpdate: true,
            pathToImages: './js/images/',
            zoomOutButton: true,
            zoomOutButtonColor: '#000000',
            zoomOutButtonAlpha: 0.15,
            categoryAxis: {
                fillAlpha: 1,
                fillColor: '#FAFAFA',
                gridAlpha: 0,
                axisAlpha: 0,
                gridPosition: 'start',
                position: 'top',
                parseDates: true,
                minPeriod: 'mm'
            },
            valueAxes: [
                {
                    title : 'Alerts',
                    logarithmic: <?php echo $glb_indexgraphlogarithmic; ?>
                }
            ],
            chartScrollbar: {
                updateOnReleaseOnly: true,
                //"graph" : graph0,
                scrollbarHeight: 40,
                color: '#000000',
                gridColor: '#000000',
                backgroundColor: '#FFFFFF',
                autoGridCount: true
            },
            chartCursor: {
                cursorPosition : 'mouse',
                categoryBalloonDateFormat : 'JJ:NN, DD MMMM'
            },
            legend: {
                markerType: 'circle'
            }
        });
        chart.validateNow();

        //chart.addListener("dataUpdated", zoomChart);

        <?php echo $graphlines; ?>

        function setPanSelect() {
            if (document.getElementById("rb1").checked) {
                chartCursor.pan = false;
                chartCursor.zoomable = true;
            } else {
                chartCursor.pan = true;
            }
        }
        chart.validateNow();
    </script>
    <script type="text/javascript">
	function ZDelId(id) {
		$.ajaxSetup({async: false});
		var ptr = document.getElementById('message');
		url = "delrow.php?id=" + id;
		$.get(url,function(data,status) 
		{ 	if ( status == "success" )
			{ 	var zzz = 0; 
			} else
			{	MSG = 'Error ' + status + ' / ' + data;
				ptr.innerHTML = MSG;
				ptr.style.display = 'block';
			}
		} );
		$.ajaxSetup({async: true});
		document.getElementById("click2go").submit();
		}
	</script>


</head>
<body onload="databasetest()">
<?php include './header.php'; ?>
<div class="container-fluid" style="padding-top: 60px;">
    <div class="row">
        <div id="chartdiv" style="width:100%; height:400px;"></div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <ul class="nav nav-pills" role="tablist" style="width: 100%;">
                <li role="presentation" class="active" style="width: 100%;"><a href="#"
                                                                               style="font-weight: 800">Filters</a>
                </li>
            </ul>
        </div>
    </div>

    <form id='click2go' method='GET' action='./detail.php' class="form-inline">
        <div class="row">
            <div class="col-lg-2 form-group">
                <label>RuleID</label>
            </div>
            <div class="col-lg-1 form-group">
                <label>Level Min</label>
            </div>
            <div class="col-lg-2 form-group">
                <label>From
                    <small>(HHMM DDMMYY)</small>
                </label>
            </div>
            <div class="col-lg-2 form-group">
                <label>Source</label>
            </div>
            <div class="col-lg-2 vc">
                <label>Data Match</label>
            </div>
            <div class="col-lg-1 vc">
                <label>IP Match</label>
            </div>
            <div class="col-lg-1 vc">
            </div>
        </div>
        <div class="row">
            <div class="col-lg-2 form-group">
                <input type='text' size='6' name='rule_id' autocomplete=999 value='<?php echo $filterule_id; ?>'
                       class="form-control input-sm"/>
            </div>
            <div class="col-lg-1 form-group">
                <select name='levelmin' class="form-control input-sm">
                    <option value=''>--</option>
                    <?php echo $filterlevelmin; ?>
                </select>
            </div>
            <div class="col-lg-2 form-group">
                <input type='text' size='16' name='from' value='<?php echo $filterfrom; ?>'
                       class="form-control input-sm"/>
            </div>
            <div class="col-lg-2 form-group">
                <select name='source' class="form-control input-sm">
                    <option value=''>--</option>
                    <?php echo $filtersource; ?>
                </select>
            </div>
            <div class="col-lg-2 vc">
                <input type='text' size='26' name='datamatch' value='<?php echo $filterdatamatch; ?>'
                       class="form-control input-sm"/>
            </div>
            <div class="col-lg-1 vc">
                <input type='text' size='10' name='ipmatch' value='<?php echo $filteripmatch; ?>'
                       class="form-control input-sm"/>
            </div>
            <div class="col-lg-1 vc">
            </div>
        </div>
        <div class="row">
            <div class="col-lg-2 form-group">
                <label>Category</label>
            </div>
            <div class="col-lg-1 form-group">
                <label>Level Max</label>
            </div>
            <div class="col-lg-2 form-group">
                <label>To
                    <small>(HHMM DDMMYY)</small>
                </label>
            </div>
            <div class="col-lg-3 form-group">
                <label>Path</label>
            </div>
            <div class="col-lg-1 vc">
                <label>Data Exclude </label>
            </div>
            <div class="col-lg-1 vc">
                <label>Rule Match</label>
            </div>
            <div class="col-lg-1 vc">
            </div>
        </div>
        <div class="row">
            <div class="col-lg-2 form-group">
                <select name='category' class="form-control input-sm">
                    <option value=''>--</option>
                    <?php echo $filtercategory; ?>
                </select>
            </div>
            <div class="col-lg-1 form-group">
                <select name='levelmax' class="form-control input-sm">
                    <option value=''>--</option>
                    <?php echo $filterlevelmax; ?>
                </select>
            </div>
            <div class="col-lg-2 form-group">
                <input type='text' size='10' name='to' value='<?php echo $filterto; ?>'
                       class="form-control input-sm"/>
            </div>
            <div class="col-lg-3 form-group">
                <select name='path' class="form-control input-sm">
                    <option value=''>--</option>
                    <?php echo $filterpath; ?>
                </select>
            </div>
            <div class="col-lg-1 vc">
                <input type='text' size='7' name='dataexclude' value='<?php echo $filterdataexclude; ?>'
                       class="form-control input-sm"/>
            </div>
            <div class="col-lg-1 vc">
                <input type='text' size='7' name='rulematch' value='<?php echo $filterrulematch; ?>'
                       class="form-control input-sm"/>
            </div>
            <div class="col-lg-1 vc">
                <input type='submit' value='..go' class="btn btn-warning"/>
            </div>
        </div>
    </form>
<!--    <br/> -->
    <div class="row">
        <div class="col-lg-12">
            <div><?php echo $noterule_id; ?></div>
        </div>
    </div>

    <?php
    # use this to store the main table as I want the 'Common Patterns' to be at the top but it needs processing at same time
    $mainstring = "";

#	Table "data" suppressed
    # Count the queries for the last line of the table.
    $querycounttable = "SELECT count(alert.id) as res_cnt
                            FROM alert, location, signature " . $wherecategory_tables . "
                            WHERE 1=1
                            " . $wherecategory_and . "
                            and alert.location_id=location.id
                            and alert.rule_id=signature.rule_id
                            " . $where;
try	{ 	$stmt = $pdo->prepare($querycounttable);
    	$stmt->execute();
    	$rowcounttable = $stmt->fetch(PDO::FETCH_ASSOC);
	} catch (Exception $e)
	{	print ("<br>Sql error : " . $e . ") <br>");
		print ("Query = (" . $querycounttable . ")");
		return;
	}

    $resultablerows = $rowcounttable['res_cnt'];

    # Fetch the actual rows of data for the table
    $querytable = "SELECT alert.id as id, alert.rule_id as rule, signature.level as lvl, alert.timestamp as timestamp, location.name as loc, alert.full_log as data, alert.src_ip as src_ip
     FROM alert, location, signature" . $wherecategory_tables . "
     WHERE 1=1
       and alert.location_id=location.id
       and alert.rule_id=signature.rule_id
      " . $where . "
      " . $wherecategory_and . "
      ORDER BY alert.timestamp DESC
      LIMIT " . $inputlimit;
try { 	$stmt = $pdo->prepare($querytable);
    	$stmt->execute();
	} catch (Exception $e)
	{ 	print ("Sqlerror : " . $e . " (" . $querytable . ")");	
	}

    $mainstring .= "<div class='newboxes toggled'><table class='dump sortable' id='sortabletable'  style='width:100%' >";
	if  (       ( $ISEDITOR and $ISRUNNING == 1  ) 
			or  ( $ISADMIN ) 
		)
	{ $mainstring .= "<tr> <th>Del?</th><th>ID</th><th>Rule</th><th>Lvl</th><th> Timestamp </th><th>Location</th><th>IP</th><th>Data</th> </tr>";
	} else
	{ $mainstring .= "<tr> <th>&nbsp;</th><th>ID</th><th>Rule</th><th>Lvl</th><th> Timestamp </th><th>Location</th><th>IP</th><th>Data</th> </tr>";
	}

    $rowcount = 0;

    # This sets up the ability to highlight keywords below
    $term = preg_replace('/\|+/', '|', trim($glb_autohighlight));
    $words = explode('|', $term);
    $highlighted = array();
    foreach ($words as $word) {
        $highlighted[] = "<span class='highlight'>" . $word . "</span>";
    }

    $mostcommonwords = array();
    $datasummary = array();

    while ($rowtable = $stmt->fetch(PDO::FETCH_ASSOC)) {

        # Dump each line to the table, be careful, this data is fromt the logs and should not be trusted
        if (isset($_GET['datamatch']) && strlen($_GET['datamatch']) > 0) {
            $tabledata = preg_replace("/(" . $_GET['datamatch'] . ")/i", '<span style="color:red">$1</span>', htmlspecialchars($rowtable['data']));
        } else {
            $tabledata = htmlspecialchars($rowtable['data']);
        }

        $rowcount++;
        $mainstring .= "<tr>";
	if  (       ( $ISEDITOR and $ISRUNNING == 1  ) 
			or  ( $ISADMIN ) 
		)
		{ 	$mainstring .= "<td> <img src=./images/delete_icon.png width=18 height=16 onclick='ZDelId(".$rowtable['id'].")'> </td>";
		} else
		{	$mainstring .= "<td> &nbsp; </td>";
		}
        $mainstring .= "<td>" . htmlspecialchars($rowtable['id']) . "</td>";
        $mainstring .= "<td>" . htmlspecialchars($rowtable['rule']) . "</td>";
        $mainstring .= "<td>" . htmlspecialchars($rowtable['lvl']) . "</td>";
        $mainstring .= "<td>" . date($glb_detailtimestamp, $rowtable['timestamp']) . "</td>";
        $mainstring .= "<td>" . htmlspecialchars(preg_replace("/ [0-9\.]*->/", " ", $rowtable['loc'])) . "</td>";

        # See if there is an IP assigned to alert
        $datatableip = $rowtable['src_ip'];
        if ($datatableip == "0.0.0.0") 
		{ 	$mainstring .= "<td></td>";
        } else 
		{ 	$mainstring .= "<td>" . $datatableip . "</td>";
        }

        # Process the full_log data
        $data = $rowtable['data'];
        $data = htmlspecialchars($data);
        $data = preg_replace("/(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})/", "<a href='ip_info.php?ip=$1'>$1</a>", $data);
        $data = str_replace($words, $highlighted, $data);
        $mainstring .= "<td class='highlighted-text' id='inflate' style='word-wrap:break-word;''>" . $data . "</td>";
        $mainstring .= "</tr>";

        $phraseline = preg_split("/ /", $rowtable['data']);
        foreach ($phraseline as $phrase) {
            $phrase2 = preg_replace("/=[a-zA-Z0-9\%\,\~\_\.\-]+&/", "=&", $phrase);
            # I have this hard coded as I think it will run faster than a glb_config array foreach loop
            if (
                preg_match("/^http/", $phrase2) # match web sites
                || preg_match("/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/", $phrase2) # match IP addresses
                || preg_match("/\w+\.\w+\.\w+/", $phrase2) # match... file paths?
                || preg_match("/^[A-Z_]+\/[0-9]+$/", $phrase2) # match HTTP return codes and proxy cache peer
            ) {
                if (!array_key_exists($phrase2, $datasummary)) {
                    $datasummary[$phrase2] = 0;
                } else {
                    $datasummary[$phrase2]++;
                }
            }
        }
    }
    $mainstring .= "</table></div>";

    # Dump cool phrases we found!
    arsort($datasummary);
    ?>
    <?php
    echo "<div class='newboxes toggled' id='commonpatterns' style='display: none;'>
				<table class='dump sortable' id='sortabletable'  style='width:100%' >
				<tr>
					<th>Count</th><th>Phrase</th>
				</tr>";
    $i = 0;
    foreach ($datasummary as $key => $value) {
        if ($i < $glb_commonpatternscount) {
            echo "<tr><td>" . number_format($value) . "</td><td><a class='numpty'>" . $key . "</a></td></tr>";
        }
        $i++;
    }
    echo "</table><div class='clr' style='border-top:20px;'>&nbsp;</div></div>";
    # Title
    ?>
<!--    <br/> -->
    <div class="row toggle" id='data'>
        <div class="col-lg-12">
            <ul class="nav nav-pills" role="tablist" style="width: 100%;">
                <li role="presentation" class="active" style="width: 100%;">
					<a href="#" style="font-weight: 800">Data</a>
                </li>
            </ul>
        </div>
    </div>
    <?php
    # This final line has to be a separate table for the 'sortable' to work
    echo "<table class='dump sortable' style='width:100%' >";
    if ($rowcount == 0) {
        echo "<tr><td><span style='color:red'>No data found, is your database populated?</span>.</td><td></td><td></td><td></td><td></td><td></td></tr>";
    } elseif ($rowcount == $glb_detailtablelimit) {
        echo "<tr><td colspan='6'><span style='color:red'>Search limited</span> to latest <span class='tw'>" . number_format($rowcount) . "</span> (of " . number_format($resultablerows) . ") results as per your global config. Please refine your search or increase the limit.</td></tr>";
    } else {
        echo "<tr><td colspan='6'>" . number_format($rowcount) . " records shown.</td></tr>";
    }

    $detail2csv_get = preg_replace("/.*php\?/", "", $_SERVER["REQUEST_URI"]);
    echo "<tr><td><a href='./detail2csv.php?" . $detail2csv_get . "'>Download all " . number_format($resultablerows) . " results as CSV</a></td></tr>";
    echo "</table>";

    # Now print main data table
    echo "
                            $mainstring
                    ";

    # Show the SQL?
    if ($glb_detailsql == 1) {
        #	For niceness show the SQL queries, just incase you want to dig deeper your self
        echo "<div class='clr' style='padding-bottom:20px;'></div>
                                    <div class='fleft top10header'>SQL (Chart)</div>
                                    <div class='fleft tiny' style=''>" . htmlspecialchars($querychart) . "</div>";

        echo "<div class='clr' style='padding-bottom:20px;'></div>
                                    <div class='fleft top10header'>SQL (Table)</div>
                                    <div class='fleft tiny' style=''>" . htmlspecialchars($querytable) . "</div>";
    }
    ?>
</div>

<div class='row'></div>

<?php
include 'footer.php';
?>
<script language="JavaScript">
    $(document).ready(function () {
        $('.toggle').click(function () {
            id = $(this).parent().attr("id");
            toggled = $(this).parent().find(".toggled");

            toggled.slideToggle('fast', function () {
                cookie = (toggled.is(":hidden")) ? "0" : "1";
                setCookie("hideshow" + id, cookie, "100");
            });
        });
        $.fn.highlight = function (what, spanClass) {
            return this.each(function () {
                var container = this,
                    content = container.innerHTML,
                    pattern = new RegExp('(>[^<.]*)(' + what + ')([^<.]*)', 'g'),
                    replaceWith = '$1<span ' + ( spanClass ? 'class="' + spanClass + '"' : '' ) + '">$2</span>$3',
                    highlighted = content.replace(pattern, replaceWith);
                container.innerHTML = highlighted;
            });
        }
        $('.numpty').click(function () {
            $('.highlighted-text').highlight($(this).text(), 'highlight');
        });
    });
</script>
<!-- ID=INFLATE  -->
	<script language="javascript">
{
$("#inflate*").hover(
	function() // on mouseover
	{ 	$( this).css('font-size', 18 ); }, 
	function() // on mouseout
	{ 	$( this).css('font-size', 14 ); }
);
$(".inflate*").hover(
	function() // on mouseover
	{ 	$( this).css('font-size', 18 ); }, 
	function() // on mouseout
	{ 	$( this).css('font-size', 14 ); }
);
}
</script>
<div id=message style="border:1px solid red;display: none;">
</div>
</body>
</html>

