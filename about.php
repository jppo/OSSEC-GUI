<?php
/*
 * Copyright (c) 2017 António 'Tó' Godinho <to@isec.pt>.
 * Copyright (c) 2018 JP P
 * This program is free software; Distributed under the terms of the GNU GPL v3.
 */
require './top.php';
include 'amilogged.php';
include 'db_ossec.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">
    <title>OSSEC GUI</title>
<?php
	include './run_his.php';
?>
    <link href="./css/style.css" rel="stylesheet" type="text/css"/>
    <link href="./css/sticky-footer.css" rel="stylesheet">
</head>
<body>
<?php 
include "db_ossec.php";
include "header.php"; 
?>

<div class='clr'></div>

<div class='top10header'>About</div>
<div class="introbody"><br><H3>Information about AnaLogi, OSSEC-WUI and OSSEC-GUI</H3>
<br> Version adapted for OSSEC >= 2.9.3, database version 2 without table "data",
	by JPP so no modifications to database are needed for OSSEC V2.9.3+ or V3.0 beta.
	Exception : a trigger on "alert" table has to be added for more comfort.
	<br>
    Functions added : 
<br>- Database reorganization, ability to delete a record on the "detail" page.
<br>- Login and group management (Authentication from PHP AUTH : https://github.com/delight-im/PHP-Auth) for managing access authorizations.
<br>- Some graphical statistics
<br>- Tested with PHP7 7.0.27-0+deb9u1, should work with PHP5 ?
<br><br> 'ANAlytical LOG Interface' built to sit on top of OSSEC (built on OSSEC 2.6)
<br> OSSEC-GUI was forked from version OSSEC-WUI from António 'Tó' Godinho <to@isec.pt>.
<br> AnaLogi was built for OSSEC 2.6 and required 0 modifications to OSSEC or the
    database schema that ships with OSSEC. AnaLogi (and OSSEC-WUI or OSSEC-GUI) requires a Webserver sporting
    PHP and MySQL (works also with MariaDB). As for now PHP7 is standard.
</div>

<div class='top10header'>To say Thanks</div>
<div class="introbody">AnaLogi has no real tracking of how many people use it (no 1px images in the code etc).<br>If you would like to say thanks and show me that this project was worth releasing please click the following
    link.<br><a href='http://www.ecsc.co.uk/analogi.html'>AnaLogi</a> at ECSC (I check the logs time to time for hits)
</div>

<div class='top10header'>FAQ</div>
<div class="introbody">
    All tweakable parts of AnaLogi are stored in config.php
    <br> Tweakable bits of the interface are displayed as <span class='tw'>such</span>
</div>

<div class='top10header'>Analogi latest Version</div>
<div class="introbody">The latest Version can be found <a href='https://github.com/ECSC/analogi/downloads'>here</a>
</div>
<div class='top10header'>OSSEC-WUI  for OSSEC versions &lt; 2.9.3</div>
<div class="introbody">The latest Version can be found <a href='https://github.com/NunesGodinho/OSSEC-WUI'>here</a>
</div>
<div class='top10header'>OSSEC-GUI V3.0 for OSSEC versions &gt; 2.9.3 or V3.0 beta</div>
<div class="introbody">The latest Version can be found <a href='https://github.com/jppo/OSSEC-GUI'>here</a>
</div>

<div class='top10header'>Wiki</div>
<div class="introbody">Click <a href='https://github.com/ECSC/analogi/wiki'>here</a> (wip)</div>

<div class='top10header'>Links</div>
<div class="introbody">
    In no particular order
    <li>https://www.ossec.net</li>
    <li>https://www.amazon.com/OSSEC-Host-Based-Intrusion-Detection-Guide/dp/159749240X</li>
    <li>https://groups.google.com/forum/?fromgroups#!forum/ossec-list</li>
    <li>https://dcid.me/notes/</li>
	<li>https://github.com/delight-im/PHP-Auth</li>

</div>


<div class='clr'></div>
<?php
include 'footer.php';
?>
