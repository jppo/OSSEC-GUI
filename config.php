<?php
/*
 * Copyright (c) 2017 António 'Tó' Godinho <to@isec.pt>.
 * Copyright (c) 2019 JP P
 * This program is free software; Distributed under the terms of the GNU GPL v3.
 */
### Google map API key Change it, it is mine !
$google_api_key = 'AIzaSyCoEQmv6CTN3zE2Tjji5GS70SBYGlfA2_c';
### Overall

# Not represented whitelist
# A list of locations/servers that are no longer in use.
# implemented = yes
# Note : it may be best to remove alerts from the database, but this is just an option
# example $glb_notrepresentedwhitelist = array ("dummyserver1", "lanserver");
$glb_notrepresentedwhitelist = array();

# Server name Regex
# REGEX string representing which characters are removed from server names when manipulating
# implemeneted = yes
$glb_hostnamereplace = "/[^a-zA-Z0-9_\/\ \-\.]/";

# Index Logarithmic
# Determines whether the index.php graph is logarithmic (useful for widely varying data sources)
# options "true" or "false"   -   NB  MUST be in double quotes
# implemented = yes
$glb_indexgraphlogarithmic = "true";

# No Data String
# Two situations where there no data is present, a query that is too strict, or the database is not logging
# implemented = yes
$glb_nodatastring = "No data found matching this query.";

# Management top # rules
# How many rules to show on management for Rule Tweaking
# implemeneted = yes
$glb_managementtweaking = 30;

# Debug
# implemented = where I've had problems, so in most places
$glb_debug = 0;

# Slideshow Pages
# The page to slide between if wallboard mode is enabled
# implemented = yes
$attackfrom = date("Hi dmy", (time() - (100 * 3600)));
$glb_slidehow_pages = array(
    1 => "index.php?level=9&hours=24&field=rule_id",
    2 => "index.php?level=9&hours=24&field=source",
    3 => "index.php?level=9&hours=24&field=level",
    4 => "massmonitoring.php?",
    5 => "detail.php?rulematch=attack&from=" . $attackfrom
);

# Auto page refresh
# auto refresh to keep the on screen data fresh (not implemented on all pages)
# implemented = yes
$glb_autorefresh = 600;

### Overviewpage / Index.php

# Default level
# Default alert level for the main graph
# implemented = yes
$glb_level = 3;

# Default hours
# Defult hours count for the main graph
# implemeneted = yes
$glb_hours = 48;

# Default graphbreakdown
# Default graph breakdown on page load
# implemented = yes
# options = [source|path|level|rule_id]
$glb_graphbreakdown = "source";

# Graph key
# Determines whether the index.php graph displays a key (keep in mind that you can hover over a dot to get the name, useful to disable for lots of servers
# implemented = yes
$glb_indexgraphkey = 1;

# Index graph all bubbles
# Determins whether the graph should have hover text for all bubbles on the mouse line.  Can be a pain if dozens of servers
# implemented = yes
$glb_indexgraphbubbletext = 1;

# Overview Table Size
# How many entries should the 'Top' subtables have
# implemented = yes
$glb_indexsubtablelimit = 10;

# Out of hours colour
# The colour used by the main graph to indicate out of hours timeperiods
# implemented = yes
$glb_outofhourscolour = "#F08080";

# Out of Hours times
# The times the graph uses to determine what is out of hours.  Only supports complete hours, not minutes
# implemented = yes
$glb_outofhours_daystart = 8;
$glb_outofhours_dayend = 18;


### Breakdown page / detail.php

# Detail.php table limit 
# Max size of the table in detail.php 
# implemented=yes
$glb_detailtablelimit = 500;

# Debug SQL on detail.php
# Useful if you want to drill down in SQL more, or for debugging
# implemented = yes
$glb_detailsql = 0;

# Detail timestamp format
# The format of the timestamp column on the detail page, useful if you copy/paste to spreadsheet/csv
# implemented = yes
# Reference : http://php.net/manual/en/function.date.php
#$glb_detailtimestamp="H:i, jS m/Y";
$glb_detailtimestamp  = "Y/m/d G:i:s ";
$glb_reducedtimestamp = "Y/m/d G:i";
# Highlighted words
# This is merely meant to highlight keywords in the breakdown page, it is not mean to replace you actually creating rules!
# cheekily taken from $BAD_WORDS 
# implemented = yes
$glb_autohighlight = "root|admin|core_dumped|failure|error|attack|bad |illegal |denied|refused|unauthorized|fatal|failed|Segmentation Fault|Corrupted";

# Common Pattern Count
# The amount of common patterns listed
# implemented = yes
$glb_commonpatternscount = 10;

### ip_info.php


### Newsfeed.php

# Threat Days
# The amount of days back in time the threat list will look
# implemented = yes
$glb_threatdays = 30;

# Threat limit
# How many alerts shown in the table
# implemented = yes
$glb_threatlimit = 30;

# Threat Level
# Lowest level to monitor. Probably wont affect usage much.
$glb_threatlevel = 3;

# Trend Warning High
# At what level of normal should the trend be considered high?
# 1 means that if rule is >100% of average it will be alerted, 2 means the trend has to be >200% of aveage to be alerted
# implemented = yes
$glb_trendlimithigh = 1;

# Trend Warning Low
# At what level of normal should the trend be considered low?
# 0.5 means that if rule is <50% of average it will be alerted, 0.1 means the trend has to be <10% of aveage to be alerted
#implemented = yes
$glb_trendlimitlow = 0.5;

# Trend cutoff
# Set the lower level at which trend does not apply, i.e. if an alert hits 3 times compared to 1, that's a 300% jump, but may not be important.  Setting this to 10 means that these rare rules do not get shown
#implemented = yes
$glb_trendcutoff = 10;

# Trend Weeks
# How many weeks back should the Trend tool look. Higher numbers are better, but make the query slower
# implemented = yes
$glb_trendweeks = 2;

# Trend Level
# What level should be looked at for trends. I believe a low number is appropriate here as a 10,000% jump in level 4 is still important
# implemented = yes
$glb_trendlevel = 2;

# IPs Trending
# Top 'x' IPs 
# implemented = yes
$glb_trendip_top = 30;

# IPs Trending Blacklist
# IPs to ignore from looking at who has been 'busy' in the logs
# supports single ip (1.2.3.4) or CIDR (1.2.3.0/24)
# implemented = yes
$glb_trendip_ignore = array(
    "192.168.1.",
    "192.168.2.",
    "172.16.",
    "10.",
    "173.10."
);

### Mass Monitoring

# Mass Monitoring Time period (all graphs)
# Num of days to cover
# implemented = yes
$glb_mass_days = 7;

# Group Ignore
# 'groups' not to report on the Group Activity Over Time report, that graph can get VERY overcrowded
# implemented = yes
# $glb_mass_groupignore = array("apache", "authentication_success", "overwrites", "fts", "generic", "multiple_spam", "smbd", "spam", "sudo", "syslog", "syscheck", "web");
$glb_mass_groupignore = array("");
$glb_mass_groupignore = array("authentication_success","disconnected","fts","su","sudo");

# Hostname Grouping Substring
# For environments with naming conventions, this can shows alerts per group
# Example -  group alerts per switches '-sw-' printers 'pr-' and servers  '-srv'
# Example $glb_mass_hostsubstr=array("-sw-","pr-","-srv");
# regex comptable 
# "exch-svr" will be counted in BOTH 'exch' and 'svr'
# implemented = yes (but buggy, amcharts doesn't like - so results are close, but not perfect)
# do not use minus sign, some javascript charts are using the names as variables
$glb_mass_hostsubstr = array("sw_", "pr_", "_svr", "_srv", "dc[0-9]", "sql", "esx", "exch", "ts[0-9]", "ids", "sensor", "_fw", "mail");

### Management

# Last Agent Checking Period
# report agents that have not checked in for x hours
# implemented = yes
$glb_management_checkin = 48;

# Database Usage - Client vs Level Enabled
# Enable/Disable the chart. For 1000s of hosts this chart may negatively impact response time
# 1=enabled    0=disabled
# implemented = yes
$glb_management_clientvslevel = 1;

# Limit lines displayed for manage_categ_map.php
$glb_query_limit = 50;

### OSSEC Database

# List of databases
# implemented = yes
# 'Main' is the default database used if there are any problems, make this your standard database
$glb_ossecdb['Main'] = "db_ossec.php";

### Adjust Chart Height by screen
### Graph for index.php
$glb_height_index = 380;
$glb_height_detail = 300;
$glb_height_stat_rules = 500;
$glb_height_stat_level = 500;
$glb_height_mass_left  = 600;
$glb_height_mass_right_high = 300;
$glb_height_mass_right_low  = 300;
#
#	List of auth groups to show
#
$glb_group_list = array("ADMIN","CONSULTANT","COLLABORATOR","MANAGER");
?>
