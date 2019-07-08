<?php
/*
 * Copyright (c) 2017 António 'Tó' Godinho <to@isec.pt>.
 * Copyright (c) 2019 JP P
 * This program is free software; Distributed under the terms of the GNU GPL v3.
 */
$query = "SELECT count(alert.id) as res_cnt, alert.rule_id as res_rule, location.name as res_loc, substring_index(substring_index(location.name, ' ', 1), '->', 1) as sname, substring_index(location.name,'->',-1) as pname
	FROM alert, location
	WHERE alert.location_id=location.id
	GROUP BY res_rule,  res_loc
	ORDER BY count(alert.id) DESC
	LIMIT " . $glb_managementtweaking . ";";


$mainstring = "";
if ($glb_debug == 1) {
    $mainstring = "<div style='font-size:24px; color:red;'>Debug</div>";
    $mainstring .= $query;
} else {
    $stmt = $pdo->prepare($query);
    $stmt->execute();

    if (!$stmt) {
        echo "SQL Error:" . $query;
    }
    $mainstring = "
		<div style='max-height:500px;overflow:auto;'>
		<table>
			<tr>
			<th>Count</th>
			<th>Rule ID</th>
			<th>System</th>
			<th>View</th>
			</tr>";

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $mainstring .= "
		<tr>
		<td style=\"padding:8px\">" . number_format($row['res_cnt']) . "</td>
		<td style=\"padding:8px\">" . $row['res_rule'] . "</td>
		<td style=\"padding:8px\">" . preg_replace($glb_hostnamereplace, "", $row['res_loc']) . "</td>
		<td><a href='./detail.php?rule_id=" . $row['res_rule'] . "&source=" . $row['sname'] . "&path=" . $row['pname'] . "'>Link</a></td>
		</tr>";
    }
    $mainstring .= "</table></div>";
}

echo $mainstring;
?>
