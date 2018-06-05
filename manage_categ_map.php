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
# filter<var> = for repopulating the filter toolbar
# where = the cumulative sql command
## filter criteria 'levelmin' and 'levelmax' 
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
	require_once './config.php';
    require_once './page_refresh.php';
    ?>
    <link href="./css/style.css" rel="stylesheet" type="text/css"/>
    <link href="./css/sticky-footer.css" rel="stylesheet">
    <script src="./js/sortable.js" type="text/javascript"></script>

    <script src="./js/jquery-3.3.1.js"></script>

    <script type="text/javascript">
	function LINKID(id,rule_id,description,level) 
	{
		url = "./php/manage_categ_map_detail.php?id=" + id + "&rule_id=" + rule_id + "&description=" + description + '&level=' + level;
//		alert("Before call ID : " + url );
		window.location.href=url;
	}
	</script>

</head>
<body >
<div class="container-fluid" style="padding-top: 60px;">
<p align=middle><h3>Management for table signature_category_mapping</h3></p>

    <?php
    # use this to store the main table as I want the 'Common Patterns' to be at the top but it needs processing at same time
    $mainstring = "";
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

#	Table "data" suppressed
    # Count the queries for the last line of the table.
    $querycounttable = "select count(SI.rule_id) as counter
from signature SI
where SI.rule_id not in ( 
			select CT.rule_id
			from  signature_category_mapping CT
			)" ;
try	{ 	$stmt = $pdo->prepare($querycounttable);
    	$stmt->execute();
    	$rowcounttable = $stmt->fetch(PDO::FETCH_ASSOC);
	} catch (Exception $e)
	{	print ("<br>Sql error : " . $e . ") <br>");
		print ("Query = (" . $querycounttable . ")");
		return;
	}

    $resultablerows = $rowcounttable['counter'];

    # Fetch the actual rows of data for the table
    $querytable = "select orig,id,rule_id,level,description as libelle
from (
SELECT '1' as orig, SG.id , SG.rule_id, SG.level, SG.description
from	signature SG
where	SG.id in 	( 	select distinct AL.rule_id
						from alert AL
					)
  and  	rule_id in (	select SI.rule_id
						from signature SI
						where SI.rule_id not in ( 
							select CT.rule_id
							from  signature_category_mapping CT
						)
					)	
union
SELECT  '2' as orig,SG.id ,SG.rule_id, SG.level, SG.description
	from signature SG
	where SG.rule_id in (	select SI.rule_id
						from signature SI
						where SI.rule_id not in ( 
							select CT.rule_id
							from  signature_category_mapping CT
						)
					)
) xx
order by orig,id LIMIT " . $glb_query_limit;
#
$SQLERROR = "";
try { 	$stmt = $pdo->prepare($querytable);
	} catch (Exception $e)
	{ 	$SQLERROR = "alert('Sqlerror : " . $e . " : " . $querytable . ")'";
		die("Sqlerror : " . $e . "==");
		return;
	}
if ( $SQLERROR == "" )
{
try { 	$stmt->execute();
	} catch (Exception $e)
	{ 	$SQLERROR = "alert('Sqlerror : " . $e . " : " . $querytable . ")'";
	}
}
    $mainstring .= "<div class='newboxes toggled'><table class='dump sortable' id='sortabletable'  style='width:100%' >
		<tr> <th>Link</th><th>ID</th><th>Rule</th><th>Lvl</th><th>Description</th>
		</tr>";
	$rowcount = 0;
    while ($rowtable = $stmt->fetch(PDO::FETCH_ASSOC)) 
	{ 	# Dump each line to the table, be careful, this data is fromt the logs and should not be trusted
        $rowcount++;
        $mainstring .= "<tr>";
		$mainstring .= "<td> <img src=./images/ok-icon.png width=20 height=18 onclick=LINKID(".$rowtable['id'] . "," . $rowtable['rule_id'] . ",";
		$catdesc     = htmlspecialchars(preg_replace("/ [0-9\.]*->/", " ", $rowtable['libelle']));
		$catdesc     = rawurlencode($catdesc);
		$mainstring .=  "'" . $catdesc . "'" . "," . $rowtable['level'];
		$mainstring .=  ");> </td>";
        $mainstring .= "<td>" . htmlspecialchars($rowtable['id']) . "</td>";
        $mainstring .= "<td>" . htmlspecialchars($rowtable['rule_id']) . "</td>";
        $mainstring .= "<td>" . htmlspecialchars($rowtable['level']) . "</td>";
        $mainstring .= "<td>" . htmlspecialchars(preg_replace("/ [0-9\.]*->/", " ", $rowtable['libelle'])) . "</td>";
		$mainstring .= "</tr>\n";
        }
    $mainstring .= "</table></div>";
	if ( $rowcount == 0 )
	{	print("<br><h4><b> Super all rules are categorized ! </h4></b><br><br>");
	}
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
	print ("<h3>" . $SQLERROR . "</h3>");
	print("<!--  SQLERROR=(" . $SQLERROR . ") -->\n");
    echo "<table class='dump sortable' style='width:100%' >";
    # Now print main data table
    echo $mainstring;

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
<form id='click2go' method='GET' action='./manage_categ_map.php'>

<?php
require_once './config.php';
include './footer.php';
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
</body>
</html>

