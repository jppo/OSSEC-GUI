<?php
/*
 * Copyright (c) 2017 António 'Tó' Godinho <to@isec.pt>.
 * Copyright (c) 2019 JP P
 * This program is free software; Distributed under the terms of the GNU GPL v3.
 */
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
	include './run_his.php';
	require_once './config.php';
	require_once './db_ossec.php';
?>

    <link href="./css/style.css" rel="stylesheet" type="text/css"/>
    <link href="./css/sticky-footer.css" rel="stylesheet" type="text/css">
    <script src="./js/amcharts.js" type="text/javascript"></script>
    <script src="./js/serial.js" type="text/javascript"></script>
    <script src="./js/themes/light.js" type="text/javascript"></script>

    <script type="text/javascript">
        function databasetest() 
        {	// <!--  If no data, alerts will be created in here  -->
            <?php include './databasetest.php' ?>
        }

        <?php
        include './php/index_graph.php';
        ?>

        var chart = AmCharts.makeChart("chartdiv", {
            type: 'serial',
            theme: 'light',
            dataProvider: chartData,
            dataDateFormat: "YYYY-MM-DD",
            categoryField: 'date',
            startDuration: 0.1,
            balloon: {
                color: '#000000'
            },
            zoomOutOnDataUpdate: true,
            pathToImages: './js/images/',
            zoomOutButton: true,
            zoomOutButtonColor: '#000000',
            zoomOutButtonAlpha: 0.15,
            categoryAxis: {
                fillAlpha: 1,
                fillColor: '#FAFAFA',
                gridAlpha: 0,
                axisAlpha: 0,
                gridPosition: 'start',
                position: 'top',
                parseDates: true,
                minPeriod: 'mm'
            },
            valueAxes: [
                {
                    title: 'Alerts',
                    logarithmic: <?php echo $glb_indexgraphlogarithmic; ?>
                }
            ],
            chartScrollbar: {
                updateOnReleaseOnly: true,
                //"graph" : graph0,
                scrollbarHeight: 40,
                color: '#000000',
                gridColor: '#000000',
                backgroundColor: '#FFFFFF',
                autoGridCount: true
            },
            <?php
            if ($glb_indexgraphbubbletext == 1) 
			{ 	echo "
                    chartCursor: {
                        cursorPosition : 'mouse',
                        categoryBalloonDateFormat : 'JJ:NN, DD MMMM'
                    },";
            } else
			{ 	echo "
                    chartCursor: {
                        cursorPosition : 'mouse',
                        categoryBalloonDateFormat : 'JJ:NN, DD MMMM'
                    },";
            }
            if ($glb_indexgraphkey == 1) 
			{ 	echo "
                    legend: {
                        markerType: 'circle'
                    },";
            } else
			{ 	echo "
                    legend: {
                        markerType: 'circle'
                    },";
            } 
            ?>
            chartCursor: {
                pan: false,
                zoomable: true
            },
            guides: [
                <?php
                echo $workinghoursguide;
                ?>
            ]
        });

        chart.validateNow();
        <?php
        echo $graphlines;
        ?>
        chart.validateNow();

    </script>


</head>
<body onload="databasetest();">
<?php include './header.php'; ?>

<div class="container-fluid" style="padding-top: 80px;">

    <div class="row">
        <div id="chartdiv" style="width:100%; height:500px;"><?php echo $nochartdata; ?></div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <ul class="nav nav-pills" role="tablist" style="width: 100%;">
                <li role="presentation" class="active" style="width: 100%;"><a href="#"
                                                                               style="font-weight: 800">Filters</a></li>
            </ul>
        </div>
    </div>

    <form method='GET' action='./index.php' class="form-inline">
        <div class="row">
            <div class="col-lg-1 form-group">
                <label>Level</label>
            </div>
            <div class="col-lg-2 form-group">
                <label>Hours</label>
            </div>
            <div class="col-lg-4 form-group">
                <label>Graph Breakdown</label>
            </div>
            <div class="col-lg-4 form-group">
                <label>Category</label>
            </div>
            <div class="col-lg-1 vc">
            </div>
        </div>
        <div class="row">
            <div class="col-lg-1 form-group">
                <select name='level' class="form-control">
                    <option value=''>--</option>
                    <?php echo $filterlevel; ?>
                </select>
            </div>
            <div class="col-lg-2 form-group">
                <input type='text' class="form-control" name='hours' value='<?php echo $inputhours; ?>'/>
            </div>
            <div class="col-lg-4 form-group">
                <input type='radio' name='field' value='source' class="checkbox-inline" <?php echo $radiosource; ?> />Source
                <input type='radio' name='field' value='path' class="checkbox-inline" <?php echo $radiopath; ?> />Path
                <input type='radio' name='field' value='rule_id' class="checkbox-inline" <?php echo $radiorule_id; ?> />Rule
                <input type='radio' name='field' value='level' class="checkbox-inline" <?php echo $radiolevel; ?> />Level
                ID
            </div>
            <div class="col-lg-4 form-group">
                <select name='category' class="form-control">
                    <option value=''>--</option>
                    <?php echo $filtercategory; ?>
                </select>
            </div>
            <div class="col-lg-1 form-group">
                <input type='submit' value='..go..' class="btn btn-warning"/>
            </div>
        </div>
    </form>


    <hr/>

    <div class="row">
        <div class="col-lg-4 col-md-4 col-xs-4">
            <?php include './php/topid.php'; ?>
        </div>
        <div class="col-lg-4 col-md-4 col-xs-4">
            <?php include './php/toplocation.php'; ?>
        </div>
        <div class="col-lg-4 col-md-4 col-xs-4">
            <?php include './php/toprare.php'; ?>
        </div>
    </div>

    <div class='row'></div>

    <?php
    include './footer.php';
    ?>
    <script type="text/javascript">
        <?php
        echo $graphheight;
        ?>
    </script>
</div>
</body>
</html>
