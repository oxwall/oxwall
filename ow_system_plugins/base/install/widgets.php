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


// Widgets

BOL_ComponentAdminService::getInstance()->addPlace(BOL_ComponentService::PLACE_DASHBOARD, false);
BOL_ComponentAdminService::getInstance()->addPlace(BOL_ComponentService::PLACE_INDEX, false);
BOL_ComponentAdminService::getInstance()->addPlace(BOL_ComponentService::PLACE_PROFILE, false);
BOL_ComponentAdminService::getInstance()->addPlace(BOL_MobileWidgetService::PLACE_MOBILE_INDEX, false);
BOL_ComponentAdminService::getInstance()->addPlace(BOL_MobileWidgetService::PLACE_MOBILE_DASHBOARD, false);

$widget = BOL_ComponentAdminService::getInstance()->addWidget("BASE_CMP_AboutMeWidget", false);
$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentService::PLACE_PROFILE);
BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentService::SECTION_LEFT, 1);

$widget = BOL_ComponentAdminService::getInstance()->addWidget("BASE_CMP_RssWidget", true);
BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentService::PLACE_PROFILE);
BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentService::PLACE_INDEX);
BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentService::PLACE_DASHBOARD);

$widget = BOL_ComponentAdminService::getInstance()->addWidget("BASE_CMP_UserViewWidget", false);
$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentService::PLACE_PROFILE);
BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentService::SECTION_RIGHT, 1);

$widget = BOL_ComponentAdminService::getInstance()->addWidget("BASE_CMP_JoinNowWidget", false);
BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentService::PLACE_INDEX);

$widget = BOL_ComponentAdminService::getInstance()->addWidget("BASE_CMP_ProfileWallWidget", false);
BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentService::PLACE_PROFILE);

$widget = BOL_ComponentAdminService::getInstance()->addWidget("BASE_CMP_UserAvatarWidget", false);
$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentService::PLACE_PROFILE);
BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentService::SECTION_LEFT, 0);

$widget = BOL_ComponentAdminService::getInstance()->addWidget("BASE_CMP_IndexWallWidget", false);
BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentService::PLACE_INDEX);

$widget = BOL_ComponentAdminService::getInstance()->addWidget("BASE_CMP_AddNewContent", false);
$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentService::PLACE_INDEX);
BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentService::SECTION_SIDEBAR, 1);

$widget = BOL_ComponentAdminService::getInstance()->addWidget("BASE_CMP_CustomHtmlWidget", true);
BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentService::PLACE_DASHBOARD);
BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentService::PLACE_PROFILE);
BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentService::PLACE_INDEX);
$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentService::PLACE_INDEX, "admin-4b543d8cdc488", true);
BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentService::SECTION_LEFT, 0);
BOL_ComponentAdminService::getInstance()->saveComponentSettingList($placeWidget->uniqName, array("content" => "Welcome to our new site! Feel free to participate in our community!","title" => "Welcome"));

$widget = BOL_ComponentAdminService::getInstance()->addWidget("BASE_CMP_UserListWidget", false);
$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentService::PLACE_INDEX);
BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentService::SECTION_LEFT, 1);

$widget = BOL_ComponentAdminService::getInstance()->addWidget("BASE_CMP_MyAvatarWidget", false);
$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentService::PLACE_INDEX);
BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentService::SECTION_SIDEBAR, 0);

$widget = BOL_ComponentAdminService::getInstance()->addWidget("BASE_CMP_QuickLinksWidget", false);
$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentService::PLACE_DASHBOARD);
BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentService::SECTION_RIGHT, 2);

$widget = BOL_ComponentAdminService::getInstance()->addWidget("BASE_MCMP_CustomHtmlWidget", true);
BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_MobileWidgetService::PLACE_MOBILE_DASHBOARD);
BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_MobileWidgetService::PLACE_MOBILE_INDEX);
$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_MobileWidgetService::PLACE_MOBILE_INDEX, "admin-5295f2e03ec8a", true);
BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_MobileWidgetService::SECTION_MOBILE_MAIN, 0);
BOL_ComponentAdminService::getInstance()->saveComponentSettingList($placeWidget->uniqName, array("content" => "Welcome to our community! Here you\'ll find like-minded individuals who are passionate about the same things as you!","title" => "Welcome!"));
$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_MobileWidgetService::PLACE_MOBILE_INDEX, "admin-5295f2e40db5c", true);
BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_MobileWidgetService::SECTION_MOBILE_MAIN, 1);
BOL_ComponentAdminService::getInstance()->saveComponentSettingList($placeWidget->uniqName, array("content" => "Feel free to participate! Take a look around and help yourself.","title" => "annotation"));

$widget = BOL_ComponentAdminService::getInstance()->addWidget("BASE_MCMP_RssWidget", true);
BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_MobileWidgetService::PLACE_MOBILE_DASHBOARD);
BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_MobileWidgetService::PLACE_MOBILE_INDEX);

$widget = BOL_ComponentAdminService::getInstance()->addWidget("BASE_MCMP_UserListWidget", false);
BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_MobileWidgetService::PLACE_MOBILE_DASHBOARD);
$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_MobileWidgetService::PLACE_MOBILE_INDEX);
BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_MobileWidgetService::SECTION_MOBILE_MAIN, 2);

$widget = BOL_ComponentAdminService::getInstance()->addWidget("BASE_CMP_ModerationToolsWidget", false);
$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentService::PLACE_DASHBOARD);
BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentService::SECTION_RIGHT, 0);

$widget = BOL_ComponentAdminService::getInstance()->addWidget("BASE_CMP_WelcomeWidget", false);
$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentService::PLACE_DASHBOARD);
BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentService::SECTION_RIGHT, 1);

// Schemes 
$scheme = BOL_ComponentAdminService::getInstance()->addScheme("ow_superwide", "ow_supernarrow", "ow_scheme_enew");
BOL_ComponentAdminService::getInstance()->savePlaceScheme(BOL_ComponentService::PLACE_PROFILE, $scheme->id);

$scheme = BOL_ComponentAdminService::getInstance()->addScheme("ow_wide", "ow_narrow", "ow_scheme_nw");
BOL_ComponentAdminService::getInstance()->savePlaceScheme(BOL_ComponentService::PLACE_INDEX, $scheme->id);

BOL_ComponentAdminService::getInstance()->addScheme("ow_column", "ow_column", "ow_scheme_equal");

BOL_ComponentAdminService::getInstance()->addScheme("ow_narrow", "ow_wide", "ow_scheme_wn");

$scheme = BOL_ComponentAdminService::getInstance()->addScheme("ow_supernarrow", "ow_superwide", "ow_scheme_ewen");
BOL_ComponentAdminService::getInstance()->savePlaceScheme(BOL_ComponentService::PLACE_DASHBOARD, $scheme->id);