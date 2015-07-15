<?php

$db = Updater::getDbo();
$logger = Updater::getLogger();
$tblPrefix = OW_DB_PREFIX;

$queryList = array();

$queryList[] = "CREATE TABLE IF NOT EXISTS `{$tblPrefix}file_temporary` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL,
  `userId` int(11) NOT NULL,
  `addDatetime` int(11) NOT NULL,
  `order` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";

$queryList[] = "CREATE TABLE IF NOT EXISTS `{$tblPrefix}base_file` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `addDatetime` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `order` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";

$queryList[] = "ALTER TABLE `{$tblPrefix}base_theme_image`
  ADD `addDatetime` INT NULL ,
  ADD `description` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL ;";

foreach ( $queryList as $query )
{
    try
    {
        $db->query($query);
    }
    catch ( Exception $e )
    {
        $logger->addEntry(json_encode($e));
    }
}
