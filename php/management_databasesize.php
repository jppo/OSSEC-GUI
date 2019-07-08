<?php
/*
 * Copyright (c) 2017 António 'Tó' Godinho <to@isec.pt>.
 * Copyright (c) 2019 JP P
 * This program is free software; Distributed under the terms of the GNU GPL v3.
 */

### Odds and sods
$query = "SELECT table_schema as 'Database', sum( data_length + index_length ) / 1024  as 'Size' 
	FROM information_schema.TABLES 
	WHERE table_schema='" . DB_NAME_O . "' 
	GROUP BY table_schema";
if ($glb_debug == 1) {
    $databaseinMB = $query;
} else {
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $databaseinMB = number_format(floor($row['Size'])) . " KB";
}

$query = "SELECT count(*) as nbrows from alert;";
if ($glb_debug == 1) {
    print("<br>Query=(" . $query . "<br>");
}  
try
{ 	$stmt = $pdo->prepare($query);
   	$stmt->execute();
} catch (Exception $e)
{	print("<br> Sqlerror : " . $e . "<br>");
	print("Query=(" . $query . ")");
}
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$databaseinrows = number_format($row['nbrows']);
?>

<div style="padding:10px;">
    <table width=50%>
        <?php
        if ($glb_debug == 1) {
            echo "<tr><td><div style='font-size:24px; color:red;'>Debug</div></td></tr>";
        }
        ?>

        <tr>
            <th>Database Size</th>
            <th>Database Alert Count</th>
        </tr>
        <tr>
            <td style="padding:8px"><?php echo $databaseinMB ?></td>
            <td style="padding:8px"><?php echo $databaseinrows ?></td>
        </tr>
    </table>
</div>
