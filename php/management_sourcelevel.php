<?php
/*
 * Copyright (c) 2017 António 'Tó' Godinho <to@isec.pt>.
 * Copyright (c) 2019 JP P
 * This program is free software; Distributed under the terms of the GNU GPL v3.
 */
$query = "SELECT count(alert.id) as res_cnt, SUBSTRING_INDEX(SUBSTRING_INDEX(location.name, ' ', 1), '->', 1) as res_name, location.id as res_id , signature.level as res_level       
	FROM alert, location, signature  
	WHERE alert.location_id = location.id         
	AND alert.rule_id = signature.rule_id         
	GROUP BY res_name, res_level
	ORDER BY res_name, res_level";

$mainstring = "";
if ($glb_debug == 1) 
{ 	$mainstring = "var chartData = []";
    # Oh this is setting a bad code precedent 
    $clientvsleveldebugstring = "<div style='font-size:24px; color:red;'>Debug</div>";
    $clientvsleveldebugstring .= $query;
} else 
{ 	
	try 
	{	$stmt = $pdo->prepare($query);
    	$stmt->execute();
	} catch ( Exception $e)
	{ 	echo "SQL Error:" .  $e . " (" . $query . ")";
    }

    $whilelocation = "";
    $mainstring = "";

/*
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
*/
	foreach ($stmt->fetchAll() as $row) 
    {    $sourcelevel[$row['res_name']][$row['res_level']] = $row['res_cnt'];
    }

    $mainstring = "var chartData = [";

    $i = 0;
    $graphcount = 0;
    foreach ($sourcelevel as $key => $val) 
	{ 	$i +=1;
		$graphcount++;
        # $key = (boxname)
        if ($i == 1) 
		{ 	$mainstring .= ",";
        }
		$i = 1;
        $mainstring .= "
			{source:\"" . preg_replace($glb_hostnamereplace, "", $key) . "\",";

        foreach ($val as $k => $v) 
		{ 	# $k = level
            # $v = count
            $mainstring .= " level" . $k . ":" . $v . ",";
        }
        $mainstring = preg_replace('/,$/', '', $mainstring);
        $mainstring .= "},";
    }
    $mainstring = preg_replace('/,$/', '', $mainstring);
    $mainstring .= "
		];";
}

echo $mainstring;

# JQuery will set this at the page bottom
$graphheight = "$( document ).ready(function() {
                $('#chartdiv').css({ 'height': '". ($graphcount * 25) . "px' });
            });";

#################################

$graphstring = "";

for ($i; $i < 16; $i++) 
{ 	# Once for each level of alert (0-15)
    $graphstring .= "
		// Graph " . $i . "
		var graph" . $i . " = new AmCharts.AmGraph();
		graph" . $i . ".title = \"level" . $i . "\";
		graph" . $i . ".labelText = \"[[value]]\";
		graph" . $i . ".valueField = \"level" . $i . "\";
		graph" . $i . ".type = \"column\";
		graph" . $i . ".lineAlpha = 0;
		graph" . $i . ".fillAlphas = 1;
		graph" . $i . ".lineColor = \"" . $levelcolours["level" . $i] . "\";
		graph" . $i . ".lineThickness = 2;
		chart.addGraph(graph" . $i . ");
	";
}
?>
