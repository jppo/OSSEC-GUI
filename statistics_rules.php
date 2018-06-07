<?php
/*
 * Copyright (c) 2017 António 'Tó' Godinho <to@isec.pt>.
 * Copyright (c) 2018 JP P
 * This program is free software; Distributed under the terms of the GNU GPL v3.
 */

#	Table "data" suppressed for ossec V2.9 replaced by "alert"
require './top.php';
include './amilogged.php';

###  Get the criteria from the URL, these are used to populate the graph, and to populate the filter options further down

$where = "";

# input<var> = the raw GET
# filter<var> = for repopulating the filter toolbar
# where = the cumulative sql command
## filter criteria 'levelmin'  
if (isset($_GET['levelmin']) && preg_match("/^[0-9]+$/", $_GET['levelmin'])) {
    $inputlevelmin = filter_var($_GET['levelmin'],FILTER_VALIDATE_INT);
    $wherelevelmin = " AND AA.level >=" . $inputlevelmin . " ";
} else {
    $inputlevelmin = "";
    $wherelevelmin = "";
}
$query = "SELECT distinct(level) FROM signature ORDER BY level";
$stmt = $pdo->prepare($query);
$stmt->execute();
$filterlevelmin = "";
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) 
{
    $selectedmin = "";
    if ($row['level'] == $inputlevelmin) 
	{ 	$selectedmin = " SELECTED";
    }
    $filterlevelmin .= "<option value='" . $row['level'] . "'" . $selectedmin . ">>=" . $row['level'] . "</option>";
}
# error_log($filterlevelmin);
### filter unit_dec
if ( isset($_GET['unit_dec'] ) ) 
	{	$unit_dec = filter_var($_GET['unit_dec'],FILTER_SANITIZE_STRING);
	} else
	{	$unit_dec = 'd';
	}
if ( $unit_dec == "m" )
	{	$monthselected = "selected";
		$weekselected  = "";
		$dayselected   = "";
	} else
	{	if ( $unit_dec == "w" )
		{	$monthselected = "";
			$weekselected  = "selected";
			$dayselected   = "";
		} else
		{	$monthselected = "";
			$weekselected  = "";
			$dayselected   = "selected";
		}
	}
### filter nb_weeks
if ( isset($_GET['nb_weeks'] ) )
	{	$nb_weeks = filter_var($_GET['nb_weeks'],FILTER_VALIDATE_INT);
	} else
	{	$nb_weeks = 1;
	}

## filter from
$inputform = "";
if (isset($_GET['frombeg']) && preg_match("/^[0-9\ ]+$/", $_GET['frombeg'])) 
	{ 	$inputfrom   = $_GET['frombeg'];
		$frombeg     = $inputform;
    	$filterfrom  = $inputfrom;
    	$f = explode(" ", $inputfrom);
    	$sqlfrom = mktime(substr($f[0], 0, 2), substr($f[0], 2, 4), 0, substr($f[1], 2, 2), substr($f[1], 0, 2), substr($f[1], 4, 2));
    	$where .= "AND alert.timestamp>=" . $sqlfrom . " ";
		$pass = "ok";
		$where .= "ok";
	} else 
	{ 	$inputfrom  = date("Hi dmy");
		$frombeg    = $inputfrom;
    	$filterfrom = $inputfrom;
    	$f = explode(" ", $inputfrom);
    	$sqlfrom    = mktime(substr($f[0], 0, 2), substr($f[0], 2, 4), 0, substr($f[1], 2, 2), substr($f[1], 0, 2), substr($f[1], 4, 2));
    	$filterfrom = $inputfrom;
		$pass = 'ko';
    	$where .= "ko";
	}
$mydate = date( "Hi dmy" );
$dmax1 = $sqlfrom;
# let's calculate dates
if ( $unit_dec == 'w' )
	{ 	$decal = 86400 * 7 * $nb_weeks ;
	} else
	{	if ( $unit_dec == "m" )
		{	$decal = 86400 * 30 * $nb_weeks ;
		} else
		{	$decal = 86400 * $nb_weeks;
		}
	}

$dmax2 = $sqlfrom - $decal;
$dmin  = $dmax2   - $decal;
$zdmax1 = date("Y-m-d H:i:s",$dmax1);
$zdmax2 = date("Y-m-d H:i:s",$dmax2);
$zdmin  = date("Y-m-d H:i:s",$dmin);

## filter to
if (isset($_GET['to']) && preg_match("/^[0-9\ ]+$/", $_GET['to'])) 
	{ 	$inputto = $_GET['to'];
    	$filterto = $inputto;
    	$t = explode(" ", $inputto);
    	$sqlto = mktime(substr($t[0], 0, 2), substr($t[0], 2, 4), 0, substr($t[1], 2, 2), substr($t[1], 0, 2), substr($t[1], 4, 2));
    	$lastgraphplot = $sqlto;
    	$where .= "AND alert.timestamp<=" . $sqlto . " ";
	} else 
	{ 	$sqlto = "";
    	$inputto = "";
    	$filterto = $inputto;
    	$where .= "";
	}


## filter criteria 'source'
$wheresource = "";
if (isset($_GET['source']) && strlen($_GET['source']) > 0) 
	{ 	$inputsource  = quote_smart($_GET['source']);
		$source       = quote_smart($_GET['source']);
    	$wheresource .= "AND LO.name like '" . $inputsource . "%' ";
	} else 
	{ 	$inputsource  = "";
		$source       = "";
    	$wheresource .= "";
	}
#
$query = "SELECT distinct(substring_index(substring_index(name, ' ', 1), '->', 1)) as dname FROM location ORDER BY dname";
$filtersource = "";

$stmt = $pdo->prepare($query);
$stmt->execute();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) 
	{ 	$selected = "";
    	if ($row['dname'] == $inputsource) 
		{ 	$selected = " SELECTED";
    	}
    $filtersource .= "<option value='" . $row['dname'] . "'" . $selected . ">" . $row['dname'] . "</option>";
	}
## filter criteria 'source2'
$wheresource2 = "";
if (isset($_GET['source2']) && strlen($_GET['source2']) > 0) 
	{ 	$inputsource2  = quote_smart($_GET['source2']);
    	$wheresource2 .= "AND LO.name like '" . $inputsource2 . "%' ";
	} else 
	{ 	$inputsource2  = "";
    	$wheresource2 .= "";
	}
#
$query = "SELECT distinct(substring_index(substring_index(name, ' ', 1), '->', 1)) as dname FROM location ORDER BY dname";
$filtersource2 = "";

$stmt = $pdo->prepare($query);
$stmt->execute();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) 
	{ 	$selected = "";
    	if ($row['dname'] == $inputsource2) 
		{ 	$selected = " SELECTED";
    	}
    $filtersource2 .= "<option value='" . $row['dname'] . "'" . $selected . ">" . $row['dname'] . "</option>";
	}
#
## filter criteria 'path'
$wherepath = "";
if (isset($_GET['path']) && strlen($_GET['path']) > 0) 
	{ 	$inputpath = quote_smart($_GET['path']);
    	$wherepath .= "AND LO.name like '%->" . $inputpath . "%' ";
	} else 
	{ 	$inputpath = "";
    	$wherepath .= "";
	}
$query = "SELECT distinct(substring_index(name,'->',-1)) as dname FROM location ORDER BY dname;";
$filterpath = "";
$stmt = $pdo->prepare($query);
$stmt->execute();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) 
	{ 	$selected = "";
    	if ($row['dname'] == $inputpath) 
		{ 	$selected = " SELECTED";
    	}
    $filterpath .= "<option value='" . $row['dname'] . "'" . $selected . ">" . $row['dname'] . "</option>";
	}


## filter rule_id
$noterule_id = "";
$whererule_id = "";
if (isset($_GET['rule_id']) && preg_match("/^[0-9,\ ]+$/", $_GET['rule_id'])) 
	{ 	$inputrule_id = $_GET['rule_id'];
    	$filterrule_id = $inputrule_id;
    	$whererule_id .= "AND AA.rule_id = " . $filterrule_id;
		$query = "select signature.description from signature where rule_id=" . $filterrule_id;
		try 
		{	$stmt = $pdo->prepare($query);
        	$stmt->execute();
        	$row = $stmt->fetch(PDO::FETCH_ASSOC);
        	$noterule_id .= "<span style='font-weight:bold;' >Rule " . $filterrule_id . "</span>: " . $row['description'] . "<br/>";
		} catch (Exception $e)
		{
		}
	} else 
	{ 	$inputrule_id = "";
    	$filterrule_id = $inputrule_id;
    	$whererule_id .= "";
	}


### filter input 'datamatch'
# Current opinion is that this does not have to be 'safe' as we trust users who can access this
if (isset($_GET['datamatch']) && strlen($_GET['datamatch']) > 0) 
	{ 	$inputdatamatch = $_GET['datamatch'];
    	$filterdatamatch = $inputdatamatch;
    	$where .= "AND alert.full_log like '%" . quote_smart($inputdatamatch) . "%' ";
	} else 
	{ 	$inputdatamatch = "";
    	$filterdatamatch = $inputdatamatch;
	}

### filter input 'dataexclude'
# Current opinion is that this does not have to be 'safe' as we trust users who can access this
if (isset($_GET['dataexclude']) && strlen($_GET['dataexclude']) > 0) 
	{ 	$inputdataexclude = $_GET['dataexclude'];
    	$filterdataexclude = $inputdataexclude;
    	$where .= "AND alert.full_log not like '%" . quote_smart($inputdataexclude) . "%' ";
	} else 
	{ 	$inputdataexclude = "";
    	$filterdataexclude = $inputdataexclude;
	}


### filter input 'datamatch'
if (isset($_GET['ipmatch']) && preg_match("/^[0-9\.]*$/", $_GET['ipmatch'])) 
	{ 	$inputipmatch = $_GET['ipmatch'];
    	$filteripmatch = $inputipmatch;
    	$where .= "AND alert.src_ip like '" . quote_smart($inputipmatch) . "%' ";
	} else 
	{ 	$inputipmatch = "";
    	$filteripmatch = $inputipmatch;
	}

### filter limit
if (isset($_GET['limit']) && is_numeric($_GET['limit']) && $_GET['limit'] < 1000) 
	{ 	$inputlimit = $_GET['limit'];
	} else 
	{ 	$inputlimit = $glb_detailtablelimit;
	}


### filter alet 'categories'
if (isset($_GET['category']) && preg_match("/^[0-9]+$/", $_GET['category'])) 
	{ 	$inputcategory = $_GET['category'];
    	$filtercagetory = $inputcategory;
    	$where .= " AND category.cat_id=" . $inputcategory . " ";
		$wherecategory = " and AA.rule_id in
		(	select SCM.rule_id 
			from signature_category_mapping SCM,
			 category CAT
			where SCM.cat_id = CAT.cat_id
		      and CAT.cat_id = '" . $inputcategory . "'
		) ";
	} else 
	{ 	$inputcategory = "";
    	$wherecategory = " ";
		$wherecategory = " ";
	}
# Get categories
$query = "SELECT *
	FROM category
	ORDER BY cat_name";
$filtercategory = "";
$stmt = $pdo->prepare($query);
$stmt->execute();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) 
{ 	$selected = "";
    if ($row['cat_id'] == $inputcategory) 
	{ 	$selected = " SELECTED";
    }
    $filtercategory .= "<option value='" . $row['cat_id'] . "'" . $selected . ">" . $row['cat_name'] . "</option>";
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

    <script src="./js/jquery-3.3.1.js" type="text/javascript"></script>
	<script type="text/javascript">
	function CHAIN_SOURCE1 ()
	{	alert("CHAIN_SOURCE1");
		var rule_id  = document.getElementById("rule_id").value;
		var ptr      = document.getElementById("levelmin");
		var levelmin = ptr.options[ptr.selectedIndex].value;
		var frombeg  = document.getElementById('frombeg').value;
		var ptr      = document.getElementById("source");
		var source   = ptr.options[ptr.selectedIndex].value;

		var url = "./detail.php";
		url += "?rule_id=" + rule_id + "&levelmin=" + levelmin;
		url += "&to=" + frombeg;
		url += "&source=" + source;
//
		var ptr = document.getElementById("unit_dec");
		var unit_dec = ptr.options[ptr.selectedIndex].value;
		var nb_weeks = document.getElementById("nb_weeks").value;
		alert("Unit_dec=" + unit_dec + " Nb Weeks=" + nb_weeks);
//		calcul "from "
		var parts = frombeg.split(/(\d{2})(\d{2}) (\d{2})(\d{2})(\d{2})/);
		var ho    = parts[1];
		var mi    = parts[2];
		var da    = parts[3];
		var mo    = parts[4] - 1;
		var ye    = parts[5];
		if ( ye < 70 )
		{	ye = "20" + ye;
		} else
		{	ye = "19" + ye;
		}

		var zero  = 0;
		var epoch_date = new Date();
		epoch_date = Date.UTC(ye,mo,da,ho,mi,zero,zero);
//		var loc_date = epoch_date.toLocaleDateString();
		var loc_date = new Date(epoch_date);

		alert("Date=" + epoch_date + "  loc=" + loc_date );
//
		alert(url);
//		window.open(url,"_blank");
	}

	</script>

    <script type="text/javascript">
<?php
print("var chart;\n");
if ( $wheresource2 == "" )
{ 	include './php/detail_statistics_rules.php';
} else
{	include './php/detail_statistics_rules.php';
}
?>
//  define graph
AmCharts.ready(function () 
{
// SERIAL CHART
	chart = new AmCharts.AmSerialChart();
	chart.dataProvider = chartData;
	chart.categoryField = "rule";
	// this single line makes the chart a bar chart,
	// try to set it to false - your bars will turn to columns
	chart.rotate = false;
	// the following two lines makes chart 3D
	chart.depth3D = 20;
	chart.angle = 30;
	chart.titles = [{
    "text": "Nb rows by period" }, 
	{ "text": "<?php echo 'From ',$zdmax2,' to ',$zdmax1,' and ',$zdmin,' to ',$zdmax2;?>",
    "bold": false }
	];
	
	// AXES
	// Category
	var categoryAxis = chart.categoryAxis;
	categoryAxis.gridPosition = "start";
	categoryAxis.axisColor = "#DADADA";
	categoryAxis.fillAlpha = 1;
	categoryAxis.gridAlpha = 0;
	categoryAxis.fillColor = "#FAFAFA";
	
	// value
	var valueAxis = new AmCharts.ValueAxis();
	valueAxis.axisColor = "#DADADA";
	valueAxis.title = "Hits by level";
	valueAxis.gridAlpha = 0.1;
	valueAxis.logarithmic = true;
	chart.addValueAxis(valueAxis);
	
	// GRAPH CURRENT
	var graph = new AmCharts.AmGraph();
	graph.title = "Hits / Rule";
	graph.valueField = "current";
	graph.type = "column";
//	graph.balloonText = "Current [[category]]:[[value]]";
//	graph.balloonText = "libel";
	graph.balloonText = " [[libel]]:[[value]]";
	graph.lineAlpha = 0;
	graph.fillColors = "#ff1c25";
	graph.fillAlphas = 1;
	chart.addGraph(graph);
	
	// GRAPH PREVIOUS
	var graph1 = new AmCharts.AmGraph();
	graph1.title = "Hits / Rule";
	graph1.valueField = "previous";
	graph1.type = "column";
//	graph1.balloonText = "Previous [[category]]:[[value]]";
	graph1.balloonText = "[[libel]]:[[value]]";
	graph1.lineAlpha = 0;
	graph1.fillColors = "#801c15";
	graph1.fillAlphas = 1;
	chart.addGraph(graph1);
	
	if ( model == 1 ) 
	{
	// GRAPH 2 CURRENT
	var graph2 = new AmCharts.AmGraph();
	graph2.title = "Hits / level";
	graph2.valueField = "current2";
	graph2.type = "column";
//	graph2.balloonText = "Current [[category]]:[[value]]";
	graph2.balloonText = "[[libel]]:[[value]]";
	graph2.lineAlpha = 0;
	graph2.fillColors = "#25df25";
	graph2.fillAlphas = 1;
	chart.addGraph(graph2);

	// GRAPH 2 PREVIOUS
	var graph3 = new AmCharts.AmGraph();
	graph3.title = "Hits / level";
	graph3.valueField = "previous2";
	graph3.type = "column";
//	graph3.balloonText = "Previous [[category]]:[[value]]";
	graph3.balloonText = "[[libel]]:[[value]]";
	graph3.lineAlpha = 0;
	graph3.fillColors = "#258025";
	graph3.fillAlphas = 1;
	chart.addGraph(graph3);
	}
	// WRITE
	chart.creditsPosition = "top-right";
	chart.write("chartdiv");
});
    </script>

</head>
<!-- <body onload="databasetest()"> -->
<body>
<?php 
include './amilogged.php';
include './header_statistics.php'; 
?>
<div class="container-fluid" style="padding-top: 60px;">
    <div class="row">
        <div class="col-lg-12">
            <ul class="nav nav-pills" role="tablist" style="width: 100%;">
                <li role="presentation" class="active" style="width: 100%;">
					<a href="#" style="font-weight: 800">Filters</a>
                </li>
            </ul>
        </div>
    </div>
<!--  Filter display group 1 -->
    <form id='click2go' method='GET' action='./statistics_rules.php' class="form-inline">
        <div class="row">
            <div class="col-lg-1 form-group">
                <label>RuleID</label>
            </div>
            <div class="col-lg-2 form-group">
                <label>Before
                    <small>(HHMM DDMMYY)</small>
                </label>
			</div>
            <div class="col-lg-1 form-group">
                <label>Unit
                    <small></small>
                </label>
			</div>
            <div class="col-lg-2 form-group">
                <label>Source 1</label>
            </div>
            <div class="col-lg-3 form-group">
                <label>Category</label>
            </div>
  <!--  TBD            <label>Detail of source 1</label>  -->
        </div>
<!-- Group 1 values -->
        <div class="row">
            <div class="col-lg-1 form-group">
                <input type='text' size='6' name='rule_id' id='rule_id' value='<?php echo $filterrule_id; ?>'
                       class="form-control input-sm"/>
            </div>
            <div class="col-lg-2 form-group">
                <input type='text' size='16' name='frombeg' id='frombeg' value='<?php echo $inputfrom; ?>'
                       class="form-control input-sm"/>
            </div>
            <div class="col-lg-1 form-group">
					<select name='unit_dec' id='unit_dec' class="form-control input-sm">
					<option value='d' <?php echo $dayselected; ?> >Day</option>
					<option value='m' <?php echo $monthselected; ?> >Month</option>
                    <option value='w'<?php echo $weekselected; ?>  >Week</option>
					</select>
            </div>
            <div class="col-lg-2 form-group">
                <select name='source' id='source' class="form-control input-sm">
                    <option value=''>--</option>
                    <?php echo $filtersource; ?>
                </select>
			</div>
            <div class="col-lg-3 form-group">
                <select name='category' id='category' class="form-control input-sm">
                    <option value=''>--</option>
                    <?php echo $filtercategory; ?>
                </select>
            </div>
            <div class="col-lg-1 vc">
           <!-- TBD     <input value='..Detail Source 1' class="btn btn-success" onclick="CHAIN_SOURCE1()"/> -->
            </div>
        </div>
<!-- display filters line 2 -->
        <div class="row">
            <div class="col-lg-1 form-group">
                <label>
                    <small>Level min </small>
                </label>
            </div>
            <div class="col-lg-2 form-group">
                <label>  
                    <small>             </small>
                </label>
            </div>
            <div class="col-lg-1 form-group">
                <label>
                    <small>Nb units</small>
                </label>
            </div>
            <div class="col-lg-2 form-group">
                <label>Source 2</label>
            </div>
            <div class="col-lg-3 form-group">
                <label>Path    </label>
            </div>
        </div>
<!-- Group2 values -->
        <div class="row">
            <div class="col-lg-1 form-group">
				<select name='levelmin' id=levelmin value='levelmin' class="form-control"> 
					<?php echo $filterlevelmin; ?> 
				</select>
            </div>
            <div class="col-lg-2 form-group">
            </div>
            <div class="col-lg-1 form-group">
				<input type='text' size='6' name='nb_weeks' id='nb_weeks' value='<?php echo $nb_weeks; ?>'
                       class="form-control input-sm"/>
            </div>
            <div class="col-lg-2 form-group">
                <select name='source2' id='source2' class="form-control input-sm">
                    <option value=''>--</option>
                    <?php echo $filtersource2; ?>
                </select>
            </div>
            <div class="col-lg-3 form-group">
                <select name='path' id='path' class="form-control input-sm">
                    <option value=''>--</option>
                    <?php echo $filterpath; ?>
                </select>
            </div>
            <div class="col-lg-1 vc">
                <input type='submit' value='..Display' class="btn btn-success"/>
            </div>
        </div>
    </form>
<!--    <br/> -->
    <div class="row">
        <div class="col-lg-12">
            <div><?php echo $noterule_id; ?></div>
        </div>
    </div>
<!-- Separe Filter from Graph -->
<div class="row">
        <div class="col-lg-12">
            <ul class="nav nav-pills" role="tablist" style="width: 100%;">
                <li role="presentation" class="active" style="width: 100%;"><a href="#"
                                                                               style="font-weight: 800">Graph</a>
                </li>
            </ul>
        </div>
    </div>
<!-- Place to display graph -->

    <div class="row">
        <div id="chartdiv" style="width:100%; height:<?php echo $glb_height_stat_rules?>px;"></div>
    </div>
	<div>
	<?php 
	echo "<br>inp=(",$inputfrom,") sqlfrom=(",$sqlfrom,") decal=",$decal," 1=(", $dmax1,") 2=(", $dmax2,") min=(", $dmin, ") my=",$mydate," unit=",$unit_dec," nb=",$nb_weeks; 
	echo "<br>pass=(",$pass," input=",$inputfrom," where=(",$where,")";
	echo "<br> MAX1=",$zdmax1," MAX2=",$zdmax2," MIN=",$zdmin;
	echo '<br> FROMBEG=',$frombeg;
	echo '<br> inputsource=',$source;
	echo '<br> wherelevelmin='.$wherelevelmin;
	echo '<br> wheresource=',$wheresource;
	echo '<br> wheresource2=',$wheresource2;
	echo '<br> wherepath='.$wherepath;
	echo '<br> whererule_id='.$whererule_id;
	echo '<br> SQL=',$querydata;
	?>
	</div>

<?php
include 'footer.php';
?>
<script type="text/javascript">
    $(document).ready(function () 
    {
        $('.toggle').click(function () 
        {   id = $(this).parent().attr("id");
            toggled = $(this).parent().find(".toggled");

            toggled.slideToggle('fast', function () 
            {
                cookie = (toggled.is(":hidden")) ? "0" : "1";
                setCookie("hideshow" + id, cookie, "100");
            });
        });
        $.fn.highlight = function (what, spanClass) 
        {       return this.each(function () {
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
</div>
</body>
</html>

