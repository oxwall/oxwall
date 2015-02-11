<?php

$tblPrefix = OW_DB_PREFIX;

$db = Updater::getDbo();

$queryList = array(
    "ALTER TABLE  `{$tblPrefix}base_comment` ADD  `attachment` TEXT NULL",
    "ALTER TABLE  `{$tblPrefix}base_question` CHANGE  `type`  `type` ENUM(  'text',  'select',  'datetime',  'boolean',  'multiselect' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  'text'"
);

$sqlErrors = array();

@mkdir(OW_DIR_PLUGIN_USERFILES.'base'.DS.'attachments');
@chmod(OW_DIR_PLUGIN_USERFILES.'base'.DS.'attachments', 0777);
@mkdir(OW_DIR_PLUGIN_USERFILES.'base'.DS.'attachments'.DS.'temp');
@chmod(OW_DIR_PLUGIN_USERFILES.'base'.DS.'attachments'.DS.'temp', 0777);

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

//UPDATE_LanguageService::getInstance()->importPrefixFromZip(dirname(__FILE__) . DS . 'langs.zip', 'base');

if ( !empty($sqlErrors) )
{
    printVar($sqlErrors);
}

