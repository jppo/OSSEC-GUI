<?php
/*
 * Copyright (c) 2017 António 'Tó' Godinho <to@isec.pt>.
 * Copyright (c) 2019 JP P
 * This program is free software; Distributed under the terms of the GNU GPL v3.
 */

$query = "SELECT 	count(alert.rule_id) as count,
		max(alert.timestamp) as timestamp, 
		substring_index(substring_index(location.name, ' ', 1), '->', 1) as source, 
		alert.rule_id as rule_id,
		signature.level,
		signature.description,
		alert.full_log as data
	FROM alert, location, signature
	WHERE alert.timestamp>" . (time() - ($glb_threatdays * 3600 * 24)) . "
	AND signature.level>" . $glb_threatlevel . "
	AND alert.rule_id = signature.rule_id
	AND alert.location_id = location.id
	GROUP BY 3,4,5,6,7
	ORDER BY 5 DESC, 2
	LIMIT " . $glb_threatlimit . ";";

if ($glb_debug == 1) {

    echo "<div style='font-size:24px; color:red;'>Debug</div>";
    echo $query;

} else {
    $threatcount = 0;
    echo "
	<table style='width:100%;'>
	<tr>
		<th class='big'>Level</th>
		<th class='big'>Location</th>
		<th class='big'>Rule</th>
		<th class='big'>Last Seen</th>
		<th class='big'>Count</th>
		<th class='big'>Data</th>
	</tr>
	";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $threatcount = 1;

        echo "<tr>
			<td>" . $row['level'] . "</td>
			<td>" . $row['source'] . "</td>
			<td>" . substr($row['description'], 0, 36) . "...</td>
			<td>" . date("D M j G:i:s", $row['timestamp']) . "</td>
			<td>" . $row['count'] . "</td>
			<td><a href='detail.php?rule_id=" . $row['rule_id'] . "&from=" . date("Gi dmy", time() - (86400 * 30)) . "&source=" . $row['source'] . "'>Link</a>
			</tr>";
    }
    if ($threatcount == 0) {
        echo $glb_nodatastring;
    }
    echo "</table>";
}

?>
