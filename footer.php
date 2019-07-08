<!--
/*
 * Copyright (c) 2017 António 'Tó' Godinho <to@isec.pt>.
 * Copyright (c) 2019 JP P
 * This program is free software; Distributed under the terms of the GNU GPL v3.
 */
-->
<footer class="footer">
    <div class="col-lg-12" style="height: 100%; text-align: center; vertical-align: middle;">
        <p style="margin-bottom: 20px; margin-top: 20px;">
            <a href="http://www2.isec.pt/~to">© 2017 - António Godinho</a>&nbsp&nbsp<a href=https://performance.izzop.com>© 2018 - JP P</a>
            <?php
            if ($glb_debug == 1) {
                $endtime = microtime();
                $endarray = explode(" ", $endtime);
                $endtime = $endarray[1] + $endarray[0];
                $totaltime = $endtime - $starttime;
                $totaltime = round($totaltime, 2);
                echo "&nbsp;|&nbsp;<span class='tiny'>" . $totaltime . "s</span>";
            }
            ?>
        </p>
    </div>
</footer>
<script src="./js/jquery-3.3.1.js"></script>
