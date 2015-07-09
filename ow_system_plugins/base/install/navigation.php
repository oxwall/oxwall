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


// Menus
OW::getNavigation()->addMenuItem(OW_Navigation::MAIN, "base_index", "base", "main_menu_index", OW_Navigation::VISIBLE_FOR_ALL);
OW::getNavigation()->addMenuItem("hidden", "base_member_profile", "base", "main_menu_my_profile", OW_Navigation::VISIBLE_FOR_MEMBER);
OW::getNavigation()->addMenuItem(OW_Navigation::MAIN, "users", "base", "users_main_menu_item", OW_Navigation::VISIBLE_FOR_ALL);
OW::getNavigation()->addMenuItem(OW_Navigation::MAIN, "base_join", "base", "base_join_menu_item", OW_Navigation::VISIBLE_FOR_GUEST);
OW::getNavigation()->addMenuItem(OW_Navigation::MAIN, "base_member_dashboard", "base", "dashboard", OW_Navigation::VISIBLE_FOR_MEMBER);
OW::getNavigation()->addMenuItem(OW_Navigation::MOBILE_HIDDEN, "base_member_dashboard", "mobile", "mobile_pages_dashboard", OW_Navigation::VISIBLE_FOR_MEMBER);
OW::getNavigation()->addMenuItem(OW_Navigation::MOBILE_BOTTOM, "base.desktop_version", "base", "desktop_version_menu_item", OW_Navigation::VISIBLE_FOR_ALL);
OW::getNavigation()->addMenuItem(OW_Navigation::MOBILE_TOP, "base_index", "base", "index_menu_item", OW_Navigation::VISIBLE_FOR_ALL);
OW::getNavigation()->addMenuItem(OW_Navigation::BOTTOM, "base.mobile_version", "base", "mobile_version_menu_item", OW_Navigation::VISIBLE_FOR_ALL);

// Custom menu items

$menuItem = new BOL_MenuItem(); // Terms of use
$menuItem->prefix = "base";
$menuItem->key = "page-119658";
$menuItem->documentKey = "page-119658";
$menuItem->type = OW_Navigation::BOTTOM;
$menuItem->order = 1;
$menuItem->visibleFor = OW_Navigation::VISIBLE_FOR_ALL;

BOL_NavigationService::getInstance()->saveMenuItem($menuItem);

$menuItem = new BOL_MenuItem(); // Terms of use
$menuItem->prefix = "base";
$menuItem->key = "page_81959573";
$menuItem->documentKey = "page_81959573";
$menuItem->type = OW_Navigation::BOTTOM;
$menuItem->order = 2;
$menuItem->visibleFor = OW_Navigation::VISIBLE_FOR_ALL;

BOL_NavigationService::getInstance()->saveMenuItem($menuItem);

$menuItem = new BOL_MenuItem(); // Mobile terms of use
$menuItem->prefix = "ow_custom";
$menuItem->key = "mobile_page_14788567";
$menuItem->documentKey = "mobile_page_14788567";
$menuItem->type = OW_Navigation::MOBILE_BOTTOM;
$menuItem->order = 0;
$menuItem->visibleFor = OW_Navigation::VISIBLE_FOR_ALL;

BOL_NavigationService::getInstance()->saveMenuItem($menuItem);

// Documents
$document = new BOL_Document(); // Terms of use
$document->key = "page-119658";
$document->uri = "terms-of-use";
$document->isStatic = 1;
$document->isMobile = 0;

BOL_NavigationService::getInstance()->saveDocument($document);

$document = new BOL_Document(); // Privacy policy
$document->key = "page_81959573";
$document->uri = "privacy-policy";
$document->isStatic = 1;
$document->isMobile = 0;

BOL_NavigationService::getInstance()->saveDocument($document);

$document = new BOL_Document(); // Mobile terms of use
$document->key = "mobile_page_14788567";
$document->uri = "cp-55";
$document->isStatic = 1;
$document->isMobile = 1;

BOL_NavigationService::getInstance()->saveDocument($document);