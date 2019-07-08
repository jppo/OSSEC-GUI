<?php
/*
 * Copyright (c) 2017 António 'Tó' Godinho <to@isec.pt>.
 * Copyright (c) 2019 JP P
 * This program is free software; Distributed under the terms of the GNU GPL v3.
 */

if ($glb_debug == 1) {
    $starttime_toprarechart = microtime();
    $startarray_toprarechart = explode(" ", $starttime_toprarechart);
    $starttime_toprarechart = $startarray_toprarechart[1] + $startarray_toprarechart[0];
}


# This will not be pretty.  A SQL command was made that worked, but due to indexing design flaws with the OSSEC MYSQL schema the command took 10 minutes to run on a relatively new/empty database.
# A better version of this interface is planned that will redesign the databse and made this nicer.

?>
<div>
    <ul class="nav nav-pills" role="tablist" style="width: 100%;">
        <li role="presentation" class="active" style="width: 100%;"><a href="#" style="font-weight: 800">Rare in <span
                        class="badge"><?php echo $inputhours . " Hrs, last seen (Lvl " . ($inputlevel); ?>)</span></a>
        </li>
    </ul>
</div>
<?php

$query = "select distinct(alert.rule_id)
	from alert, signature, signature_category_mapping, category
	where alert.timestamp>" . (time() - ($inputhours * 3600)) . "
	and alert.rule_id=signature.rule_id
	and alert.rule_id=signature_category_mapping.rule_id
	and signature_category_mapping.cat_id=category.cat_id
	and signature.level>" . $inputlevel . "
	" . $wherecategory . "";


$stmt = $pdo->prepare($query);
try 
{
	$stmt->execute();
} catch (Exception $e)
{
    	echo "SQL Error:" . $query." ".$e;
}

$lastrare = array();

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

    $ruleid = $row['rule_id'];

    $querylast = "select max(alert.timestamp) as time, signature.description as descr
		from alert, signature
		where alert.rule_id=" . $ruleid . "
		and alert.rule_id=signature.rule_id
		and alert.timestamp<" . (time() - ($inputhours * 3600));

    $stmtlast = $pdo->prepare($querylast);
    $stmtlast->execute();
    $rowlast = $stmtlast->fetch();
    $lastrare[$ruleid] = $rowlast['time'] . "||" . $rowlast['descr'];
}


if ($glb_debug == 1) {
    $mainstring = "<div><span class=\"label label-danger\" style=\"font-size: 125%;\">Debug</span></div>";
    $mainstring .= $query;

    $endtime_toprarechart = microtime();
    $endarray_toprarechart = explode(" ", $endtime_toprarechart);
    $endtime_toprarechart = $endarray_toprarechart[1] + $endarray_toprarechart[0];
    $totaltime_toprarechart = $endtime_toprarechart - $starttime_toprarechart;
    $mainstring .= "<br>Took " . round($totaltime_toprarechart, 1) . " seconds";
} else {

    asort($lastrare);

    $i = 0;
    $mainstring = "";

    $mainstring .= "<table class=\"table table-striped\"><tbody>";

    foreach ($lastrare as $key => $val) {
        if ($i < $glb_indexsubtablelimit && trim($val) != "||") {
            $display = explode("||", $val);
            if ($display[0] == "") {
                $displaydate = "New";
            } else {
                $displaydate = date("dS M H:i", $display[0]);
            }
            $mainstring .= "<tr>";
            $mainstring .= "<td>" . $displaydate . "</td>";
            $mainstring .= "<td><a class='top10data' href='./detail.php?rule_id=" . $key . "&breakdown=source'>" . htmlspecialchars(substr($display[1], 0, 40)) . "...</a></td>";
            $mainstring .= "</tr>";
            $i++;
        }
    }
    $mainstring .= "</tbody></table>";
}

if ($mainstring == "") {
    echo $glb_nodatastring;
} else {
    echo $mainstring;
}
?>
