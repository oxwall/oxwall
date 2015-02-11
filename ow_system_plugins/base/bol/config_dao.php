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
 * Data Access Object for `base_config` table.
 * 
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_ConfigDao extends OW_BaseDao
{
    const KEY = 'key';
    const NAME = 'name';
    const DESCRIPTION = 'description';
    const VALUE = 'value';

    /**
     * Singleton instance.
     *
     * @var BOL_ConfigDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_ConfigDao
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
        return 'BOL_Config';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_config';
    }

    /**
     * Finds config item by key and name.
     *
     * @param string $key
     * @param string $name
     * @return BOL_Config
     */
    public function findConfig( $key, $name )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::KEY, $key);
        $example->andFieldEqual(self::NAME, $name);

        return $this->findObjectByExample($example);
    }

    /**
     * Finds confids list by plugin key.
     * 
     * @param string $key
     * @return array
     */
    public function findConfigsList( $key )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::KEY, $key);

        return $this->findListByExample($example);
    }

    /**
     * Removes config by provided plugin key and config name.
     * 
     * @param string $key
     * @param string $name
     */
    public function removeConfig( $key, $name )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::KEY, trim($key));
        $example->andFieldEqual(self::NAME, trim($name));

        $this->deleteByExample($example);
    }

    /**
     * Removes configs by provided plugin key.
     * 
     * @param string $key
     */
    public function removeConfigs( $key )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::KEY, trim($key));

        $this->deleteByExample($example);
    }
}