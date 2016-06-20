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

$languageService = Updater::getLanguageService();

$languages = $languageService->getLanguages();
$langId = null;

foreach ( $languages as $lang )
{
    if ( $lang->tag == "en" )
    {
        $langId = $lang->id;
        break;
    }
}

$languagesToAdd = array(
    array( 'base', 'meta_title_user_list', '{$user_list} users | {$site_name}' ),
    array( 'base', 'meta_desc_user_list', 'View {$user_list} users at {$site_name}. Join us and meet the love your life today!' ),
    array( 'base', 'meta_keywords_user_list', '' ),
    array( 'base', 'user_list_type_latest', 'Latest' ),
    array( 'base', 'user_list_type_online', 'Online' ),
    array( 'base', 'seo_meta_section_users', 'Users' ),
    array( 'base', 'seo_meta_form_element_title_label', 'Title' ),
    array( 'base', 'seo_meta_form_element_title_desc', 'Recommended title length is up to 70 symbols' ),
    array( 'base', 'seo_meta_form_element_desc_label', 'Meta description' ),
    array( 'base', 'seo_meta_form_element_desc_desc', 'Recommended description length is up to 150 symbols' ),
    array( 'base', 'seo_meta_form_element_keywords_label', 'Meta keywords' ),
    array( 'base', 'seo_meta_form_element_index_label', 'Allow for indexing' ),
    array( 'base', 'seo_meta_choose_pages_label', 'Choose pages:' ),
    array( 'base', 'seo_meta_user_list_label', 'All Site Members by List (Online / Latest) Page' ),
    array( 'base', 'seo_meta_section_base_pages', 'Base Pages' ),

    // index
    array( 'base', 'meta_title_index', '{$site_name} - Find Dates Here!' ),
    array( 'base', 'meta_desc_index', '{$site_name} dating website with dating apps and robust online community. Meet the love of your life here today!' ),
    array( 'base', 'meta_keywords_index', '' ),
    array( 'base', 'seo_meta_index_label', 'Index' ),
    // join
    array( 'base', 'meta_title_join', 'Join {$site_name} - Find Dates Here!' ),
    array( 'base', 'meta_desc_join', 'Join {$site_name} to meet new people and chat with other signles. Find your love today!' ),
    array( 'base', 'meta_keywords_join', '' ),
    array( 'base', 'seo_meta_join_label', 'Join' ),
    // Sign-in
    array( 'base', 'meta_title_sign_in', 'Sign in to {$site_name} - Find Dates Here!' ),
    array( 'base', 'meta_desc_sign_in', 'Sign in to your {$site_name} site and meet new people!' ),
    array( 'base', 'meta_keywords_sign_in', '' ),
    array( 'base', 'seo_meta_sign_in_label', 'Sign-In Page' ),
    // Forgot
    array( 'base', 'meta_title_forgot_pass', 'Forgot password for {$site_name}?' ),
    array( 'base', 'meta_desc_forgot_pass', 'Enter the email you used during registration for {$site_name} to receive a new password.' ),
    array( 'base', 'meta_keywords_forgot_pass', '' ),
    array( 'base', 'seo_meta_forgot_pass_label', 'Forgot Password Page' ),

);

if ( $langId !== null )
{
    foreach ( $languagesToAdd as $entry ){
        $languageService->addOrUpdateValue($langId, $entry[0], $entry[1], $entry[2]);
    }
}

if( !Updater::getConfigService()->configExists("base", "seo_meta_info") ){
    Updater::getConfigService()->addConfig("base", "seo_meta_info", json_encode(array("disabledEntities" => array())));
}


