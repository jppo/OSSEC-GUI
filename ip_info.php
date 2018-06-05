<?php
/*
 * Copyright (c) 2017 António 'Tó' Godinho <to@isec.pt>.
 * Copyright (c) 2018 JP P
 * This program is free software; Distributed under the terms of the GNU GPL v3.
 */
require_once './run_his.php';
require_once './config.php';
require_once './top.php';
include "./amilogged.php";

if (!(isset($_GET['ip']) && $_GET['ip']) || !filter_var($_GET['ip'], FILTER_VALIDATE_IP)) {
    $ip = $_SERVER['REMOTE_ADDR'];
} else {
    $ip = $_GET['ip'];
}


# Get GeoIP stuff into JSON format
$url = "http://freegeoip.net/json/" . $ip;
$content = get_content($url);
$jsoned = json_decode($content);
$jsonlat = $jsoned->{'latitude'};
$jsonlng = $jsoned->{'longitude'};
if ($jsonlat == "") {
    $jsonlat = "0";
}
if ($jsonlng == "") {
    $jsonlng = "0";
}

#var_dump($jsoned);
# Get AS and CIDR
$url = "https://secure.dshield.org/api/ip/" . $ip;
$content = get_content($url);
$xml = simplexml_load_string($content);
$ip_isp = $xml->asname;
$ip_range = $xml->network;
if ( $ip_range == '' ) { $ip_range = 'None given' ; }
$ip_attacks = $xml->attacks;
if ( $ip_attacks == '' ) { $ip_attacks = 0; }


#First Instance
$query = "SELECT alert.timestamp as first
	FROM alert
	WHERE alert.src_ip='" . $ip . "'
	ORDER BY alert.timestamp
	LIMIT 1";
$stmt = $pdo->prepare($query);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$firstinstance = $row['first'];

# Seen at
$query = "SELECT distinct(substring_index(substring_index(location.name, ' ', 1), '->', 1)) as loc_name
	FROM location
	WHERE location.id in ( select location_id from alert where alert.full_log like '%" . $ip . "%');";
if ($glb_debug == 1) {
    $seenat = "<div style='font-size:24px; color:red;'>Debug</div>";
    $seenat .= $query;
} else {
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $seenat = "";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $seenat .= "<a href='detail.php?datamatch=" . $ip . "&source=" . $row['loc_name'] . "&level=7'>" . $row['loc_name'] . "</a>, ";
    }
}
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
    <title>OSSEC WUI</title>

    <?php
    include "page_refresh.php";
    ?>

<!--    <link href="./css/bootstrap.min.css" rel="stylesheet"> -->
    <link href="./css/style.css" rel="stylesheet" type="text/css"/>
    <link href="./css/sticky-footer.css" rel="stylesheet">

<?php
    print ('<script src="https://maps.googleapis.com/maps/api/js?key=' . $google_api_key . '"></script>');
?>
    <script>
        var map;

        function initialize() {
            var mapOptions = {
                zoom: 5,
                center: new google.maps.LatLng(<?php echo $jsonlat . ", " . $jsonlng ?>),
                disableDefaultUI: true,
                mapTypeId: google.maps.MapTypeId.ROADMAP
            };
            map = new google.maps.Map(document.getElementById('map_canvas'),
                mapOptions);
        }

        google.maps.event.addDomListener(window, 'load', initialize);
    </script>

</head>
<!-- <body onload="databasetest()"> -->
<body>
<?php include './header.php'; ?>
<div class="container-fluid" style="padding-top: 80px;">
    <div class='clr'></div>

    <div>
        <form method="GET" action="./ip_info.php?">
            <input type='text' name='ip'/>
            <input type='submit' value='Search'/>
        </form>
    </div>

    <div class='clr gap'></div>

    <div class='top10header'>IP Address - <?php echo $ip ?></div>
    <div style="width:50%" class='fleft'>


        <div class='wide fleft'>Hostname</div>
        <div class='fleft'><?php echo gethostbyaddr($ip) ?></div>
        <div class='clr gap'></div>

        <div class='wide fleft'>ISP</div>
        <div class='fleft'><?php echo $ip_isp ?></div>
        <div class='clr gap'></div>

        <div class='wide fleft'>Network Range</div>
        <div class='fleft'><?php echo $ip_range ?></div>
        <div class='clr gap'></div>

        <div class='wide fleft'><a href='http://www.dshield.org/ipinfo.html?ip=<?php echo $ip; ?>'>dshield</a> have
            counted
        </div>
        <div class='fleft'><?php echo $ip_attacks ?> attacks from this IP</div>
        <div class='clr gap'></div>

        <div class='wide fleft'>First Ossec Alert</div>
        <div class='fleft'><?php
            $x = (strlen($firstinstance) > 0) ? date($glb_detailtimestamp, $firstinstance) : "-";
            echo $x;
            ?></div>
        <div class='clr gap'></div>

        <div class='wide fleft'>Country</div>
        <div class='fleft'><?php 
				$cnty = $jsoned->{'country_name'}; 
				if ( $cnty == '') { print('None');} else { print($cnty); }
		?>
		</div>
        <div class='clr gap'></div>

        <div class='wide fleft'>Detail Breakdown</div>
        <div class='fleft'><a
                    href='detail.php?datamatch=<?php echo $ip ?>&from=<?php echo date("Hi dmy", $firstinstance) ?>'>View</a>
        </div>
        <div class='clr gap'></div>

        <div class='wide fleft'>Seen At</div>
        <div class='fleft' style='width:370px; height:80px; overflow:auto;'><?php echo $seenat; ?></div>
        <div class='clr'></div>


    </div>
    <div style="width:50%" class='fleft'>

        <div id="map_canvas" style="width:420px ; height:250px"></div>
        <div class='tiny'>Geo Location accuracy may vary</div>
    </div>


    <div class='clr'></div>
    <div class='gap'></div>
    <div class='top10header'>Useful Links</div>

<!--   no more avalaible/interesting
    <div><a href="http://www.dnsstuff.com/tools/ptr.ch?ip=<?php echo $ip ?>">DNS Stuff PTR</a></div>
    <div><a href="http://www.dnsstuff.com/tools/whois.ch?ip=<?php echo $ip ?>">DNS Stuff Whois</a></div>
-->
    <div><a href="http://www.whois.sc/<?php echo $ip ?>">Whois (with captcha)</a></div>
    <div><a href="https://www.dshield.org/ipinfo.html?ip=<?php echo $ip ?>&Submit=Submit">DShield</a></div>
<!-- McAffee access pro
    <div><a href="http://www.trustedsource.org/query.php?q=<?php echo $ip ?>">Trusted Source</a></div>
-->
    <div><a href="https://isc.sans.org/ipinfo.html?ip=<?php echo $ip ?>">SANS</a></div>
    <div><a href="https://www.mcafee.com/threat-intelligence/ip/default.aspx?ip=<?php echo $ip ?>">McAfee</a></div>
    <div><a href="https://www.senderbase.org/senderbase_queries/detailip?search_string=<?php echo $ip ?>">Cisco
            Lookup</a></div>
    <div><a href="https://www.robtex.com/ip/<?php echo $ip ?>.html">Robtex</a></div>
    <div><a href="https://www.mxtoolbox.com/SuperTool.aspx?action=blacklist%3a<?php echo $ip ?>">MxToolBox</a></div>
    <div></div>

    <div class="clr"></div>
    <div style='padding:40px ;width:95%; text-align:center;'>
        <a class='tiny' href='http://www.ecsc.co.uk/analogi.html'>ECSC | Vendor Independent Information Security
            Specialists</a>
    </div>

    <div class='clr'></div>

    <?php
    include 'footer.php';
    ?>
