<?php

$tblPrefix = OW_DB_PREFIX;
$db = Updater::getDbo();
$logger = Updater::getLogger();

$queryList = array();
$queryList[] = "ALTER TABLE  `{$tblPrefix}base_mail` ADD  `sent` BOOLEAN NOT NULL DEFAULT  '0' ";

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

UPDATE_LanguageService::getInstance()->importPrefixFromZip(dirname(__FILE__) . DS . 'langs.zip', 'base');

if ( OW::getConfig()->configExists("base", "install_complete") )
{
    OW::getConfig()->saveConfig("base", "install_complete", 1);
}
else
{
    OW::getConfig()->addConfig("base", "install_complete", 1);
}