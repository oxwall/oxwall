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

$tblPrefix = OW_DB_PREFIX;
$db = Updater::getDbo();

$simpleQueryList = array(
    "CREATE TABLE IF NOT EXISTS `{$tblPrefix}base_sitemap` (
        id INT(11) NOT NULL AUTO_INCREMENT,
        url VARCHAR(255) NOT NULL,
        entityType VARCHAR(20) NOT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY url (url),
        KEY entityType (entityType)
    )
    ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci"
);

foreach ( $simpleQueryList as $query )
{
    try
    {
        $db->query($query);
    }
    catch ( Exception $e )
    {
        Updater::getLogger()->addEntry(json_encode($e));
    }
}

$config = OW::getConfig();

// add configs
if ( !$config->configExists('base', 'seo_sitemap_build_finished') )
{
    $config->addConfig('base', 'seo_sitemap_build_finished', 0);
}

if ( !$config->configExists('base', 'seo_sitemap_max_urls_in_file') )
{
    $config->addConfig('base', 'seo_sitemap_max_urls_in_file', 4000);
}

if ( !$config->configExists('base', 'seo_sitemap_entitites_max_count') )
{
    $config->addConfig('base', 'seo_sitemap_entitites_max_count', 200000);
}

if ( !$config->configExists('base', 'seo_sitemap_entitites_limit') )
{
    $config->addConfig('base', 'seo_sitemap_entitites_limit', 500);
}

if ( !$config->configExists('base', 'seo_sitemap_build_in_progress') )
{
    $config->addConfig('base', 'seo_sitemap_build_in_progress', 0);
}

if ( !$config->configExists('base', 'seo_sitemap_in_progress') )
{
    $config->addConfig('base', 'seo_sitemap_in_progress', 0);
}

if ( !$config->configExists('base', 'seo_sitemap_in_progress_time') )
{
    $config->addConfig('base', 'seo_sitemap_in_progress_time', 0);
}

if ( !$config->configExists('base', 'seo_sitemap_last_build') )
{
    $config->addConfig('base', 'seo_sitemap_last_build', 0);
}

if ( !$config->configExists('base', 'seo_sitemap_last_start') )
{
    $config->addConfig('base', 'seo_sitemap_last_start', 0);
}

if ( !$config->configExists('base', 'seo_sitemap_entities') )
{
    $config->addConfig('base', 'seo_sitemap_entities', json_encode(array()));
}

if ( !$config->configExists('base', 'seo_sitemap_schedule_update') )
{
    $config->addConfig('base', 'seo_sitemap_schedule_update', 'weekly');
}

if ( !$config->configExists('base', 'seo_sitemap_index') )
{
    $config->addConfig('base', 'seo_sitemap_index', 0);
}

Updater::getSeoService()->addSitemapEntity('admin', 'seo_sitemap_base_pages', 'base_pages', array(
    'base_pages'
), null, 1);

// register sitemap entities
Updater::getSeoService()->addSitemapEntity('admin', 'seo_sitemap_users', 'users', array(
    'user_list',
    'users'
), 'seo_sitemap_users_desc');

// add the SEO admin menu
try
{
    OW::getNavigation()->addMenuItem(
        OW_Navigation::ADMIN_SETTINGS,
        'admin_settings_seo',
        'admin',
        'sidebar_menu_item_seo_settings',
        OW_Navigation::VISIBLE_FOR_ALL);
}
catch ( Exception $e )
{
    Updater::getLogger()->addEntry(json_encode($e));
}

// import langs
Updater::getLanguageService()->importPrefixFromDir(__DIR__ . DS . 'langs', true);
