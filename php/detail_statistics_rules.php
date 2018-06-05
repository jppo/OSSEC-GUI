<?php
/*
 * Copyright (c) 2017 António 'Tó' Godinho <to@isec.pt>.
 * Copyright (c) 2018 JP P
 * This program is free software; Distributed under the terms of the GNU GPL v3.
 */
include "amilogged.php";
function write_data($oldrule_id,$olddesc,$ctr1,$ctr2,$ctr3,$ctr4,$sep)
{
if ( $ctr1 == "" ) { $ctr1 = 0.1; }
if ( $ctr2 == "" ) { $ctr2 = 0.1; }
if ( $ctr3 == "" ) { $ctr3 = 0.1; }
if ( $ctr4 == "" ) { $ctr4 = 0.1; }
print("{\n");
print("   \"rule\": \"" . $oldrule_id ."\",\n");
print("   \"libel\": \"" . $olddesc . "\",\n");
print("   \"current\": " . $ctr1 . ",\n");
print("   \"previous\": " . $ctr2 . ",\n");
print("   \"current2\": " . $ctr3 . ",\n");
print("   \"previous2\": " . $ctr4 .  "\n}" . $sep . "\n" );
}

$mainstring = "";
$graphlines = "";

# var $where is set in the file that calls me

$querydata = "select id,description,sum(acounter) as counter1, sum(bcounter) as counter2, 
sum(ccounter) as counter3, sum(dcounter) as counter4,
sum(acounter) + sum(bcounter) + sum(ccounter) + sum(dcounter) as total
from (
select \"1\" as week, SUBSTRING_INDEX(SUBSTRING_INDEX(LO.name, \" \", 1), \"->\", 1) as server,
		SG.id, SG.description,
		count(*) as acounter, 0 as bcounter,0 as ccounter, 0 as dcounter
from alert AA, location LO, signature SG
where AA.location_id = LO.id
  and AA.rule_id     = SG.rule_id
  and AA.timestamp  <= " . $dmax1 . "
  and AA.timestamp  > " . $dmax2 . " " . $wheresource . " " . $wherepath . " " . $wherecategory . $whererule_id . $wherelevelmin . "
group by 1,2,3,4
union
select \"2\" as week, SUBSTRING_INDEX(SUBSTRING_INDEX(LO.name, \" \", 1), \"->\", 1) as server,
		SG.id, SG.description,
		0 as acounter, count(*) as bcounter,0 as ccounter, 0 as dcounter
from alert AA, location LO, signature SG
where AA.location_id = LO.id
  and AA.rule_id     = SG.rule_id
  and AA.timestamp <= " . $dmax2 . "
  and AA.timestamp >= " . $dmin . " " . $wheresource . " " . $wherepath . " " . $wherecategory . $whererule_id . $wherelevelmin . "
group by 1,2,3,4 ";

if ( $wheresource2 != "" )
{	$querydata .= 
"union 
 select \"3\" as week, SUBSTRING_INDEX(SUBSTRING_INDEX(LO.name, \" \", 1), \"->\", 1) as server,
		SG.id, SG.description,
		0 as acounter, 0 as bcounter, count(*) as ccounter, 0 as dcounter
from alert AA, location LO, signature SG
where AA.location_id = LO.id
  and AA.rule_id     = SG.rule_id
  and AA.timestamp  <= " . $dmax1 . "
  and AA.timestamp   > " . $dmax2 . " " . $wheresource2 . " " . $wherepath . " " . $wherecategory . $whererule_id . $wherelevelmin . "
group by 1,2,3,4
union
select \"4\" as week, SUBSTRING_INDEX(SUBSTRING_INDEX(LO.name, \" \", 1), \"->\", 1) as server,
		SG.id, SG.description,
		0 as acounter,0 as bcounter,0 as ccounter,count(*) as dcounter
from alert AA, location LO, signature SG
where AA.location_id = LO.id
  and AA.rule_id     = SG.rule_id
  and AA.timestamp  <= " . $dmax2 . "
  and AA.timestamp  >= " . $dmin . " " . $wheresource2 . " " . $wherepath . " " . $wherecategory . $whererule_id . $wherelevelmin . "
group by 1,2,3,4 ";
	$model = 1;
	$limitmax = 60;
} else
{	$model = 0;
	$limitmax = 90;
}

$querydata .= ") xx
group by id
order by total DESC,id limit " . $limitmax . "; ";
$anydata = 0;
# $glb_debug = 1;
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
print ("// ctr=" . $ctr . "\n");

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
$oldrule_id = -1;
$olddesc = "";
$ii   = 0;
foreach ( $stmt->fetchALL() as $row)
{	$ii       += 1;
	$rule_id   = $row['id'];
	$rule_desc = $row['description'];
	if ( $flag == 0 )	
	{	$oldrule_id = $rule_id;
		$olddesc    = $rule_desc;
		$flag = 1;
	} 
	if ( $rule_id != $oldrule_id )
	{	write_data($oldrule_id,$olddesc,$ctr1,$ctr2,$ctr3,$ctr4,",");
		$ctr1 = 0;
		$ctr2 = 0;
		$ctr3 = 0;
		$ctr4 = 0;
		$oldrule_id = $rule_id;
		$olddesc    = $rule_desc;
	}
	$ctr1 += $row['counter1'];
	$ctr2 += $row['counter2'];
	$ctr3 += $row['counter3'];
	$ctr4 += $row['counter4'];
}
if ( $oldrule_id != -1 )
{	write_data($rule_id,$olddesc,$ctr1,$ctr2,$ctr3,$ctr4,"\n];\n");
}
if ( $ii == 0 )
	{	print("];");
	}

print("// detail_statistics_rules.php \n");
print("model = " . $model . ";\n");
?>
