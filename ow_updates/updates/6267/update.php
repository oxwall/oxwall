<?php

$tblPrefix = OW_DB_PREFIX;
$db = Updater::getDbo();
$logger = Updater::getLogger();

$queryList = array();

$queryList[] = "ALTER TABLE  `{$tblPrefix}base_component_setting` ADD  `type` VARCHAR( 20 ) NOT NULL DEFAULT  'string'";
$queryList[] = "ALTER TABLE  `{$tblPrefix}base_component_entity_setting` ADD  `type` VARCHAR( 20 ) NOT NULL DEFAULT  'string'";

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

try
{
    Updater::getLanguageService()->deleteLangKey('base','questions_question_presentation_select_label');
    Updater::getLanguageService()->deleteLangKey('base','questions_question_presentation_radio_label');
    Updater::getLanguageService()->deleteLangKey('base','questions_question_presentation_age_label');
    Updater::getLanguageService()->deleteLangKey('base','questions_question_presentation_birthdate_label');
    // Updater::getLanguageService()->deleteLangKey('base','local_page_meta_tags_page-119658');

}
catch ( Exception $e )
{
    $logger->addEntry(json_encode($e));
}

if( !UPDATE_ConfigService::getInstance()->configExists('base', 'user_invites_limit') )
{
    UPDATE_ConfigService::getInstance()->addConfig('base', 'user_invites_limit', 50);
}

Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__) . DS . 'langs.zip', 'base');