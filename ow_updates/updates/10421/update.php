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

$adminKeysToDelete = array(
    "ads_add_banner",
    "ads_add_banner_code_desc",
    "ads_add_banner_code_label",
    "ads_add_banner_country_desc",
    "ads_add_banner_country_label",
    "ads_add_banner_submit_label",
    "ads_add_banner_title_label",
    "ads_banners_add_floatbox_label",
    "ads_banners_count_label",
    "ads_banner_add_success_message",
    "ads_banner_all_region",
    "ads_banner_delete_success_message",
    "ads_banner_edit_success_message",
    "ads_delete_banner_confirm_message",
    "ads_delete_button_label",
    "ads_edit_banner_button_label",
    "ads_edit_banner_submit_label",
    "ads_index_list_box_cap_label",
    "ads_manage_add_banners_message",
    "ads_manage_global_label",
    "ads_manage_select_plugin_text",
    "advertisement_menu_banner_list",
    "advertisement_menu_manage_banners",
    "input_settings_allow_photo_upload_label",
    "page_heading_ads",
    "page_title_ads",
    "questions_config_range_label",
    "sidebar_menu_item_ads"
);

foreach ( $adminKeysToDelete as $key )
{
    Updater::getLanguageService()->deleteLangKey("admin", $key);
}