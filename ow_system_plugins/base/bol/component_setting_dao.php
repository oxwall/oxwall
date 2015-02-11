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
 * Data Access Object for `base_component_setting` table.
 *
 * @author Sergey Kambalin <greyexpert@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_ComponentSettingDao extends OW_BaseDao
{

    /**
     * Constructor.
     *
     */
    protected function __construct()
    {
        parent::__construct();
    }
    /**
     * Singleton instance.
     *
     * @var BOL_ComponentSettingDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_ComponentSettingDao
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
        return 'BOL_ComponentSetting';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_component_setting';
    }

    public function findSettingList( $componentPlaceUniqName, array $settingNames = array() )
    {
        $example = new OW_Example();
        $example->andFieldEqual('componentPlaceUniqName', $componentPlaceUniqName);
        if ( !empty($settingNames) )
        {
            $example->andFieldInArray('name', $settingNames);
        }

        return $this->findListByExample($example);
    }

    public function findListByComponentUniqNameList( array $componentPlaceUniqNameList )
    {
        if ( empty($componentPlaceUniqNameList) )
        {
            return array();
        }

        $example = new OW_Example();
        $example->andFieldInArray('componentPlaceUniqName', $componentPlaceUniqNameList);

        return $this->findListByExample($example);
    }

    public function cloneSettingList( $sourceComponentPlaceUniqName, $destComponentPlaceUniqName )
    {
        $sourceSettings = $this->findSettingList($sourceComponentPlaceUniqName);

        foreach ( $sourceSettings as $setting )
        {
            $setting->id = null;
            $setting->componentPlaceUniqName = $destComponentPlaceUniqName;
            $this->save($setting);
        }
    }

    /**
     *
     * @param string $componentPlaceUniqName
     * @param string $name
     * @param string $value
     */
    public function saveSetting( $componentPlaceUniqName, $name, $value )
    {
        $example = new OW_Example();
        $example->andFieldEqual('name', $name);
        $example->andFieldEqual('componentPlaceUniqName', $componentPlaceUniqName);
        $componentSettingDto = $this->findObjectByExample($example);
        if ( !$componentSettingDto )
        {
            $componentSettingDto = new BOL_ComponentSetting();
            $componentSettingDto->name = $name;
        }

        $componentSettingDto->componentPlaceUniqName = $componentPlaceUniqName;
        $componentSettingDto->setValue($value);

        return $this->save($componentSettingDto);
    }

    public function deleteList( $componentPlaceUniqName )
    {
        $example = new OW_Example();
        $example->andFieldEqual('componentPlaceUniqName', $componentPlaceUniqName);

        return $this->deleteByExample($example);
    }
}