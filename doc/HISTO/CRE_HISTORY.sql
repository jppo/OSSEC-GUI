/*
	Create tables for OSSEC version V2.9.3 "history"
	Only the "alert" table here.
*/
--
-- Table structure for table `alert`
--

DROP TABLE IF EXISTS `alert`;
CREATE TABLE `alert` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `server_id` smallint(5) unsigned NOT NULL,
  `rule_id` mediumint(8) unsigned NOT NULL,
  `level` tinyint(3) unsigned DEFAULT NULL,
  `timestamp` int(10) unsigned NOT NULL,
  `location_id` smallint(5) unsigned NOT NULL,
  `src_ip` varchar(46) NOT NULL,
  `dst_ip` varchar(46) NOT NULL,
  `src_port` smallint(5) unsigned DEFAULT NULL,
  `dst_port` smallint(5) unsigned DEFAULT NULL,
  `alertid` varchar(30) DEFAULT NULL,
  `user` text NOT NULL,
  `full_log` text NOT NULL,
  `is_hidden` tinyint(4) NOT NULL DEFAULT '0',
  `tld` varchar(5) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`,`server_id`),
  KEY `alertid` (`alertid`),
  KEY `level` (`level`),
  KEY `time` (`timestamp`),
  KEY `rule_id` (`rule_id`),
  KEY `src_ip` (`src_ip`),
  KEY `tld` (`tld`)
) ENGINE=InnoDB AUTO_INCREMENT=8787727 DEFAULT CHARSET=utf8;

