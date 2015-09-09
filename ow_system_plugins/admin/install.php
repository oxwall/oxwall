<?php

// Configs

OW::getConfig()->addConfig("admin", "mass_mailing_timestamp", null, null);
OW::getConfig()->addConfig("admin", "admin_menu_state", json_encode(array()), null);

// Langs
OW::getLanguage()->importPluginLangs(dirname(__FILE__) . DS . "langs.zip", "admin");

// Menu
OW::getNavigation()->addMenuItem("admin", "admin_default", "admin", "sidebar_menu_dashboard", OW_Navigation::VISIBLE_FOR_ALL);
OW::getNavigation()->addMenuItem("admin", "admin_finance", "admin", "sidebar_menu_item_dashboard_finance", OW_Navigation::VISIBLE_FOR_ALL);

OW::getNavigation()->addMenuItem("admin_pages", "admin_pages_main", "admin", "sidebar_menu_item_pages_manage", OW_Navigation::VISIBLE_FOR_ALL);
OW::getNavigation()->addMenuItem("admin_pages", "admin_pages_user_dashboard", "admin", "sidebar_menu_item_user_dashboard", OW_Navigation::VISIBLE_FOR_ALL);
OW::getNavigation()->addMenuItem("admin_pages", "admin_pages_user_profile", "admin", "sidebar_menu_item_user_profile", OW_Navigation::VISIBLE_FOR_ALL);
OW::getNavigation()->addMenuItem("admin_pages", "admin_pages_splash_screen", "admin", "sidebar_menu_item_splash_screen", OW_Navigation::VISIBLE_FOR_MEMBER);
OW::getNavigation()->addMenuItem("admin_pages", "admin_pages_maintenance", "admin", "sidebar_menu_item_maintenance", OW_Navigation::VISIBLE_FOR_MEMBER);

OW::getNavigation()->addMenuItem("admin_appearance", "admin_themes_edit", "admin", "sidebar_menu_item_theme_edit", OW_Navigation::VISIBLE_FOR_ALL);
OW::getNavigation()->addMenuItem("admin_appearance", "admin_themes_choose", "admin", "sidebar_menu_item_theme_choose", OW_Navigation::VISIBLE_FOR_ALL);
OW::getNavigation()->addMenuItem("admin_appearance", "admin_themes_add_new", "admin", "sidebar_menu_themes_add", OW_Navigation::VISIBLE_FOR_ALL);

OW::getNavigation()->addMenuItem("admin_privacy", "admin_permissions", "admin", "sidebar_menu_item_permission", OW_Navigation::VISIBLE_FOR_MEMBER);
OW::getNavigation()->addMenuItem("admin_privacy", "admin_permissions_roles", "admin", "sidebar_menu_item_permission_role", OW_Navigation::VISIBLE_FOR_MEMBER);
OW::getNavigation()->addMenuItem("admin_privacy", "admin_permissions_moderators", "admin", "sidebar_menu_item_permission_moders", OW_Navigation::VISIBLE_FOR_MEMBER);

OW::getNavigation()->addMenuItem("admin_users", "admin_users_browse", "admin", "sidebar_menu_item_users", OW_Navigation::VISIBLE_FOR_ALL);
OW::getNavigation()->addMenuItem("admin_users", "admin_user_roles", "admin", "sidebar_menu_item_users_roles", OW_Navigation::VISIBLE_FOR_ALL);
OW::getNavigation()->addMenuItem("admin_users", "questions_index", "admin", "sidebar_menu_item_questions", OW_Navigation::VISIBLE_FOR_ALL);
OW::getNavigation()->addMenuItem("admin_users", "admin_massmailing", "admin", "sidebar_menu_item_plugin_mass_mailing", OW_Navigation::VISIBLE_FOR_ALL);
OW::getNavigation()->addMenuItem("admin_users", "admin_restrictedusernames", "admin", "sidebar_menu_item_restricted_usernames", OW_Navigation::VISIBLE_FOR_ALL);

OW::getNavigation()->addMenuItem("admin_settings", "admin_settings_main", "admin", "sidebar_menu_item_main_settings", OW_Navigation::VISIBLE_FOR_ALL);
OW::getNavigation()->addMenuItem("admin_settings", "admin_settings_user", "admin", "sidebar_menu_item_user_settings", OW_Navigation::VISIBLE_FOR_ALL);
OW::getNavigation()->addMenuItem("admin_settings", "admin_settings_language", "admin", "sidebar_menu_item_settings_language", OW_Navigation::VISIBLE_FOR_ALL);

OW::getNavigation()->addMenuItem("admin_plugins", "admin_plugins_installed", "admin", "sidebar_menu_plugins_installed", OW_Navigation::VISIBLE_FOR_MEMBER);
OW::getNavigation()->addMenuItem("admin_plugins", "admin_plugins_available", "admin", "sidebar_menu_plugins_available", OW_Navigation::VISIBLE_FOR_MEMBER);
OW::getNavigation()->addMenuItem("admin_plugins", "admin_plugins_add", "admin", "sidebar_menu_plugins_add", OW_Navigation::VISIBLE_FOR_MEMBER);

OW::getNavigation()->addMenuItem("admin_mobile", "mobile.admin.navigation", "mobile", "mobile_admin_navigation", OW_Navigation::VISIBLE_FOR_ALL);
OW::getNavigation()->addMenuItem("admin_mobile", "mobile.admin.pages.index", "mobile", "mobile_admin_pages_index", OW_Navigation::VISIBLE_FOR_ALL);
OW::getNavigation()->addMenuItem("admin_mobile", "mobile.admin.pages.dashboard", "mobile", "mobile_admin_pages_dashboard", OW_Navigation::VISIBLE_FOR_ALL);
OW::getNavigation()->addMenuItem("admin_mobile", "mobile.admin_settings", "mobile", "mobile_admin_settings", OW_Navigation::VISIBLE_FOR_MEMBER);

OW::getNavigation()->addMenuItem("admin_dev", "admin_developer_tools_language", "admin", "sidebar_menu_item_dev_langs", OW_Navigation::VISIBLE_FOR_ALL);

// Athorization
OW::getAuthorization()->addGroup('admin'); // TODO check if the group is used somewhere