<?php

$db = Updater::getDbo();
$logger = Updater::getLogger();
$tblPrefix = OW_DB_PREFIX;

$queryList = array();
$queryList[] = "CREATE TABLE IF NOT EXISTS `{$tblPrefix}base_search_entity` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `entityType` varchar(50) NOT NULL,
    `entityId` int(10) unsigned NOT NULL,
    `text` text NOT NULL,
    `status` varchar(20) NOT NULL DEFAULT 'active',
    `timeStamp` int(10) unsigned NOT NULL,
    `activated` tinyint(1) unsigned NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`),
    KEY `entity` (`entityType`,`entityId`),
    KEY `status` (`status`, `activated`, `timeStamp`),
    FULLTEXT KEY `entityText` (`text`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

$queryList[] = "CREATE TABLE IF NOT EXISTS `{$tblPrefix}base_search_entity_tag` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `entityTag` varchar(50) NOT NULL,
    `searchEntityId` int(10) unsigned NOT NULL,
    PRIMARY KEY (`id`),
    KEY `searchEntityId` (`searchEntityId`),
    KEY `entityTag` (`entityTag`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

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
