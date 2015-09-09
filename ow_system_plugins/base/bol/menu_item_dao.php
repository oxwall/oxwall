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
 * Data Access Object for `menu_item` table.  
 * 
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_MenuItemDao extends OW_BaseDao
{
    const PREFIX = 'prefix';
    const KEY = 'key';
    const DOCUMENT_KEY = 'documentKey';
    const TYPE = 'type';
    const ORDER = 'order';
    const ROUTE_PATH = 'routePath';
    const EXTERNAL_URL = 'externalUrl';
    const NEW_WINDOW = 'newWindow';
    const VISIBLE_FOR = 'visibleFor';
    const VALUE_TYPE_MAIN = 'main';
    const VALUE_TYPE_BOTTOM = 'bottom';
    const VALUE_TYPE_HIDDEN = 'hidden';
    const VALUE_TYPE_ADMIN = 'admin';
    const VALUE_TYPE_SETTINGS = 'admin_settings';
    const VALUE_TYPE_PAGES = 'admin_pages';
    const VALUE_TYPE_APPEARANCE = 'admin_appearance';
    const VALUE_TYPE_USERS = 'admin_users';
    const VALUE_TYPE_PLUGINS = 'admin_plugins';
    const VALUE_TYPE_MOBILE = 'admin_mobile';
    const VALUE_TYPE_MOBILE_TOP = 'mobile_top';
    const VALUE_TYPE_MOBILE_BOTTOM = 'mobile_bottom';
    const VALUE_TYPE_MOBILE_HIDDEN = 'mobile_hidden';
    
    const VALUE_VISIBLE_FOR_NOBODY = 0;
    const VALUE_VISIBLE_FOR_GUEST = 1;
    const VALUE_VISIBLE_FOR_MEMBER = 2;
    const VALUE_VISIBLE_FOR_ALL = 3;
    const CACHE_TAG_MENU_TYPE_LIST = 'base.menu.menu_type_list';

    /**
     * @var BOL_DocumentDao
     */
    private $documentDao;

    /**
     * Singleton instance.
     *
     * @var BOL_MenuItemDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_MenuItemDao
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
        $this->documentDao = BOL_DocumentDao::getInstance();
    }

    /**
     * @see OW_BaseDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return 'BOL_MenuItem';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_menu_item';
    }

    /**
     * Returns all active items for provided menu type.
     *
     * @param string $menuType
     * @return array
     */
    public function findMenuItems( $menuType )
    {
        return $this->dbo->queryForList("
			SELECT `mi`.*, `mi`.`key` AS `menu_key`, `d`.`class`, `d`.`action`, `d`.`uri`, `d`.`isStatic`
			FROM `" . $this->getTableName() . "` AS `mi`
			LEFT JOIN `" . $this->documentDao->getTableName() . "` AS `d` ON ( `mi`.`" . self::DOCUMENT_KEY . "` = `d`.`" . BOL_DocumentDao::KEY . "`)
			WHERE `mi`.`" . self::TYPE . "` = :menuType ORDER BY `mi`.`order` ASC", array('menuType' => $menuType), 24 * 3600, array(self::CACHE_TAG_MENU_TYPE_LIST, OW_CacheManager::TAG_OPTION_INSTANT_LOAD));
    }

    /**
     * Returns all active items for provided menu types.
     *
     * @param string $menuType
     * @return array
     */
    public function findMenuItemsForMenuTypes( $menuTypes )
    {
        return $this->dbo->queryForList("
			SELECT `mi`.*, `mi`.`key` AS `menu_key`, `d`.`class`, `d`.`action`, `d`.`uri`, `d`.`isStatic`
			FROM `" . $this->getTableName() . "` AS `mi`
			LEFT JOIN `" . $this->documentDao->getTableName() . "` AS `d` ON ( `mi`.`" . self::DOCUMENT_KEY . "` = `d`.`" . BOL_DocumentDao::KEY . "`)
			WHERE `mi`.`" . self::TYPE . "` IN (" . $this->dbo->mergeInClause($menuTypes) . ") ORDER BY `mi`.`order` ASC");
    }

    /**
     * Returns max sort order for menu type.
     * 
     * @param string $menuType
     * @return integer
     */
    public function findMaxOrderForMenuType( $menuType )
    {
        return (int) $this->dbo->queryForColumn("SELECT MAX(`" . self::ORDER . "`) FROM `" . $this->getTableName() . "` WHERE `" . self::TYPE . "` = :menuType", array('menuType' => $menuType));
    }

    /**
     * @param string $menuType
     * @param string $prefix
     * @param string $key
     * @return BOL_MenuItem
     */
    public function findMenuItem( $prefix, $key )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::PREFIX, $prefix);
        $example->andFieldEqual(self::KEY, $key);

        return $this->findObjectByExample($example);
    }

    public function findFirstLocal( $visibleFor, $menuType )
    {

        return $this->dbo->queryForObject("
			SELECT *
			FROM `" . $this->getTableName() . "`
			WHERE `visibleFor` & ? AND `externalUrl` IS NULL AND `type` = ?
			ORDER BY `order` ASC
			LIMIT 1", $this->getDtoClassName(), array($visibleFor, $menuType));
    }

    public function findByDocumentKey( $docKey )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::DOCUMENT_KEY, $docKey);

        return $this->findObjectByExample($example);
    }

    protected function clearCache()
    {
        OW::getCacheManager()->clean(array(BOL_MenuItemDao::CACHE_TAG_MENU_TYPE_LIST));
    }
}
