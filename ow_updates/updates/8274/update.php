<?php

$db = Updater::getDbo();
$logger = Updater::getLogger();
$tblPrefix = OW_DB_PREFIX;

$queryList = array(
    "ALTER TABLE `{$tblPrefix}base_billing_gateway` ADD `hidden` TINYINT(1) NOT NULL DEFAULT '0' AFTER `dynamic`;",
    "ALTER TABLE `{$tblPrefix}base_attachment` CHANGE `bundle` `bundle` VARCHAR( 128 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ",
    "ALTER TABLE `{$tblPrefix}base_attachment` CHANGE `bundle` `bundle` VARCHAR( 128 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ",
    "ALTER TABLE `{$tblPrefix}base_attachment` CHANGE `origFileName` `origFileName` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ",
    "ALTER TABLE `{$tblPrefix}base_attachment` CHANGE `pluginKey` `pluginKey` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ",
    "ALTER TABLE `{$tblPrefix}base_question_to_account_type` CHANGE `accountType` `accountType` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ",
    "ALTER TABLE `{$tblPrefix}base_question_to_account_type` CHANGE `questionName` `questionName` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ",
    "ALTER TABLE `{$tblPrefix}base_billing_sale` ADD INDEX (`entityKey`) ",
    "ALTER TABLE `{$tblPrefix}base_billing_sale` ADD INDEX (`entityId`) ",
    "ALTER TABLE `{$tblPrefix}base_billing_sale` ADD INDEX (`userId`) ",
    "ALTER TABLE `{$tblPrefix}base_billing_sale` ADD INDEX (`status`) ",
);

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
    Updater::getLanguageService()->deleteLangKey('admin', 'pages_page_field_content_desc');
}
catch ( Exception $e )
{
    $logger->addEntry(json_encode($e));
}

UPDATE_LanguageService::getInstance()->importPrefixFromZip(dirname(__FILE__) . DS . 'langs.zip', 'base');
