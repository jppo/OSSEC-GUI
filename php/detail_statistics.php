<?php
/*
 * Copyright (c) 2017 António 'Tó' Godinho <to@isec.pt>.
 * Copyright (c) 2018 JP P
 * This program is free software; Distributed under the terms of the GNU GPL v3.
 */
include "amilogged.php";
function write_data($oldlevel,$ctr1,$ctr2,$ctr3,$ctr4,$sep)
{
if ( $ctr1 == "" ) { $ctr1 = 0.1; }
if ( $ctr2 == "" ) { $ctr2 = 0.1; }
if ( $ctr3 == "" ) { $ctr3 = 0.1; }
if ( $ctr4 == "" ) { $ctr4 = 0.1; }
print("{\n");
print("   \"level\": \"Level " . $oldlevel ."\",\n");
print("   \"current\": " . $ctr1 . ",\n");
print("   \"previous\": " . $ctr2 . ",\n");
print("   \"current2\": " . $ctr3 . ",\n");
print("   \"previous2\": " . $ctr4 .  "\n}" . $sep . "\n" );
}

$mainstring = "";
$graphlines = "";

# var $where is set in the file that calls me

$querydata = "select level,week,sum(xcounter) as counter
from (
select \"1\" as week, SUBSTRING_INDEX(SUBSTRING_INDEX(LO.name, \" \", 1), \"->\", 1) as server,
		AA.level,count(*) as xcounter
from alert AA, location LO
where AA.location_id = LO.id
  and AA.timestamp <= " . $dmax1 . "
  and AA.timestamp  > " . $dmax2 . " " . $wheresource . " " . $wherepath . " " . $wherecategory . $whererule_id . $wherelevelmin . "
group by 1,2,3
union
select \"2\" as week, SUBSTRING_INDEX(SUBSTRING_INDEX(LO.name, \" \", 1), \"->\", 1) as server,
		AA.level,count(*) as xcounter
from alert AA, location LO
where AA.location_id = LO.id
  and AA.timestamp <= " . $dmax2 . "
  and AA.timestamp >= " . $dmin . " " . $wheresource . " " . $wherepath . " " . $wherecategory . $whererule_id . $wherelevelmin . "
group by 1,2,3 ";

if ( $wheresource2 != "" )
{	$querydata .= 
"union 
 select \"3\" as week, SUBSTRING_INDEX(SUBSTRING_INDEX(LO.name, \" \", 1), \"->\", 1) as server,
		AA.level,count(*) as xcounter
from alert AA, location LO
where AA.location_id = LO.id
  and AA.timestamp <= " . $dmax1 . "
  and AA.timestamp  > " . $dmax2 . " " . $wheresource2 . " " . $wherepath . " " . $wherecategory . $whererule_id . $wherelevelmin . "
group by 1,2,3
union
select \"4\" as week, SUBSTRING_INDEX(SUBSTRING_INDEX(LO.name, \" \", 1), \"->\", 1) as server,
		AA.level,count(*) as xcounter
from alert AA, location LO
where AA.location_id = LO.id
  and AA.timestamp <= " . $dmax2 . "
  and AA.timestamp >= " . $dmin . " " . $wheresource2 . " " . $wherepath . " " . $wherecategory . $whererule_id . $wherelevelmin . "
group by 1,2,3 ";
	$model = 1;
} else
{	$model = 0;
}

$querydata .= ") xx
group by level,week
order by level,week; ";
$anydata = 0;
if ( $glb_debug == 1 )
	{   print("alert('SQL : " . $query . "');\n");
		$anydata = 0;
		return;
	}
try
	{	# print("// Before prepare\n");
		$stmt = $pdo->prepare($querydata);
		$stmt->execute();
		$anydata = 1;
		$ctr = $stmt->rowcount();
		# print ("// After prepare\n");
	} catch (Exception $e)
	{	print("alert('Sqlerror : " . $e . "');\n");
		print("alert('Sql : (" . $querydata . "');\n");
		return;
	}	
$graphlines = "";
# print ("// ctr=" . $ctr . "\n");

$chglevel = 0;
print ("var chartData = [ \n");
/*
	Model :
	{ "level": "Level 1", "current": nn, "previous": mm },
*/
$flag = 0;
$ctr1 = 0;
$ctr2 = 0;
$ctr3 = 0;
$ctr4 = 0;
$oldlevel = -1;
$ii   = 0;
foreach ( $stmt->fetchALL() as $row)
{	$ii     += 1;
	$level   = $row['level'];
	$week    = $row['week'];
	if ( $flag == 0 )	
	{	$oldlevel = $level;
		$flag = 1;
	} 
	if ( $level != $oldlevel )
	{	write_data($oldlevel,$ctr1,$ctr2,$ctr3,$ctr4,",");
		$ctr1 = 0;
		$ctr2 = 0;
		$ctr3 = 0;
		$ctr4 = 0;
		$oldlevel = $level;
	}
	switch($week)
	{	case 1 :	$ctr1 += $row['counter'];
					break;
		case 2 :	$ctr2 += $row['counter'];
					break;
		case 3 :	$ctr3 += $row['counter'];
					break;
		case 4 :	$ctr4 += $row['counter'];
					break;
		default :	error_log("statistics_2.php week=" . $week);
	}
}
if ( $oldlevel != -1 )
{	write_data($oldlevel,$ctr1,$ctr2,$ctr3,$ctr4,"\n];\n");
}
if ( $ii == 0 )
	{	print(" ];");
	}

print("// detail_statistics_2 \n");
print("model = " . $model . ";\n");
?>
