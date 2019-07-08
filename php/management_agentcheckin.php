<?php
/*
 * Copyright (c) 2017 António 'Tó' Godinho <to@isec.pt>.
 * Copyright (c) 2019 JP P
 * This program is free software; Distributed under the terms of the GNU GPL v3.
*/
$query = "SELECT MAX(alert.timestamp) as res_time, SUBSTRING_INDEX(SUBSTRING_INDEX(location.name, ' ', 1), '->', 1) as res_name
	FROM alert, location
	WHERE alert.location_id=location.id
	GROUP by res_name
	ORDER BY res_time;";

$mainstring = "";
$rowctr = 0;
if ($glb_debug == 1) 
{ 	$mainstring = "<div style='font-size:24px; color:red;'>Debug</div>";
    $mainstring .= $query;
} else 
{
	try
	{ 	$stmt = $pdo->prepare($query);
    	$stmt->execute();
		$rowctr = $stmt->rowCount();;
	} catch ( Exception $e)
	{ 	echo "SQL Error:" .  $e . " (" . $query . ")";
    }
	$rowctr = 300 + (20 * $rowctr);
    $mainstring = "
		<div style='max-height:" . $rowctr . "px;overflow:auto;'>
		<table>
			<tr>
			<th>Agent</th>
			<th>Last Alert</th>
			<th></th>
			</tr>";
	foreach ($stmt->fetchAll() as $row) 
	{ 	$hoursago = (time() - $row['res_time']) / 3600;
		{ 	$mainstring .= "<tr>
                                <td  style=\"padding:8px\"><a href='./detail.php?source=" . $row['res_name'] . "&from=0000 " . date("dmy", ($row['res_time']) - (7 * 24 * 3600)) . "'>" . $row['res_name'] . "</a></td>
                                <td  style=\"padding:8px\">" . date("l jS F Y ga", $row['res_time']) . "</td>
                                <td  style=\"padding:8px\">" . floor((time() - $row['res_time']) / 86400) . " days</td>
                            </tr>";
        }
    }

    $mainstring .= "</table>
                </div>";
}
echo $mainstring;
?>
