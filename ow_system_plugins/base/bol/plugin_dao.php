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
 * Data Access Object for `plugin` table.  
 * 
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_PluginDao extends OW_BaseDao
{
    const ID = "id";
    const TITLE = "title";
    const DESCRIPTION = "description";
    const MODULE = "module";
    const KEY = "key";
    const IS_SYSTEM = "isSystem";
    const IS_ACTIVE = "isActive";
    const VERSION = "version";
    const UPDATE = "update";
    const LICENSE_KEY = "licenseKey";
    const LICENSE_CHECK_STAMP = "licenseCheckTimestamp";
    const UPDATE_VAL_UP_TO_DATE = 0;
    const UPDATE_VAL_UPDATE = 1;
    const UPDATE_VAL_MANUAL_UPDATE = 2;

    /**
     * Constructor.
     */
    protected function __construct()
    {
        parent::__construct();
    }
    /**
     * Singleton instance.
     *
     * @var BOL_PluginDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_PluginDao
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
     * @see OW_BaseDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return "BOL_Plugin";
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . "base_plugin";
    }

    /**
     * Returns all active plugins.
     *
     * @return array<BOL_Plugin>
     */
    public function findActivePlugins()
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::IS_ACTIVE, true);
        return $this->findListByExample($example);
    }

    /**
     * Finds plugin by key.
     * 
     * @param string $key
     * @return BOL_Plugin
     */
    public function findPluginByKey( $key )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::KEY, $key);

        return $this->findObjectByExample($example);
    }

    /**
     * Deletes plugin entry by key.
     * 
     * @param string $key
     */
    public function deletePluginKey( $key )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::KEY, $key);

        $this->deleteByExample($example);
    }

    /**
     * Returns all regular (not system plugins).
     * 
     * @return array<BOL_Plugin>
     */
    public function findRegularPlugins()
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::IS_SYSTEM, 0);

        return $this->findListByExample($example);
    }

    public function findPluginsForUpdateCount()
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::UPDATE, self::UPDATE_VAL_UPDATE);

        return $this->countByExample($example);
    }

    /**
     * @return BOL_Plugin
     */
    public function findPluginForManualUpdate()
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::UPDATE, self::UPDATE_VAL_MANUAL_UPDATE);
        $example->andFieldEqual(self::IS_ACTIVE, 1);
        $example->setLimitClause(0, 1);

        return $this->findObjectByExample($example);
    }

    /**
     * @return array 
     */
    public function findPluginsWithInvalidLicense()
    {
        $example = new OW_Example();
        $example->andFieldGreaterThan(self::LICENSE_CHECK_STAMP, 0);

        return $this->findListByExample($example);
    }
}
