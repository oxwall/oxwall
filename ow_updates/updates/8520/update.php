<?php

$db = Updater::getDbo();
$logger = Updater::getLogger();
$tblPrefix = OW_DB_PREFIX;

$queryList = array(
    "ALTER TABLE `{$tblPrefix}base_question_section` ADD `isDeletable` INT NOT NULL DEFAULT '1';",
    "UPDATE `{$tblPrefix}base_question_section` SET `isDeletable` = '0' WHERE `name` IN ( 'about_my_match', 'f90cde5913235d172603cc4e7b9726e3' ); ",
    " ALTER TABLE  `{$tblPrefix}base_user_suspend` ADD  `message` TEXT NOT NULL  ",
    " ALTER TABLE `{$tblPrefix}base_avatar` ADD `status` VARCHAR(32) NOT NULL DEFAULT 'active' "
);

$queryList[] = "DROP TABLE `{$tblPrefix}base_flag`;";
$queryList[] = "CREATE TABLE IF NOT EXISTS `{$tblPrefix}base_flag` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entityType` varchar(100) NOT NULL,
  `entityId` int(11) DEFAULT NULL,
  `userId` int(11) DEFAULT NULL,
  `reason` varchar(50) DEFAULT NULL,
  `timeStamp` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `entityType` (`entityType`,`entityId`,`userId`),
  KEY `timeStamp` (`timeStamp`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

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

$keysToDelete = array(
    "admin+manage_plugins_add_size_error_message",
    "base+auth_group_label",
    'base+welcome_letter_template_html',
    'base+welcome_letter_template_text'
);


foreach ( $keysToDelete as $key )
{
    $keyArr = explode("+", $key);

    try
    {
        Updater::getLanguageService()->deleteLangKey($keyArr[0], $keyArr[1]);
    }
    catch ( Exception $e )
    {
        $logger->addEntry(json_encode($e));
    }
}

//if ( defined('SOFT_PACK') )
//{
//    $queryList = array(
//        " UPDATE  `{$tblPrefix}base_component_setting` SET `value` = '<p>Welcome! Start with the following steps:
//    </p>
//    <li><a href=\"profile/edit\">Complete your profile</a></li>
//    <li><a href=\"users/search\">Look who\'s in</a></li>
//    </ul>'  where `componentPlaceUniqName` = 'admin-4b543f4714cdc' AND name = 'content' ;"
//    );
//
//    foreach ( $queryList as $query )
//    {
//        try
//        {
//            $db->query($query);
//        }
//        catch ( Exception $e )
//        {
//            $logger->addEntry(json_encode($e));
//        }
//    }
//}
// Add 'tmp' directory in avatars userfiles
@mkdir(OW_DIR_PLUGIN_USERFILES . 'base' . DS . 'avatars' . DS . 'tmp');
@chmod(OW_DIR_PLUGIN_USERFILES . 'base' . DS . 'avatars' . DS . 'tmp', 0777);

UPDATE_LanguageService::getInstance()->importPrefixFromZip(dirname(__FILE__) . DS . 'langs.zip', 'base');


$widgetService = Updater::getWidgetService();

try
{
    $widget = $widgetService->addWidget('BASE_CMP_ModerationToolsWidget', false);
    $widgetPlace = $widgetService->addWidgetToPlace($widget, BOL_ComponentService::PLACE_DASHBOARD);
    $widgetService->addWidgetToPosition($widgetPlace, BOL_ComponentService::SECTION_RIGHT, 0);
}
catch ( Exception $e )
{
    Updater::getLogger()->addEntry(json_encode($e));
}


$widgetService->deleteWidget('admin-4b543f4714cdc');
$widgetService->deleteWidgetPlace('admin-4b543f4714cdc');

try
{
    $widget = $widgetService->addWidget('BASE_CMP_WelcomeWidget', false);
    $placeWidget = $widgetService->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_DASHBOARD);
    $widgetService->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_RIGHT, 1);
}
catch ( Exception $e )
{
    Updater::getLogger()->addEntry(json_encode($e));
}

try
{
    if ( !OW::getConfig()->configExists('base', 'avatar_max_upload_size') )
    {
        OW::getConfig()->addConfig('base', 'avatar_max_upload_size', true, 'Enable file attachments');
    }
}
catch ( Exception $e )
{
    Updater::getLogger()->addEntry(json_encode($e));
}

$action = Updater::getAuthorizationService()->findAction("base", "delete_comment_by_content_owner");

if ( $action )
{
    Updater::getAuthorizationService()->deleteAction($action->getId());
}
