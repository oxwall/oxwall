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

// Menu

OW::getNavigation()->addMenuItem(BOL_NavigationService::MENU_TYPE_ADMIN, "admin_default", "admin", "sidebar_menu_main", OW_Navigation::VISIBLE_FOR_ALL);
OW::getNavigation()->addMenuItem(BOL_NavigationService::MENU_TYPE_ADMIN, "admin_finance", "admin", "sidebar_menu_item_dashboard_finance", OW_Navigation::VISIBLE_FOR_ALL);

OW::getNavigation()->addMenuItem(BOL_NavigationService::MENU_TYPE_USERS, "admin_users_browse", "admin", "sidebar_menu_item_users", OW_Navigation::VISIBLE_FOR_ALL);
OW::getNavigation()->addMenuItem(BOL_NavigationService::MENU_TYPE_USERS, "admin_permissions_moderators", "admin", "sidebar_menu_item_permission_moders", OW_Navigation::VISIBLE_FOR_MEMBER);
OW::getNavigation()->addMenuItem(BOL_NavigationService::MENU_TYPE_USERS, "admin_user_roles", "admin", "sidebar_menu_item_users_roles", OW_Navigation::VISIBLE_FOR_ALL);
OW::getNavigation()->addMenuItem(BOL_NavigationService::MENU_TYPE_USERS, "questions_index", "admin", "sidebar_menu_item_questions", OW_Navigation::VISIBLE_FOR_ALL);
OW::getNavigation()->addMenuItem(BOL_NavigationService::MENU_TYPE_USERS, "admin_restrictedusernames", "admin", "sidebar_menu_item_restricted_usernames", OW_Navigation::VISIBLE_FOR_ALL);
OW::getNavigation()->addMenuItem(BOL_NavigationService::MENU_TYPE_USERS, "admin_massmailing", "admin", "sidebar_menu_item_plugin_mass_mailing", OW_Navigation::VISIBLE_FOR_ALL);

OW::getNavigation()->addMenuItem(BOL_NavigationService::MENU_TYPE_SETTINGS, "admin_settings_main", "admin", "sidebar_menu_item_general", OW_Navigation::VISIBLE_FOR_ALL);
OW::getNavigation()->addMenuItem(BOL_NavigationService::MENU_TYPE_SETTINGS, "admin_settings_user", "admin", "sidebar_menu_item_user_settings", OW_Navigation::VISIBLE_FOR_ALL);
OW::getNavigation()->addMenuItem(BOL_NavigationService::MENU_TYPE_SETTINGS, "admin_settings_user_input", "admin", "sidebar_menu_item_content_settings", OW_Navigation::VISIBLE_FOR_ALL);
OW::getNavigation()->addMenuItem(BOL_NavigationService::MENU_TYPE_SETTINGS, "admin_settings_page", "admin", "sidebar_menu_item_page_settings", OW_Navigation::VISIBLE_FOR_ALL);
OW::getNavigation()->addMenuItem(BOL_NavigationService::MENU_TYPE_SETTINGS, "admin_settings_language", "admin", "sidebar_menu_item_settings_language", OW_Navigation::VISIBLE_FOR_ALL);
OW::getNavigation()->addMenuItem(BOL_NavigationService::MENU_TYPE_SETTINGS, "admin_settings_mail", "admin", "sidebar_menu_item_smtp_settings", OW_Navigation::VISIBLE_FOR_ALL);

OW::getNavigation()->addMenuItem(BOL_NavigationService::MENU_TYPE_APPEARANCE, "admin_themes_choose", "admin", "sidebar_menu_item_themes", OW_Navigation::VISIBLE_FOR_ALL);
OW::getNavigation()->addMenuItem(BOL_NavigationService::MENU_TYPE_APPEARANCE, "admin_themes_edit", "admin", "sidebar_menu_item_themes_customize", OW_Navigation::VISIBLE_FOR_ALL);

OW::getNavigation()->addMenuItem(BOL_NavigationService::MENU_TYPE_PAGES, "admin_pages_main", "admin", "sidebar_menu_item_pages_manage", OW_Navigation::VISIBLE_FOR_ALL);
OW::getNavigation()->addMenuItem(BOL_NavigationService::MENU_TYPE_PAGES, "admin_pages_maintenance", "admin", "sidebar_menu_item_special_pages", OW_Navigation::VISIBLE_FOR_MEMBER);
OW::getNavigation()->addMenuItem(BOL_NavigationService::MENU_TYPE_PAGES, "admin_pages_user_profile", "admin", "sidebar_menu_item_user_profile", OW_Navigation::VISIBLE_FOR_ALL);
OW::getNavigation()->addMenuItem(BOL_NavigationService::MENU_TYPE_PAGES, "admin_pages_user_dashboard", "admin", "sidebar_menu_item_user_dashboard", OW_Navigation::VISIBLE_FOR_ALL);

OW::getNavigation()->addMenuItem(BOL_NavigationService::MENU_TYPE_PLUGINS, "admin_plugins_installed", "admin", "sidebar_menu_plugins_installed", OW_Navigation::VISIBLE_FOR_MEMBER);
OW::getNavigation()->addMenuItem(BOL_NavigationService::MENU_TYPE_PLUGINS, "admin_plugins_available", "admin", "sidebar_menu_plugins_available", OW_Navigation::VISIBLE_FOR_MEMBER);
OW::getNavigation()->addMenuItem(BOL_NavigationService::MENU_TYPE_PLUGINS, "admin_plugins_add", "admin", "sidebar_menu_plugins_add", OW_Navigation::VISIBLE_FOR_MEMBER);

OW::getNavigation()->addMenuItem(BOL_NavigationService::MENU_TYPE_MOBILE, "mobile.admin.navigation", "mobile", "mobile_admin_navigation", OW_Navigation::VISIBLE_FOR_ALL);
OW::getNavigation()->addMenuItem(BOL_NavigationService::MENU_TYPE_MOBILE, "mobile.admin.pages.index", "mobile", "mobile_admin_pages_index", OW_Navigation::VISIBLE_FOR_ALL);
OW::getNavigation()->addMenuItem(BOL_NavigationService::MENU_TYPE_MOBILE, "mobile.admin.pages.dashboard", "mobile", "mobile_admin_pages_dashboard", OW_Navigation::VISIBLE_FOR_ALL);
OW::getNavigation()->addMenuItem(BOL_NavigationService::MENU_TYPE_MOBILE, "mobile.admin_settings", "mobile", "mobile_admin_settings", OW_Navigation::VISIBLE_FOR_MEMBER);

OW::getNavigation()->addMenuItem("admin_dev", "admin_developer_tools_language", "admin", "sidebar_menu_item_dev_langs", OW_Navigation::VISIBLE_FOR_ALL);



