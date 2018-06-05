<?php
/*
 * Copyright (c) 2017 António 'Tó' Godinho <to@isec.pt>.
 * Copyright (c) 2018 JP P
 * This program is free software; Distributed under the terms of the GNU GPL v3.
 */

print ("================================================================");

# This allows for us to study the previous 10,000 seconds (~3.5 hours) or 100,000 seconds (~1day)
if ($trend_window == 100000) 
{   $trend_window_block      = 100000;
    $trend_window_substr     = 5;
    $trend_window_substrzero = "00000";
} else 
{   $trens_window            = 10000;
    $trend_window_block      = 10000;
    $trend_window_substr     = 6;
    $trend_window_substrzero = "0000";
}


# To make this context aware (i.e. we expect Monday morning to have different traffic levels to Saturday night) we need to look at specific blocks of time that match the current block of time
$where = "(";
for ($j = 0; $j < $glb_trendweeks; $j++) {
    $where .= "( alert.timestamp<" . ($lastfullblock - ($j * 604800)) . "
		AND alert.timestamp>" . ($lastfullblock - $trend_window_block - ($j * 604800)) . "
		) OR ";
}

$where = substr($where, 0, -3) . ")";

$query = " SELECT CONCAT(substring(alert.timestamp, 1, " . $trend_window_substr . "), '" . $trend_window_substrzero . "') as res_time, 
COUNT(alert.id) as res_cnt, 
SUBSTRING_INDEX(SUBSTRING_INDEX(location.name, ' ', 1), '->', 1) as res_loc,
CONCAT(alert.rule_id) as res_field
FROM alert, location, signature
WHERE alert.timestamp<" . $lastfullblock . "
AND alert.location_id=location.id
AND signature.level>=" . $glb_trendlevel . "
AND alert.rule_id=signature.rule_id
AND " . $where . "
GROUP BY res_loc, res_field, res_time
ORDER BY res_loc, res_field, res_time, res_cnt;";

$query = preg_replace('/\t/', ' ', $query);
$query = preg_replace('/\n/', ' ', $query);
# error_log($query,0);
# error_log($lastfullblock,0);
# error_log($glb_trendlevel,0);

$counter = 0;
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
if ($glb_debug == 1) {
    print("<div style='font-size:24px; color:red;'>Debug</div>");
    print($query);
} else 
{ try
	{   $stmt = $pdo->prepare($query);
    	$stmt->execute();
	} catch (Exception $e)
	{	error_log("Sqlerror : " . $e);
		print("<br><br><hr> Sqlerror " . $e);
		die;
	}
	foreach ($stmt->fetchALL() as $row) 
	{ 	$trendarray[$row['res_loc']][$row['res_field']][$row['res_time']] = $row['res_cnt'];
		$counter +=1;
	}

# print ("<br> Nb events=" . $counter);
# This will loop through the results above, remove the highest and lowest results for each server/rule/timeperiod to find a nicer average, then compare the current figure to that. This should show if the current alerts/timeperiod is higher than average.

	if ( $counter > 0 )
	{ $counter = 0;
    foreach ($trendarray as $key => $val) 
	{ 	foreach ($val as $k => $v) 
		{
            # key = client # k = rule id # v = time/count array
            # If v<4 there are not enough historical values to work on
            # Also ensure that the last value from SQL relates to now, not two weeks ago
            end($v);
            if ((count($v) > 1) && (key($v) == ($lastfullblock - $trend_window_block))) {
                #The current and latest 10000 second count
                $lastfullcount = end($v);
                #Remove the current figure to stop it poising the average
                array_pop($v);

                $arraysize = count($v);
                sort($v, SORT_NUMERIC);

                # Remove the highest 5% of values, remove the lowest 5% of values to get rid of spikes
                # UPDATE need to revisit this, if you only look at 10 weeks then removing top digit it actually removing 10% at top and bottom
/*
                for ($i = 0; $i < ($arraysize / 20); $i++) {
                    array_shift($v);
                    array_pop($v);
                }
*/
                # Get an average for the remaining results
                $trendaverage = array_sum($v) / count($v);

                #gives arrayID=>count (top and bottom stripped)

                $average = floor($lastfullcount / $trendaverage * 100);

                if ($lastfullcount > $glb_trendcutoff && (
                        $lastfullcount > ($trendaverage * $glb_trendlimithigh)
                        ||
                        $lastfullcount < ($trendaverage * $glb_trendlimitlow)
                    )) 
				{ # Pop the anwers in to an array for further sorting
                    $finaltrendinfo[$key . "||" . $k . "||" . $lastfullcount] = $average;
					$counter +=1;
                }
            }

        }
      }
	}
	if ( $counter < 1 )
	{	print("<br> Nothing worth !");
	} else
	{ 	# print("<br> OK counter=" . $counter);
		$zzz = 0;
	}
	if ( $counter > 0 ) {
    arsort($finaltrendinfo);
    print( "<div class='clr' style='padding-bottom:0px'></div>");

    print( "<table>");
    print( "<tr><th>Percent</th><th>Count</th><th>Host</th><th>Rule</th><th>Level</th></tr>");

    foreach ($finaltrendinfo as $one => $two) {
        $details = preg_split("/\|\|/", $one);

        $query = "SELECT description as descr, level as lvl FROM signature
			WHERE signature.rule_id=" . $details[1];
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        print("<tr>
			<td><a href='./detail.php?rule_id=" . $details[1] . "&from=" . date("Hi dmy", time() - (86400 * 30)) . "&source=" . $details[0] . "'>" . number_format($two) . "%</a></td>
			<td>" . $details[2] . "</td>
			<td>" . $details[0] . "</td>
			<td>" . $row['descr'] . "</a></td>
			<td>" . $row['lvl'] . "</td>
			</tr>");
        }

    print("</table>");
	}
}

?>
