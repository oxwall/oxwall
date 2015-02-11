<?php

$tblPrefix = OW_DB_PREFIX;

$db = Updater::getDbo();

$queryList = array(
    "INSERT INTO `{$tblPrefix}base_document` (`key`, `class`, `action`, `uri`, `isStatic`) VALUES (39, 'page-119658', NULL, NULL, 'terms-of-use', 1)"
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

