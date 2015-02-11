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
$plugin = OW::getPluginManager()->getPlugin('admin');

OW::getRouter()->addRoute(new OW_Route('admin_default', 'admin', 'ADMIN_CTRL_Base', 'index'));
OW::getRouter()->addRoute(new OW_Route('admin_finance', 'admin/finance', 'ADMIN_CTRL_Finance', 'index'));
OW::getRouter()->addRoute(new OW_Route('admin_settings_language', 'admin/languages', 'ADMIN_CTRL_Languages', 'index'));

OW::getRouter()->addRoute(new OW_Route('admin_settings_language_mod', 'admin/languages/mod', 'ADMIN_CTRL_Languages', 'mod'));

OW::getRouter()->addRoute(new OW_Route('admin_developer_tools_language', 'admin/dev-tools/languages', 'ADMIN_CTRL_Languages', 'index'));
OW::getRouter()->addRoute(new OW_Route('admin_developer_tools_language_mod', 'admin/dev-tools/languages/mod', 'ADMIN_CTRL_Languages', 'mod'));

OW::getAutoloader()->addClass('ColorField', $plugin->getClassesDir() . 'form_fields.php');
OW::getAutoloader()->addClass('ADMIN_UserListParams', $plugin->getCmpDir() . 'user_list.php');

$router = OW::getRouter();

$router->addRoute(new OW_Route('admin_permissions', 'admin/permissions', 'ADMIN_CTRL_Permissions', 'index'));
$router->addRoute(new OW_Route('admin_permissions_roles', 'admin/permissions/roles', 'ADMIN_CTRL_Permissions', 'roles'));
$router->addRoute(new OW_Route('admin_permissions_moderators', 'admin/permissions/moderators', 'ADMIN_CTRL_Permissions', 'moderators'));
$router->addRoute(new OW_Route('admin_user_roles', 'admin/users/roles', 'ADMIN_CTRL_Users', 'roles'));
$router->addRoute(new OW_Route('admin_users_browse_membership_owners', 'admin/users/role/:roleId', 'ADMIN_CTRL_Users', 'role'));

$router->addRoute(new OW_Route('questions_index', 'admin/questions/account-types', 'ADMIN_CTRL_Questions', 'accountTypes'));
$router->addRoute(new OW_Route('questions_account_types', 'admin/questions/account-types', 'ADMIN_CTRL_Questions', 'accountTypes'));
$router->addRoute(new OW_Route('questions_properties', 'admin/questions/pages', 'ADMIN_CTRL_Questions', 'pages'));
$router->addRoute(new OW_Route('questions_settings', 'admin/questions/settings/', 'ADMIN_CTRL_Questions', 'settings'));

$router->addRoute(new OW_Route('admin_themes_edit', 'admin/theme', 'ADMIN_CTRL_Theme', 'settings'));
$router->addRoute(new OW_Route('admin_themes_choose', 'admin/themes', 'ADMIN_CTRL_Themes', 'chooseTheme'));
$router->addRoute(new OW_Route('admin_themes_add_new', 'admin/themes/add', 'ADMIN_CTRL_Themes', 'addTheme'));

$router->addRoute(new OW_Route('admin_pages_edit_external', 'admin/pages/edit-external/id/:id', 'ADMIN_CTRL_PagesEditExternal', 'index'));
$router->addRoute(new OW_Route('admin_pages_edit_local', 'admin/pages/edit-local/id/:id', 'ADMIN_CTRL_PagesEditLocal', 'index'));
$router->addRoute(new OW_Route('admin_pages_edit_plugin', 'admin/pages/edit-plugin/id/:id', 'ADMIN_CTRL_PagesEditPlugin', 'index'));

$router->addRoute(new OW_Route('admin_pages_add', 'admin/pages/add/type/:type', 'ADMIN_CTRL_Pages', 'index'));
$router->addRoute(new OW_Route('admin_pages_main', 'admin/pages/manage', 'ADMIN_CTRL_Pages', 'manage'));
$router->addRoute(new OW_Route('admin_pages_splash_screen', 'admin/pages/splash-screen', 'ADMIN_CTRL_Pages', 'splashScreen'));
$router->addRoute(new OW_Route('admin_pages_maintenance', 'admin/pages/maintenance', 'ADMIN_CTRL_Pages', 'maintenance'));

$router->addRoute(new OW_Route('admin_pages_user_dashboard', 'admin/user-dashboard', 'ADMIN_CTRL_Components', 'dashboard'));
$router->addRoute(new OW_Route('admin_pages_user_profile', 'admin/user-profile', 'ADMIN_CTRL_Components', 'profile'));

$router->addRoute(new OW_Route('admin_pages_user_settings', 'admin/user-settings', 'ADMIN_CTRL_UserSettings', 'index'));

$router->addRoute(new OW_Route('admin_plugins_installed', 'admin/plugins', 'ADMIN_CTRL_Plugins', 'index'));
$router->addRoute(new OW_Route('admin_plugins_available', 'admin/available', 'ADMIN_CTRL_Plugins', 'available'));
$router->addRoute(new OW_Route('admin_plugins_add', 'admin/plugins/add', 'ADMIN_CTRL_Plugins', 'add'));

$router->addRoute(new OW_Route('admin_delete_roles', 'admin/users/delete-roles', 'ADMIN_CTRL_Users', 'deleteRoles'));
$router->addRoute(new OW_Route('admin.roles.reorder', 'admin/users/ajax-reorder', 'ADMIN_CTRL_Users', 'ajaxReorder'));
$router->addRoute(new OW_Route('admin.roles.edit-role', 'admin/users/ajax-edit-role', 'ADMIN_CTRL_Users', 'ajaxEditRole'));
$router->addRoute(new OW_Route('admin_users_browse', 'admin/users/:list', 'ADMIN_CTRL_Users', 'index', array('list' => array('default' => 'recent'))));

$router->addRoute(new OW_Route('admin_settings_main', 'admin/settings', 'ADMIN_CTRL_Settings', 'index'));
$router->addRoute(new OW_Route('admin_settings_user', 'admin/settings/user', 'ADMIN_CTRL_Settings', 'user'));
$router->addRoute(new OW_Route('admin_settings_mail', 'admin/settings/email', 'ADMIN_CTRL_Settings', 'mail'));
$router->addRoute(new OW_Route('admin_settings_page', 'admin/settings/page', 'ADMIN_CTRL_Settings', 'page'));
$router->addRoute(new OW_Route('admin_settings_user_input', 'admin/settings/user-input', 'ADMIN_CTRL_Settings', 'userInput'));

$router->addRoute(new OW_Route('admin_massmailing', 'admin/mass-mailing', 'ADMIN_CTRL_MassMailing', 'index'));
$router->addRoute(new OW_Route('admin_restrictedusernames', 'admin/restricted-usernames', 'ADMIN_CTRL_RestrictedUsernames', 'index'));

$router->addRoute(new OW_Route('admin_languages_index', 'admin/languages', 'ADMIN_CTRL_Languages', 'index'));

$router->addRoute(new OW_Route('admin_theme_css', 'admin/theme/css', 'ADMIN_CTRL_Theme', 'css'));
$router->addRoute(new OW_Route('admin_theme_settings', 'admin/theme/settings', 'ADMIN_CTRL_Theme', 'settings'));
$router->addRoute(new OW_Route('admin_theme_graphics', 'admin/theme/graphics', 'ADMIN_CTRL_Theme', 'graphics'));
$router->addRoute(new OW_Route('admin_core_update_request', 'admin/update-core', 'ADMIN_CTRL_Plugins', 'coreUpdateRequest'));

// Mobile
$router->addRoute(new OW_Route('mobile.admin.navigation', 'admin/mobile/navigation', 'ADMIN_CTRL_MobileNavigation', 'index'));

$router->addRoute(new OW_Route('mobile.admin.pages.index', 'admin/mobile/pages/index', 'ADMIN_CTRL_MobileWidgetPanel', 'index'));
$router->addRoute(new OW_Route('mobile.admin.pages.dashboard', 'admin/mobile/pages/dashboard', 'ADMIN_CTRL_MobileWidgetPanel', 'dashboard'));
$router->addRoute(new OW_Route('mobile.admin.pages.profile', 'admin/mobile/pages/profile', 'ADMIN_CTRL_MobileWidgetPanel', 'profile'));
$router->addRoute(new OW_Route('mobile.admin_settings', 'admin/mobile/settings', 'ADMIN_CTRL_MobileSettings', 'index'));

function admin_on_application_finalize( OW_Event $event )
{
    OW::getLanguage()->addKeyForJs('admin', 'edit_language');
}
OW::getEventManager()->bind(OW_EventManager::ON_FINALIZE, 'admin_on_application_finalize');

function admin_add_auth_labels( BASE_CLASS_EventCollector $event )
{
    $language = OW::getLanguage();
    $event->add(
        array(
            'admin' => array(
                'label' => $language->text('admin', 'auth_group_label'),
                'actions' => array()
            )
        )
    );
}
OW::getEventManager()->bind('admin.add_auth_labels', 'admin_add_auth_labels');

$handler = new ADMIN_CLASS_EventHandler();
$handler->init();