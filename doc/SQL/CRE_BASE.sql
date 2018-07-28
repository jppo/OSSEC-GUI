/*
	Create tables for OSSEC version V2.9.3
*/
--
-- Table structure for table `agent`
--

DROP TABLE IF EXISTS `agent`;
CREATE TABLE `agent` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `server_id` smallint(5) unsigned NOT NULL,
  `last_contact` int(10) unsigned NOT NULL,
  `ip_address` varchar(46) NOT NULL,
  `version` varchar(32) NOT NULL,
  `name` varchar(64) NOT NULL,
  `information` varchar(128) NOT NULL,
  PRIMARY KEY (`id`,`server_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `alert`
--

DROP TABLE IF EXISTS `alert`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

--
-- Table structure for table `category`
--

DROP TABLE IF EXISTS `category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `category` (
  `cat_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cat_name` varchar(32) NOT NULL,
  PRIMARY KEY (`cat_id`),
  UNIQUE KEY `cat_name` (`cat_name`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

--
-- Table structure for table `location`
--

DROP TABLE IF EXISTS `location`;
CREATE TABLE `location` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `server_id` smallint(5) unsigned NOT NULL,
  `name` varchar(128) NOT NULL,
  PRIMARY KEY (`id`,`server_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

--
-- Table structure for table `server`
--

DROP TABLE IF EXISTS `server`;
CREATE TABLE `server` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `last_contact` int(10) unsigned NOT NULL,
  `version` varchar(32) NOT NULL,
  `hostname` varchar(64) NOT NULL,
  `information` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hostname` (`hostname`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
--
-- Table structure for table `signature`
--

DROP TABLE IF EXISTS `signature`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `signature` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rule_id` mediumint(8) unsigned NOT NULL,
  `level` tinyint(3) unsigned DEFAULT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rule_id` (`rule_id`),
  KEY `level` (`level`),
  KEY `rule_id_2` (`rule_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

--
-- Table structure for table `signature_category_mapping`
--

DROP TABLE IF EXISTS `signature_category_mapping`;
CREATE TABLE `signature_category_mapping` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rule_id` mediumint(8) unsigned NOT NULL,
  `cat_id` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`id`,`rule_id`,`cat_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


