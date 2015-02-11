<?php

$tblPrefix = OW_DB_PREFIX;

$db = Updater::getDbo();

$simpleQueryList = array(
    "CREATE TABLE IF NOT EXISTS `{$tblPrefix}base_question_config` (
        id INT(11) NOT NULL AUTO_INCREMENT,
        questionPresentation ENUM('text','textarea','select','date','location','checkbox','multicheckbox','radio','url','password','age','birthdate') NOT NULL DEFAULT 'text',
        name VARCHAR(255) NOT NULL,
        description VARCHAR(1024) DEFAULT NULL,
        presentationClass VARCHAR(255) DEFAULT NULL,
        PRIMARY KEY (id)
    )
    ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci",
    "CREATE TABLE IF NOT EXISTS `{$tblPrefix}base_restricted_usernames` (
        id INT(11) NOT NULL AUTO_INCREMENT,
        username VARCHAR(32) NOT NULL,
        PRIMARY KEY (id)
    )
    ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci",
    "ALTER TABLE `{$tblPrefix}activity_action` CHANGE COLUMN status status ENUM('active','inactive') NOT NULL",
    "ALTER TABLE `{$tblPrefix}base_log` CHANGE COLUMN type type VARCHAR(100) NOT NULL, CHANGE COLUMN `key` `key` VARCHAR(100) NOT NULL",
    "ALTER TABLE `{$tblPrefix}base_plugin` ADD COLUMN installRoute VARCHAR(255) DEFAULT NULL AFTER adminSettingsRoute",
    "ALTER TABLE `{$tblPrefix}base_question` ADD COLUMN custom VARCHAR(2048) DEFAULT NULL AFTER sortOrder",
    "DROP TABLE IF EXISTS `{$tblPrefix}base_banner`",
    "DROP TABLE IF EXISTS `{$tblPrefix}base_banner_location`",
    "DROP TABLE IF EXISTS `{$tblPrefix}base_banner_position`",
    "UPDATE `{$tblPrefix}base_authorization_group` SET `moderated`='1' WHERE `name`='admin'",
    "DELETE FROM `{$tblPrefix}base_menu_item` WHERE `prefix` = 'admin' AND `key` = 'sidebar_menu_item_ads'",
    "INSERT INTO `{$tblPrefix}base_config` SET
        `key`='base',
        `name`='check_mupdates_ts',
        `value`='0',
        `description`='Last manual updates check timestamp.'",
    "INSERT INTO `{$tblPrefix}base_menu_item` SET
        `prefix`='admin',
        `key`='sidebar_menu_item_dashboard_finance',
        `documentKey`='',
        `type`='admin',
        `order`=2,
        `routePath`='admin_finance',
        `newWindow`='0',
        `visibleFor`='3'",
    "INSERT INTO `{$tblPrefix}base_menu_item` SET
        `prefix`='admin',
        `key`='sidebar_menu_item_restricted_usernames',
        `documentKey`='',
        `type`='admin_users',
        `order`=6,
        `routePath`='admin_restrictedusernames',
        `newWindow`='0',
        `visibleFor`='3'",
    "UPDATE `{$tblPrefix}base_plugin` SET `build` = 1"
);

$sqlErrors = array();

foreach ( $simpleQueryList as $query )
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

UPDATE_LanguageService::getInstance()->importPrefixFromZip(dirname(__FILE__) . DS . 'admin_langs.zip', 'update');
UPDATE_LanguageService::getInstance()->importPrefixFromZip(dirname(__FILE__) . DS . 'base_langs.zip', 'update');

$dirArray = array(
    OW_DIR_PLUGINFILES . 'admin' . DS . 'languages' . DS,
    OW_DIR_PLUGINFILES . 'admin' . DS . 'languages' . DS . 'export' . DS,
    OW_DIR_PLUGINFILES . 'admin' . DS . 'languages' . DS . 'import' . DS,
    OW_DIR_PLUGINFILES . 'admin' . DS . 'languages' . DS . 'tmp' . DS,
    OW_DIR_PLUGINFILES . 'ow' . DS,
    OW_DIR_PLUGINFILES . 'plugin' . DS
);

foreach ( $dirArray as $dir )
{
    if ( !file_exists($dir) )
    {
        mkdir($dir);
        chmod($dir, 0777);
    }
}

try
{
    $widgetService = UPDATE_WidgetService::getInstance();
    $widgetService->deleteWidget('BASE_CMP_SidebarAds');

    $newWidget = $widgetService->addWidget('BASE_CMP_MyAvatarWidget');
    $newWidgetPlace = $widgetService->addWidgetToPlace($newWidget, UPDATE_WidgetService::PLACE_INDEX);
    $widgetService->addWidgetToPosition($newWidgetPlace, UPDATE_WidgetService::SECTION_SIDEBAR);
}
catch ( Exception $e )
{
    printVar($e);
}

if ( !empty($sqlErrors) )
{
    printVar($sqlErrors);
}
