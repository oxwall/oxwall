<?php

$tblPrefix = OW_DB_PREFIX;
$db = Updater::getDbo();
$logger = Updater::getLogger();

$queryList = array(
    "CREATE TABLE IF NOT EXISTS `{$tblPrefix}base_cache` (
  `id` int(11) NOT NULL auto_increment,
  `key` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `expireTimestamp` int(11) NOT NULL,
  `instantLoad` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `key_index` (`key`),
  KEY `expire_index` (`expireTimestamp`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1",

    "CREATE TABLE IF NOT EXISTS `{$tblPrefix}base_cache_tag` (
  `id` int(11) NOT NULL auto_increment,
  `tag` varchar(255) NOT NULL,
  `cacheId` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `tag_index` (`tag`),
  KEY `cacheId_index` (`cacheId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1",
"ALTER IGNORE TABLE  `{$tblPrefix}base_authorization_user_role` ADD UNIQUE  `user2role` (  `userId` ,  `roleId` ) ",

"ALTER TABLE  `{$tblPrefix}base_user` CHANGE  `joinIp`  `joinIp` INT( 11 ) UNSIGNED NOT NULL",
"INSERT IGNORE INTO  `{$tblPrefix}base_config` (`key` ,`name` ,`value` ,`description`) VALUES ('base',  'master_page_theme_info',  '[]', NULL)"
);

$queryList[] = "CREATE TABLE IF NOT EXISTS `{$tblPrefix}base_invitation` (
  `id` int(11) NOT NULL auto_increment,
  `entityType` varchar(255) NOT NULL,
  `entityId` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `userId` int(11) NOT NULL,
  `pluginKey` varchar(255) NOT NULL,
  `timeStamp` int(11) NOT NULL,
  `viewed` int(11) NOT NULL,
  `sent` tinyint(4) NOT NULL default '0',
  `active` tinyint(4) NOT NULL default '1',
  `data` text,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `entityType` (`entityType`,`entityId`,`userId`),
  KEY `timeStamp` (`timeStamp`),
  KEY `userId` (`userId`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

$queryList[] = " DELETE FROM  `{$tblPrefix}base_question` WHERE name = 'ab9fc810a1938e599b7d084efea97d91' LIMIT 1 ";

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

Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__) . DS . 'langs.zip', 'base');

/* code to move custom default avatars to clouds */

if ( defined('OW_USE_AMAZON_S3_CLOUDFILES') && OW_USE_AMAZON_S3_CLOUDFILES || defined('OW_USE_CLOUDFILES') && OW_USE_CLOUDFILES )
{
    $storage = Updater::getStorage();

    $conf = json_decode(Updater::getConfigService()->getValue('base', 'default_avatar'), true);
    $dir = OW_DIR_USERFILES . 'plugins' . DS . 'base' . DS . 'avatars'. DS;

    if ( !empty($conf[1]) )
    {
        $path = $dir . $conf[1];
        if ( file_exists($path) )
        {
            $storage->copyFile($path, $path);
        }
    }

    if ( !empty($conf[2]) )
    {
        $path = $dir . $conf[2];
        if ( file_exists($path) )
        {
            $storage->copyFile($path, $path);
        }
    }
}
