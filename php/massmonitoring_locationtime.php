<?php
/*
 * Copyright (c) 2017 António 'Tó' Godinho <to@isec.pt>.
 * Copyright (c) 2018 JP P
 * This program is free software; Distributed under the terms of the GNU GPL v3.
 */

$locationtimedebugstring = "";

# Note, this graphs looks at 10,000 second blocks, which is more than 24 hours, so not every day will have a result

$query = "select
		count(distinct(SUBSTRING_INDEX(location.name, ' ', 1))) as res_cnt,
		concat(substring(alert.timestamp, 1, 5), '00000') as res_time
	from alert, location
	where alert.location_id = location.id
	and alert.timestamp>" . (time() - ($glb_mass_days * 24 * 3600)) . "
	group by res_time;";

if ($glb_debug == 1) 
{ 	echo "var chartData2 = []";
    $locationtimedebugstring = "<div style='font-size:24px; color:red;'>Debug</div>";
    $locationtimedebugstring .= $query;
} else 
{ 	$stmt = $pdo->prepare($query);
    $stmt->execute();

    if (!$stmt) {
        echo "SQL Error:" . $query;
    }

    echo "
	
		var chartData2 = [";

    $mainstring2 = "";
	$j = 0;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) 
	{	$j += 1;
        $loctime[$row['res_time']] = $row['res_cnt'];
    }

    $i = 0;
	if ( ! ( $j == 0 ) )
	{ 	foreach ($loctime as $key => $val) 
		{ 	# $key = time
        	$mainstring2 .= "
			 {date: new Date(" . date("Y", $key) . ", " . (date("m", $key) - 1) . ", " . date("j", $key) . ", " . date("G", $key) . ", " . (date("i", $key)) . "), location:" . $val . "},";
    	}
	} else
	{	$mainstring2 .= "";
	}
    $mainstring2 = preg_replace('/,$/', '', $mainstring2);
    $mainstring2 .= "
		];";

    echo $mainstring2;
}

?>
