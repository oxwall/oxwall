<?php

$tblPrefix = OW_DB_PREFIX;

$db = Updater::getDbo();

$queryList = array(
    "INSERT INTO `{$tblPrefix}base_config` (`key`, `name`, `value`, `description`) VALUES ('admin', 'mass_mailing_timestamp', '0', NULL)",
    "ALTER TABLE `{$tblPrefix}base_plugin` DROP COLUMN installRoute",
    "ALTER TABLE `{$tblPrefix}base_plugin` CHANGE COLUMN uninstallRoute uninstallRoute VARCHAR(255) DEFAULT NULL AFTER adminSettingsRoute",
    "ALTER TABLE `{$tblPrefix}base_plugin` CHANGE COLUMN adsEnabled adsEnabled TINYINT(1) NOT NULL DEFAULT 0 AFTER uninstallRoute",
    "ALTER TABLE `{$tblPrefix}base_plugin` CHANGE COLUMN build build INT(11) NOT NULL DEFAULT 0 AFTER adsEnabled",
    "ALTER TABLE `{$tblPrefix}base_plugin` CHANGE COLUMN `update` `update` TINYINT(1) NOT NULL DEFAULT 0 AFTER build",
    "ALTER TABLE `{$tblPrefix}base_plugin` CHANGE COLUMN licenseKey licenseKey VARCHAR(255) DEFAULT NULL AFTER `update`",
    "INSERT INTO  `{$tblPrefix}base_config` (`key` ,`name` ,`value` ,`description`) VALUES ('base',  'dev_mode',  '0', NULL)"
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

UPDATE_LanguageService::getInstance()->importPrefixFromZip(dirname(__FILE__) . DS . 'langs.zip', 'base');

if ( !empty($sqlErrors) )
{
    printVar($sqlErrors);
}