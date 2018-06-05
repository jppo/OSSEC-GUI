<?php
/*
 * Copyright (c) 2017 António 'Tó' Godinho <to@isec.pt>.
 * Copyright (c) 2018 JP P
 * This program is free software; Distributed under the terms of the GNU GPL v3.
 */

# The graph data 'series' can be broken down in several ways
$query = "select concat(substring(alert.timestamp, 1, 5), \"00000\") as res_time, count(alert.id) as res_cnt
		from alert
		group by substring(alert.timestamp, 1, 5)
		order by substring(alert.timestamp, 1, 5)";


if ($glb_debug == 1) {
    # Oh this is setting a bad code precedent 
    $timevolumedebugstring = "<div style='font-size:24px; color:red;'>Debug</div>";
    $timevolumedebugstring .= $query;
} else 
{ 	
	try 
	{	$stmt = $pdo->prepare($query);
    	$stmt->execute();
	} catch (Exception $e)
	{ 	echo "SQL Error:" . $e . " " . $query . ")";
		return;
    }

    $mainstring = "var chartData_timemanagement = [ ";

    $i = 0;
    $alerttotal = 0;
    $sizetotal = 0;
	foreach ($stmt->fetchAll() as $row)
	{ 	if ($i > 0) 
		{ 	$mainstring .= ",";
       	}
		$i++;

        $tmpdate = $row['res_time'];
        $sizetotal += $row['res_cnt'];

        $mainstring .= "
			{date: new Date(" . date("Y", $tmpdate) . ", " . (date("m", $tmpdate) - 1) . ", " . date("j", $tmpdate) . "), count:" . $row['res_cnt'] . ", total:" . $sizetotal . "}";

        $alerttotal = $alerttotal + $row['res_cnt'];
    }
    $mainstring .= "];";
}
echo $mainstring;
# print ("\n// ligne 56\n");
$graph_timemanagement_average = $alerttotal / $i;
print ("\n// ligne 53 (" . $graph_timemanagement_average . ")\n");

$graph_timemanagement = "
		// GRAPHS 1
		var graph_timemanagement = new AmCharts.AmGraph();
		graph_timemanagement.title = \"Daily Alerts\";
		graph_timemanagement.valueField = \"count\";
		graph_timemanagement.valueAxis = chart_timemanagement.valueAxes[0];
		graph_timemanagement.bullet = \"round\";
		graph_timemanagement.hideBulletsCount = 30;
		graph_timemanagement.balloonText = \"[[value]]\";
		graph_timemanagement.lineThickness = 2;
		chart_timemanagement.addGraph(graph_timemanagement);
		// GRAPHS 2
		var graph_timemanagement2 = new AmCharts.AmGraph();
		graph_timemanagement2.title = \"Cumulative Alerts\";
		graph_timemanagement2.valueField = \"total\";
		graph_timemanagement2.valueAxis = chart_timemanagement.valueAxes[1];
		graph_timemanagement2.bullet = \"round\";
		graph_timemanagement2.hideBulletsCount = 30;
		graph_timemanagement2.balloonText = \"[[value]]\";
		graph_timemanagement2.lineThickness = 2;
		chart_timemanagement.addGraph(graph_timemanagement2);
";
?>
