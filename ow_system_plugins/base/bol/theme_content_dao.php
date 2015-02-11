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

/**
 * Data Access Object for `theme_content` table.  
 * 
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_ThemeContentDao extends OW_BaseDao
{
    const THEME_ID = 'themeId';
    const TYPE = 'type';
    const VALUE = 'value';
    const VALUE_TYPE_ENUM_CSS = 'css';
    const VALUE_TYPE_ENUM_DECORATOR = 'decorator';
    const VALUE_TYPE_ENUM_MASTER_PAGE = 'master_page';
    const VALUE_TYPE_ENUM_MOBILE_MASTER_PAGE = 'm_master_page';

    /**
     * Singleton instance.
     *
     * @var BOL_ThemeContentDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_ThemeContentDao
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * Constructor.
     */
    protected function __construct()
    {
        parent::__construct();
    }

    /**
     * @see OW_BaseDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return 'BOL_ThemeContent';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_theme_content';
    }

    /**
     * Returns theme content items for provided theme id.
     *
     * @param integer $themeId
     * @return array
     */
    public function findByThemeId( $themeId )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::THEME_ID, $themeId);
        return $this->findListByExample($example, 24 * 3600, array(BOL_ThemeDao::CACHE_TAG_PAGE_LOAD_THEME, OW_CacheManager::TAG_OPTION_INSTANT_LOAD));
    }

    /**
     * Deletes theme content items for provided theme id.
     * 
     * @param string $themeId
     * @return integer
     */
    public function deleteByThemeId( $themeId )
    {
        $this->clearCache();
        $example = new OW_Example();
        $example->andFieldEqual(self::THEME_ID, $themeId);
        return $this->deleteByExample($example);
    }

    protected function clearCache()
    {
        OW::getCacheManager()->clean(array(BOL_ThemeDao::CACHE_TAG_PAGE_LOAD_THEME));
    }
}
