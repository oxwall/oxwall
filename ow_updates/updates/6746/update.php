<?php

$tblPrefix = OW_DB_PREFIX;
$db = Updater::getDbo();
$logger = Updater::getLogger();

$queryList = array();

$queryList[] = "ALTER TABLE `{$tblPrefix}base_theme`
  ADD COLUMN `developerKey` VARCHAR(255) DEFAULT NULL AFTER `id`,
  ADD COLUMN `build` INT(11) NOT NULL DEFAULT 0 AFTER `developerKey`,
  ADD COLUMN `update` TINYINT(4) NOT NULL DEFAULT 0 AFTER `build`,
  ADD COLUMN `licenseKey` VARCHAR(255) DEFAULT NULL AFTER `update`;";

$queryList[] = "INSERT INTO `{$tblPrefix}base_place` (`name`, `editableByUser`) VALUES
    ('mobile.index', 0),
    ('mobile.dashboard', 0)";

$queryList[] = "ALTER TABLE  `{$tblPrefix}base_component_position` CHANGE  `section`  `section` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL";
$queryList[] = "ALTER TABLE  `{$tblPrefix}base_document` ADD  `isMobile` TINYINT NOT NULL DEFAULT  '0'";
$queryList[] = "ALTER TABLE  `{$tblPrefix}base_theme_content` CHANGE  `type`  `type` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL";

$queryList[] = "ALTER TABLE `{$tblPrefix}base_comment_entity` DROP INDEX `pluginKey_2`";
$queryList[] = "ALTER TABLE `{$tblPrefix}base_component` DROP INDEX `id`";
$queryList[] = "ALTER TABLE `{$tblPrefix}base_component_entity_setting` DROP INDEX `id`";
$queryList[] = "ALTER TABLE `{$tblPrefix}base_tag` DROP INDEX `id`";
$queryList[] = "ALTER TABLE `{$tblPrefix}base_theme_image` DROP INDEX `id`";
$queryList[] = "ALTER TABLE `{$tblPrefix}base_user_featured` DROP INDEX `id`";
$queryList[] = "ALTER TABLE `{$tblPrefix}base_theme_control` ADD  `mobile` BOOLEAN NOT NULL DEFAULT  '0'";
$queryList[] = "ALTER TABLE `{$tblPrefix}base_theme_control` CHANGE  `type`  `type` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  'text'";
$queryList[] = "ALTER TABLE `{$tblPrefix}base_theme` ADD  `mobileCustomCss` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER  `customCss`";

$queryList[] = "ALTER TABLE `{$tblPrefix}base_question` ADD `parent` VARCHAR( 255 )";

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

if ( !UPDATE_ConfigService::getInstance()->configExists('base', 'profile_question_edit_stamp') )
{
    UPDATE_ConfigService::getInstance()->addConfig('base', 'profile_question_edit_stamp', 0);
}


$navigation = OW::getNavigation();

try
{
    $navigation->addMenuItem(
        OW_Navigation::ADMIN_MOBILE,
        'mobile.admin.navigation',
        'mobile',
        'mobile_admin_navigation',
        OW_Navigation::VISIBLE_FOR_ALL);
}
catch ( Exception $e )
{
    $logger->addEntry(json_encode($e));
}

try
{
    $navigation->addMenuItem(
        OW_Navigation::ADMIN_MOBILE,
        'mobile.admin.pages.index',
        'mobile',
        'mobile_admin_pages_index',
        OW_Navigation::VISIBLE_FOR_ALL);
}
catch ( Exception $e )
{
    $logger->addEntry(json_encode($e));
}

try
{
    $navigation->addMenuItem(
        OW_Navigation::ADMIN_MOBILE,
        'mobile.admin.pages.dashboard',
        'mobile',
        'mobile_admin_pages_dashboard',
        OW_Navigation::VISIBLE_FOR_ALL);
}
catch ( Exception $e )
{
    $logger->addEntry(json_encode($e));
}

try
{
    $navigation->addMenuItem(
        OW_Navigation::MOBILE_HIDDEN,
        'base_member_dashboard',
        'mobile',
        'mobile_pages_dashboard',
        OW_Navigation::VISIBLE_FOR_MEMBER);
}
catch ( Exception $e )
{
    $logger->addEntry(json_encode($e));
}

try
{
    $navigation->addMenuItem(
        OW_Navigation::MOBILE_BOTTOM,
        'base.desktop_version',
        'base',
        'desktop_version_menu_item',
        OW_Navigation::VISIBLE_FOR_ALL);
}
catch ( Exception $e )
{
    $logger->addEntry(json_encode($e));
}

try
{
    $navigation->addMenuItem(
        BOL_MenuItemDao::VALUE_TYPE_HIDDEN,
        'base.mobile_version',
        'base',
        'mobile_version_menu_item',
        OW_Navigation::VISIBLE_FOR_ALL);
}
catch ( Exception $e )
{
    $logger->addEntry(json_encode($e));
}

try
{
    $navigation->addMenuItem(
        OW_Navigation::MOBILE_TOP,
        'base_index',
        'base',
        'index_menu_item',
        OW_Navigation::VISIBLE_FOR_ALL);
}
catch ( Exception $e )
{
    $logger->addEntry(json_encode($e));
}


$widgetService = BOL_MobileWidgetService::getInstance();

try
{
    $widget = $widgetService->addWidget("BASE_MCMP_CustomHtmlWidget", true);
    $widgetService->addWidgetToPlace($widget, BOL_MobileWidgetService::PLACE_MOBILE_DASHBOARD);
    $widgetService->addWidgetToPlace($widget, BOL_MobileWidgetService::PLACE_MOBILE_INDEX);

    $widget = $widgetService->addWidget("BASE_MCMP_RssWidget", true);
    $widgetService->addWidgetToPlace($widget, BOL_MobileWidgetService::PLACE_MOBILE_DASHBOARD);
    $widgetService->addWidgetToPlace($widget, BOL_MobileWidgetService::PLACE_MOBILE_INDEX);

    $widget = $widgetService->addWidget("BASE_MCMP_UserListWidget", false);
    $place = $widgetService->addWidgetToPlace($widget, BOL_MobileWidgetService::PLACE_MOBILE_INDEX);
    $widgetService->addWidgetToPosition($place, BOL_MobileWidgetService::SECTION_MOBILE_MAIN);
}
catch ( Exception $e )
{
    $logger->addEntry(json_encode($e));
}

// create dirs for wurfl
$wurflDir = OW_DIR_LIB . 'wurfl' . DS;
$resourcesDir = OW_DIR_PLUGINFILES . 'base' . DS . 'wurfl' . DS;
$persistenceDir = $resourcesDir . 'persistence' . DS;
$cacheDir = $resourcesDir . 'cache' . DS;

if ( !file_exists($wurflDir) )
{
    mkdir($wurflDir);
    chmod($wurflDir, 0777);
    mkdir($persistenceDir);
    chmod($persistenceDir, 0777);
    mkdir($cacheDir);
    chmod($cacheDir, 0777);
}

UPDATE_LanguageService::getInstance()->importPrefixFromZip(dirname(__FILE__) . DS . 'langs.zip', 'base');

if ( !UPDATE_ConfigService::getInstance()->configExists('base', 'users_on_page') )
{
    UPDATE_ConfigService::getInstance()->addConfig('base', 'users_on_page', 12);
}

/* try
  {
  $navigation->addMenuItem(
  OW_Navigation::ADMIN_MOBILE,
  'mobile.admin.pages.profile',
  'mobile',
  'mobile_admin_pages_profile',
  OW_Navigation::VISIBLE_FOR_ALL);
  }
  catch ( Exception $e )
  {
  $logger->addEntry(json_encode($e));
  } */
