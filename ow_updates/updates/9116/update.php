<?php

/**
 * EXHIBIT A. Common Public Attribution License Version 1.0
 * The contents of this file are subject to the Common Public Attribution License Version 1.0 (the “License”);
 * you may not use this file except in compliance with the License. You may obtain a copy of the License at
 * http://www.oxwall.org/license. The License is based on the Mozilla Public License Version 1.1
 * but Sections 14 and 15 have been added to cover use of software over a computer network and provide for
 * limited attribution for the Original Developer. In addition, Exhibit A has been modified to be consistent
 * with Exhibit B. Software distributed under the License is distributed on an “AS IS” basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for the specific language
 * governing rights and limitations under the License. The Original Code is Oxwall software.
 * The Initial Developer of the Original Code is Oxwall Foundation (http://www.oxwall.org/foundation).
 * All portions of the code written by Oxwall Foundation are Copyright (c) 2011. All Rights Reserved.

 * EXHIBIT B. Attribution Information
 * Attribution Copyright Notice: Copyright 2011 Oxwall Foundation. All rights reserved.
 * Attribution Phrase (not exceeding 10 words): Powered by Oxwall community software
 * Attribution URL: http://www.oxwall.org/
 * Graphic Image as provided in the Covered Code.
 * Display of Attribution Information is required in Larger Works which are defined in the CPAL as a work
 * which combines Covered Code or portions thereof with code not governed by the terms of the CPAL.
 */

Updater::getLanguageService()->deleteLangKey('admin', 'sidebar_menu_pages');
Updater::getLanguageService()->deleteLangKey('admin', 'sidebar_menu_item_pages_manage');
Updater::getLanguageService()->deleteLangKey('admin', 'splash_screen_page_heading');
Updater::getLanguageService()->deleteLangKey('admin', 'splash_screen_page_title');
Updater::getLanguageService()->deleteLangKey('admin', 'splash_screen_submit_success_message');
Updater::getLanguageService()->deleteLangKey('admin', 'permissions_index_save');
Updater::getLanguageService()->deleteLangKey('admin', 'permission_global_privacy_settings_success_message');
Updater::getLanguageService()->deleteLangKey('admin', 'question_settings_updated');
Updater::getLanguageService()->deleteLangKey('admin', 'admin_dashboard');
Updater::getLanguageService()->deleteLangKey('admin', 'heading_user_roles');
Updater::getLanguageService()->deleteLangKey('admin', 'heading_main_settings');
Updater::getLanguageService()->deleteLangKey('admin', 'heading_user_input_settings');
Updater::getLanguageService()->deleteLangKey('admin', 'heading_page_settings');
Updater::getLanguageService()->deleteLangKey('admin', 'heading_mail_settings');
Updater::getLanguageService()->deleteLangKey('admin', 'themes_choose_page_title');
Updater::getLanguageService()->deleteLangKey('admin', 'themes_settings_page_title');
Updater::getLanguageService()->deleteLangKey('admin', 'pages_page_heading');
Updater::getLanguageService()->deleteLangKey('admin', 'maintenance_page_heading');
Updater::getLanguageService()->deleteLangKey('admin', 'maintenance_section_label');
Updater::getLanguageService()->deleteLangKey('admin', 'splash_screen_section_label');
Updater::getLanguageService()->deleteLangKey('admin', 'widgets_admin_profile_heading');
Updater::getLanguageService()->deleteLangKey('admin', 'page_title_manage_plugins');
Updater::getLanguageService()->deleteLangKey('admin', 'manage_plugins_available_box_cap_label');
Updater::getLanguageService()->deleteLangKey('admin', 'manage_plugins_add_box_cap_label');
Updater::getLanguageService()->deleteLangKey('admin', 'heading_mobile_settings');
Updater::getLanguageService()->deleteLangKey('admin', 'mobile_settings_tabe_heading');

UPDATE_LanguageService::getInstance()->importPrefixFromZip(dirname(__FILE__) . DS . 'langs.zip', 'admin');

$db = Updater::getDbo();
$logger = Updater::getLogger();
$tblPrefix = OW_DB_PREFIX;

$queryList = array();
$queryList[] = 'UPDATE `' . $tblPrefix . 'base_menu_item` SET `order` = `order` + 1 WHERE `type` = "admin_users" AND `order` > 1';
$queryList[] = 'UPDATE `' . $tblPrefix . 'base_menu_item` SET `type`  = "admin_users", `order` = 2 WHERE `key` = "sidebar_menu_item_permission_moders"';
$queryList[] = 'UPDATE `' . $tblPrefix . 'base_menu_item` SET `order` = `order` + 1 WHERE `key` = "sidebar_menu_item_plugin_mass_mailing"';
$queryList[] = 'UPDATE `' . $tblPrefix . 'base_menu_item` SET `order` = `order` - 1 WHERE `key` = "sidebar_menu_item_restricted_usernames"';
$queryList[] = 'UPDATE `' . $tblPrefix . 'base_menu_item` SET `key`   = "sidebar_menu_item_general" WHERE `key` = "sidebar_menu_item_main_settings"';
$queryList[] = 'INSERT INTO `' . $tblPrefix . 'base_menu_item` SET `prefix` = "admin", `key` = "sidebar_menu_item_content_settings", `type` = "admin_settings", `order` = 3, `visibleFor` = 3, `newWindow` = NULL, `routePath` = "admin_settings_user_input"';
$queryList[] = 'INSERT INTO `' . $tblPrefix . 'base_menu_item` SET `prefix` = "admin", `key` = "sidebar_menu_item_page_settings", `type` = "admin_settings", `order` = 4, `visibleFor` = 3, `newWindow` = NULL, `routePath` = "admin_settings_page"';
$queryList[] = 'UPDATE `' . $tblPrefix . 'base_menu_item` SET `order` = 5 WHERE `key` = "sidebar_menu_item_settings_language"';
$queryList[] = 'DELETE FROM `' . $tblPrefix . 'base_menu_item` WHERE `key` = "sidebar_menu_themes_add"';
$queryList[] = 'UPDATE `' . $tblPrefix . 'base_menu_item` SET `order` = 1, `key` = "sidebar_menu_item_themes" WHERE `key` = "sidebar_menu_item_theme_choose"';
$queryList[] = 'UPDATE `' . $tblPrefix . 'base_menu_item` SET `order` = 2, `key` = "sidebar_menu_item_themes_customize" WHERE `key` = "sidebar_menu_item_theme_edit"';
$queryList[] = 'UPDATE `' . $tblPrefix . 'base_menu_item` SET `key` = "sidebar_menu_item_manage_pages" `order` = 1 WHERE `key` = "sidebar_menu_item_pages_manage"';
$queryList[] = 'UPDATE `' . $tblPrefix . 'base_menu_item` SET `key` = "sidebar_menu_item_special_pages", `order` = 2 WHERE `key` = "sidebar_menu_item_maintenance"';
$queryList[] = 'DELETE FROM `' . $tblPrefix . 'base_menu_item` WHERE `key` = "sidebar_menu_item_splash_screen"';
$queryList[] = 'UPDATE `' . $tblPrefix . 'base_menu_item` SET `order` = 3 WHERE `key` = "sidebar_menu_item_user_profile"';
$queryList[] = 'UPDATE `' . $tblPrefix . 'base_menu_item` SET `order` = 4 WHERE `key` = "sidebar_menu_item_user_dashboard"';
$queryList[] = 'DELETE FROM `' . $tblPrefix . 'base_menu_item` WHERE `key` = "sidebar_menu_item_permission"';
$queryList[] = 'DELETE FROM `' . $tblPrefix . 'base_menu_item` WHERE `key` = "sidebar_menu_item_permission_role"';
$queryList[] = 'UPDATE `' . $tblPrefix . 'base_menu_item` SET `key` = "sidebar_menu_main" WHERE `key` = "sidebar_menu_dashboard"';
$queryList[] = 'INSERT INTO `' . $tblPrefix . 'base_menu_item` SET `prefix` = "admin", `key` = "sidebar_menu_item_smtp_settings", `type` = "admin_settings", `order` = 6, `visibleFor` = 3, `newWindow` = NULL, `routePath` = "admin_settings_mail"';

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
