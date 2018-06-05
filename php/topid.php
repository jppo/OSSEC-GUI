<?php
/*
 * Copyright (c) 2017 António 'Tó' Godinho <to@isec.pt>.
 * Copyright (c) 2018 JP P
 * This program is free software; Distributed under the terms of the GNU GPL v3.
 */

if ($glb_debug == 1) {
    $starttime_topidchart = microtime();
    $startarray_topidchart = explode(" ", $starttime_topidchart);
    $starttime_topidchart = $startarray_topidchart[1] + $startarray_topidchart[0];
}

# To filter on 'Category' (SSHD) extra table needs adding, but they slow down the query for other things, so lets only put them into the SQL if needed....
if (strlen($wherecategory) > 5) {
    $wherecategory_tables = ", signature_category_mapping, category";
    $wherecategory_and = "and alert.rule_id=signature_category_mapping.rule_id
        and signature_category_mapping.cat_id=category.cat_id";
} else {
    $wherecategory_tables = "";
    $wherecategory_and = "";
}

$query = "SELECT count(alert.id) as res_cnt, alert.rule_id as res_id, signature.description as res_desc, signature.rule_id as res_rule
	FROM alert, signature " . $wherecategory_tables . "
	WHERE alert.timestamp>'" . (time() - ($inputhours * 60 * 60)) . "' 
	and alert.rule_id=signature.rule_id 
	" . $wherecategory_and . "
	AND signature.level>=" . $inputlevel . "
	" . $glb_notrepresentedwhitelist_sql . " 
	" . $wherecategory . " 
	GROUP BY res_id, res_desc, res_rule  
	ORDER BY count(alert.id) DESC
	LIMIT " . $glb_indexsubtablelimit;
?>
<div>
    <ul class="nav nav-pills" role="tablist" style="width: 100%;">
        <li role="presentation" class="active" style="width: 100%;"><a href="#" style="font-weight: 800">Top Rule_ID
                <span class="badge"><?php echo $inputhours . " Hrs (lvl " . ($inputlevel); ?>)</span></a></li>
    </ul>
</div>
<?php

$mainstring = "";

$stmt = $pdo->prepare($query);
try
{	$stmt->execute();
} catch (Exception $e)
{
    $mainstring = "SQL Error: " . var_dump($stmt);
	
} 
if ($glb_debug == 1) {
    $mainstring = "<div><span class=\"label label-danger\" style=\"font-size: 125%;\">Debug</span></div>";
    $mainstring .= $query;

    $endtime_topidchart = microtime();
    $endarray_topidchart = explode(" ", $endtime_topidchart);
    $endtime_topidchart = $endarray_topidchart[1] + $endarray_topidchart[0];
    $totaltime_topidchart = $endtime_topidchart - $starttime_topidchart;
    $mainstring .= "<br>Took " . round($totaltime_topidchart, 1) . " seconds";
} else {

    # Keep this in the same format that detail.php already uses
    $from = date("Hi dmy", (time() - ($inputhours * 3600)));

    $mainstring .= "<table class=\"table table-striped\"><tbody>";

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $mainstring .= "<tr>";
        $mainstring .= "<td>" . number_format($row['res_cnt']) . "</td>";
        $mainstring .= "<td><a class='top10data tooltip_small' href='./detail.php?rule_id=" . $row['res_rule'] . "&from=" . $from . "&breakdown=source'>" . htmlspecialchars(substr($row['res_desc'], 0, 28)) . "...<span>" . htmlspecialchars($row['res_desc']) . "</span></a></td>";
        $mainstring .= "</tr>";
    }
    $mainstring .= "</tbody></table>";
}

if ($mainstring == "") {
    echo $glb_nodatastring;
} else {
    echo $mainstring;
}
?>
