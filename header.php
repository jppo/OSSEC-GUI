<?php
/*
 * Copyright (c) 2017 António 'Tó' Godinho <to@isec.pt>.
 * Copyright (c) 2019 JP P
 * This program is free software; Distributed under the terms of the GNU GPL v3.
 */
require_once './db_ossec.php';
if ( ! empty($auth) )
{	$USER = " (" . $_SESSION['myname'] . ")";
} else
{	$USER = "";
}

if ( constant('DB_TYPE_O') == 'history' ) 
  { print ('<nav classname="MyNavbar" class="navbar navbar-inverse navbar-expand-lg navbar-fixed-top">');
    print ('<div class="container-fluid">');
    print ('<div class="navbar-header">');
    print ('<a href="./index.php?" class="navbar-brand text-primary">OSSEC - GUI - ' . $VERSION  . ' Mode:History' . ' ' . $USER . '</a>');
    print ('</div>');
  } else
  { print ('<nav classname="MyNavbar" class="navbar navbar-inverse navbar-expand-lg navbar-fixed-top">');
    print ('<div class="container-fluid">');
    print ('<div class="navbar-header">');
    print ('<a href="./index.php?" class="navbar-brand text-primary">OSSEC - GUI - ' . $VERSION .  ' Mode:Running' . ' ' . $USER . '</a>');
    print ('</div>');
  }
?>
        
        <ul class="nav navbar-nav navbar-right">
            <?php
            if (isset($glb_ossecdb) && count($glb_ossecdb) > 1) {
                ?>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true"
                       aria-expanded="false">Dropdown <span class="caret"></span></a>
                    <ul class="dropdown-menu">
                        <form action='./index.php'>
                            <select name='glb_ossecdb'
                                    onchange='document.cookie = \"ossecdbjs=\"+glb_ossecdb.options[selectedIndex].value ; location.reload(true)'>
                                <?php
                                foreach ($glb_ossecdb as $name => $file) {
                                    if ($_COOKIE['ossecdbjs'] == $name) {
                                        $glb_ossecdb_selected = " SELECTED ";
                                    } else {
                                        $glb_ossecdb_selected = "";
                                    }
                                    $glb_ossecdb_option .= "<option value='" . $name . "' " . $glb_ossecdb_selected . " >" . $name . " (" . DB_NAME_O . ", " . DB_HOST_O . ")</option>";
                                }
                                echo $glb_ossecdb_option;
                                ?>
                            </select>
                        </form>
                    </ul>
                </li>
                <?php
			}
            ?>
            <li><a href="./index.php?">Home</a></li>
            <li><a href="./newsfeed.php?">NewsFeed</a></li>
            <li><a href="./massmonitoring.php?">Mass Monitoring</a></li>
            <li><a href="./detail.php?from=<?php echo date("Hi dmy", (time() - (3600 * 24 * 30))) ?>">Detail</a></li>
            <li><a href="./ip_info.php?">IP Info</a></li>
<?php
if ( $ISADMIN )
{
print(' <li><a href="#"');
print('onclick=\'alert("Warning : Due to the complexity of the code, this page may take a few minute to load."); window.location = "./management.php"\'>Management</a> </li>');
}?>
            <li><a href="./statistics_level.php?from=<?php echo date("Hi dmy", (time() - (3600 * 24 * 30))) ?>">Statistics</a></li>
            <li role="separator" class="divider"></li>
		<?php
			if ( ! empty($auth) ) 
            { print('<li><a href="./logout.php">Logout</a></li>');
			}
		?>
            <li><a href="./about.php">About</a></li>
        </ul>
     </div>
    </nav>

