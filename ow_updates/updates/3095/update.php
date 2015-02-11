<?php

$tblPrefix = OW_DB_PREFIX;

$db = Updater::getDbo();

$queryList = array(
	"ALTER TABLE  `{$tblPrefix}base_comment_entity` ADD  `pluginKey` VARCHAR( 100 ) NOT NULL AFTER  `entityId`",
    "ALTER TABLE  `{$tblPrefix}base_comment_entity` ADD INDEX (  `pluginKey` )",
    "ALTER TABLE `{$tblPrefix}base_plugin`  DROP COLUMN `adsEnabled`",
);

$sqlErrors = array();

foreach ( $queryList as $query )
{
    try
    {
        $db->query($query);
    }
    catch ( Exception $e )
    {
        $sqlErrors[] = $e;
    }
}

$config = OW::getConfig();

if ( !$config->configExists('base', 'default_avatar') )
{
    $config->addConfig('base', 'default_avatar', '', 'Default avatar');
}

UPDATE_LanguageService::getInstance()->importPrefixFromZip(dirname(__FILE__) . DS . 'langs.zip', 'base');

if ( !empty($sqlErrors) )
{
    //printVar($sqlErrors);
}
