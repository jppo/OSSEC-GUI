<?php
/*
 * Copyright (c) 2017 António 'Tó' Godinho <to@isec.pt>.
 * Copyright (c) 2019 JP P
 * This program is free software; Distributed under the terms of the GNU GPL v3.
 */

$whereignore = "";


# Construct the SQL code to ignore IPs 
$whereignore = "";
foreach ($glb_trendip_ignore as $ignoreips) 
{ 	$whereignore .= "and a.src_ip not like '" . $ignoreips . "%' ";
}
# This was originally just a subquery, but to keep order results I need to order by the string used for the 'WHERE IN' which is dynamic, so I need to parse the string with PHP so I can use it twice :/
$query = "select res_ip from 
	 ( SELECT a.src_ip as res_ip,
	  count(a.id) as res_count
	  from alert a
	  where a.timestamp > " . (time() - ($glb_threatdays * 24 * 3600)) .
	  " and a.src_ip is not null " .
	  " and a.src_ip <> '(null)' " .
	  " " . $whereignore . "  group by res_ip order by res_count desc
	  limit " . $glb_trendip_top . ") as snuff where res_ip is not null;";
$query = preg_replace('/\t/', ' ', $query);
$query = preg_replace('/\n/', ' ', $query);
// $glb_debug = 1;
$nbrows = 0;
if ($glb_debug == 1) 
{ 	echo "<div style='font-size:24px; color:red;'>Debug</div>";
    echo $query . "<br>";
	error_log($query,0);
	exit("fatal");
} else 
{ 	$whereinorderby = "";
	try
	{ 	$stmt = $pdo->prepare($query);
    	$stmt->execute();
	} catch (Exception $e)
	{ 	echo "<div style='font-size:24px; color:red;'>Error</div>";
		echo "<br> Sqlerror : " . $e;
    	echo "<br>" . $query;
		error_log($query,0);
		exit("fatal");
	}
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $whereinorderby .= "'" . $row['res_ip'] . "',";
		$nbrows += 1;
    }
if ( $nbrows > 0 )
	{	$whereinorderby = preg_replace('/,$/', '', $whereinorderby);
	} else
	{	$whereinorderby = " 'qwert'  ";
	}
}
if ( $glb_debug == 1 )
{	echo "<div style='font-size:24px; color:red;'>Debug</div>";
    echo $query;
	error_log($query,0);
	echo "<br>------------------------------------------------------";
	echo "<br>xxx={" . $whereinorderby . "}";
	echo "<br>------------------------------------------------------";
	exit("SALUT");
}
# Now the final query
$query = "select 
	alert.src_ip as res_ip,
	count(alert.id) as res_cnt,
	category.cat_name as res_name,
	category.cat_id as res_id
	from alert, signature_category_mapping, category
	where alert.timestamp > " . (time() - ($glb_threatdays * 24 * 3600)) . "
	and alert.rule_id=signature_category_mapping.rule_id
	and signature_category_mapping.cat_id=category.cat_id"
	. " and alert.src_ip in (" . $whereinorderby . ") group by res_ip, res_name order by field (alert.src_ip, " . $whereinorderby . ");";
$query = preg_replace('/\t/', ' ', $query);
$query = preg_replace('/\n/', ' ', $query);

if ($glb_debug == 1)
{ 	echo "<div style='font-size:24px; color:red;'>Debug</div>";
	echo "<br>------------------------------------------------------------------<br>";
    echo $query;
	echo "<br>------------------------------------------------------------------";
	# error_log($query,0);
	exit(0);
} else 
{ 	echo "<div class='clr' style='padding-bottom:0px'></div>";
	echo "<br><table>";
    echo "<tr><th>IP</th><th>Groups (count)<br></th></tr>";
   	$tmpip = array();
try
	{ 	$stmt = $pdo->prepare($query);
    	$stmt->execute();
	} catch ( Exception $e)
	{	echo "<div style='font-size:24px; color:red;'>Error</div>";
		echo "<br> Sqlerror : " . $e;
		print("<br>" . $query);
		exit("Sqlerror");
	}
    foreach ( $stmt->fetchALL() as $row) 
	{ 	$tmpip[$row['res_ip']][$row['res_id'] . "|" . $row['res_name']] = $row['res_cnt'];
    }

    $prevdate = date("Hi dmy", (time() - ($glb_threatdays * 86400)));

    foreach ($tmpip as $key => $val) 
	{ # $key = ip
echo "<tr><td><a href='ip_info.php?ip=" . $key . "'>" . $key . "</td><td>";
arsort($val);

        foreach ($val as $k => $v) 
		{ # $k = '1|squid'
          # $v = 123

            $categorybits = preg_split('/\|/', $k);

            echo "<a href='detail.php?from=" . $prevdate . "&category=" . $categorybits[0] . "&ipmatch=" . $key . "'>" . $categorybits[1] . "</a> (" . number_format($v) . "), ";

        }
        echo "</tr>";
    }
    echo "</table>";
}
?>
