-- Structure de la table `repairs`
-- Table pour Maintenance.php

CREATE TABLE IF NOT EXISTS `repairs` (
	`ID` bigint(20) unsigned NOT NULL auto_increment,
  	`DEVICEID` varchar(255) NOT NULL default '',
      	`NAME` varchar(255) default NULL,
        `REQDATE` datetime default NULL,
	`REQDESC` text,
	`ACTIONDATE` datetime default NULL,
	`ACTIONDESC` text,
	`WARANTY` varchar(255) default NULL,
	`ACCOUNT` varchar(255) default NULL,
	`PRICE` varchar(255) default NULL,
	`COMMENTS` text,
	`STATUT` int(2) NOT NULL default '0',
	`PRIORITE` varchar(50) NOT NULL default '',
	`ADMIN` varchar(50) NOT NULL default '',
	 PRIMARY KEY  (`ID`),
	 KEY `IDEVICEID` (`DEVICEID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=11 ;
