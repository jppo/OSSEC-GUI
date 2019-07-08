<?php
/*
 * Copyright (c) 2017 António 'Tó' Godinho <to@isec.pt>.
 * Copyright (c) 2019 JP P                              
 * This program is free software; Distributed under the terms of the GNU GPL v3.
 */
require_once './top.php';
include "./amilogged.php";
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
    include './page_refresh.php';
	include './run_his.php';
    ?>
    <link href="./css/style.css" rel="stylesheet" type="text/css"/>
    <link href="./css/sticky-footer.css" rel="stylesheet">

</head>
<body>
<?php
$glb_ossecdb = 0;
include './header.php';
?>
<div class="row">
    <br/>
</div>
<div class="container-fluid" style="padding-top: 80px;">
    <div class="row">
        <div class="col-lg-6">
            <ul class="nav nav-pills" role="tablist" style="width: 100%;">
                <li role="presentation" class="active" style="width: 100%;"><a href="#" style="font-weight: 800">Rule
                        Trend Analysis (~3.5 hours)</a></li>
            </ul>
        </div>
        <div class="col-lg-6">
            <ul class="nav nav-pills" role="tablist" style="width: 100%;">
                <li role="presentation" class="active" style="width: 100%;"><a href="#" style="font-weight: 800">Rule
                        Trend Analysis (~28 hours)</a></li>
            </ul>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-6">
            <?php
            # Set vars for this trend analysis
            $trend_window = 10000;
            $lastfullblock = intval(substr(time(), 0, 6) . "0000");
            ?>
            <small>Comparing Level <?php echo $glb_trendlevel; ?>+ over
                Period <?php echo date("Y/m/d G:i:s", $lastfullblock - $trend_window) . " -> " . date("Y/m/d G:i:s", $lastfullblock) . " over the last " . $glb_trendweeks ?>
                weeks
            </small>
            <br/>

            <div style="max-height:300px; overflow:auto;">
                <?php 
					include "./php/newsfeed_trend.php"; 
				?>
            </div>
        </div>
        <div class="col-lg-6">
            <?php
            # Set vars for this trend analysis
            $trend_window = 100000;
            $lastfullblock = intval(substr(time(), 0, 5) . "00000");
            ?>
            <small>Comparing Level <?php echo $glb_trendlevel; ?></small>+ over
                Period <?php echo date("Y/m/d G:i:s", $lastfullblock - $trend_window) . " -> " . date("Y/m/d G:i:s", $lastfullblock) . " over the last " . $glb_trendweeks ?>
                weeks
                <br/>

                <div style="max-height:300px; overflow:auto;">
                    <?php include "php/newsfeed_trend.php"; ?>
                </div>
        </div>
    </div>


    <div class="row">
        <div class="col-lg-6">
            <ul class="nav nav-pills" role="tablist" style="width: 100%;">
                <li role="presentation" class="active" style="width: 100%;"><a href="#" style="font-weight: 800">Alert
                        Threat Feed</a></li>
            </ul>
        </div>
        <div class="col-lg-6">
            <ul class="nav nav-pills" role="tablist" style="width: 100%;">
                <li role="presentation" class="active" style="width: 100%;"><a href="#" style="font-weight: 800">IPs
                        Trending</a></li>
            </ul>
        </div>
        <div class="col-lg-6">
            <?php
            # Set vars for this trend analysis
            $trend_window = 1000000;
            $lastfullblock = intval(substr(time(), 0, 5) . "000000");
            ?>
            <small>Grouped list of most important alerts over the last <?php echo $glb_threatdays; ?></small> days,
                level <?php echo $glb_threatdays; ?>+.
                <br/>

                <div style="max-height:300px; overflow:auto;">
                    <?php include './php/newsfeed_threat.php'; ?>
                </div>
        </div>
        <div class="col-lg-6">
            <?php
            # Set vars for this trend analysis
            $trend_window = 10000;
            $lastfullblock = intval(substr(time(), 0, 5) . "0000");
            ?>
            <div class="introbody" style='height:25px;padding-bottom:10px;'>Top <span
                        class='tw'><?php echo $glb_trendip_top; ?></span> IPs appear most in the logs over the last
                <span class='tw'><?php echo $glb_threatdays ?></span> days. One alert may span multiple groups<br><br>
            </div>

            <div style="max-height:300px; overflow:auto;">
                <?php include './php/newsfeed_trendip.php'; ?>
            </div>
        </div>
    </div>

    <div class='clr'></div>
    <?php
    include 'footer.php';
    ?>
