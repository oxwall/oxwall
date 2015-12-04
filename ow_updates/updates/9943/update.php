<?php

$dbPrefix = OW_DB_PREFIX;
$sql = array();

$sql[] =
    "CREATE TABLE IF NOT EXISTS `{$dbPrefix}base_geolocationdata_ipv4` (
  `ipFrom` varchar(200) NOT NULL,
  `IpTo` varchar(200) DEFAULT NULL,
  `cc2` varchar(200) NOT NULL,
  `cc3` varchar(200) DEFAULT NULL,
  `name` varchar(200) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";


$sql[] = "CREATE TABLE IF NOT EXISTS `{$dbPrefix}base_geolocation_ip_to_country` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cc2` char(2) NOT NULL,
  `cc3` char(3) NOT NULL,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";


foreach ( $sql as $q )
{
    try
    {
        OW::getDbo()->query( $q );
    }
    catch (Exception $ex)
    {
        // Log
    }
}

