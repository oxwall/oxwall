<?php

$tblPrefix = OW_DB_PREFIX;
$db = Updater::getDbo();

$queryList = array(
    "CREATE TABLE IF NOT EXISTS `{$tblPrefix}base_attachment` (
     `id` int(11) NOT NULL AUTO_INCREMENT,
    `userId` int(11) NOT NULL,
    `addStamp` int(11) NOT NULL,
    `status` tinyint(1) NOT NULL DEFAULT '0',
    `fileName` varchar(100) DEFAULT NULL,
    PRIMARY KEY (`id`)
    ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci",
    "ALTER IGNOER TABLE  `{$tblPrefix}base_authorization_user_role` ADD UNIQUE  `user2role` (  `userId` ,  `roleId` ) "
);

foreach ( $queryList as $query )
{
    try
    {
        $db->query($query);
    }
    catch ( Exception $e )
    {
        if ( isset($logArray) )
        {
            $logArray[] = $e;
        }
        else
        {
            $errors[] = $e;
        }
    }
}

$widget = BOL_ComponentAdminService::getInstance()->addWidget('BASE_CMP_QuickLinksWidget', false);
$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_DASHBOARD);
BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_RIGHT );

/* code to move all custom css files to clouds */

if ( defined('OW_USE_AMAZON_S3_CLOUDFILES') && OW_USE_AMAZON_S3_CLOUDFILES || defined('OW_USE_CLOUDFILES') && OW_USE_CLOUDFILES )
{
    $storage = Updater::getStorage();

    $themesList = BOL_ThemeService::getInstance()->findAllThemes();

    /* @var $theme BOL_Theme */
    foreach ( $themesList as $theme )
    {
		if ( file_exists(OW_DIR_THEME_USERFILES . $theme->getCustomCssFileName()) && is_file(OW_DIR_THEME_USERFILES . $theme->getCustomCssFileName()) )
		{
        	$storage->copyFile(OW_DIR_THEME_USERFILES . $theme->getCustomCssFileName(), OW_DIR_THEME_USERFILES . $theme->getCustomCssFileName());
		}
    }
}

Updater::getLanguageService()->deleteLangKey('base','questions_question_presentation_select_label');
Updater::getLanguageService()->deleteLangKey('base','questions_question_presentation_radio_label');
Updater::getLanguageService()->deleteLangKey('base','questions_question_presentation_age_label');
Updater::getLanguageService()->deleteLangKey('base','questions_question_presentation_birthdate_label');

Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__) . DS . 'langs.zip', 'base');