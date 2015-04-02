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
 * The class is responsible for text search management.
 * 
 * @author Alex Ermashev <alexermashev@gmail.com>
 * @package ow.ow_core
 * @since 1.0
 */
final class OW_TextSearchManager
{
    /**
     * Singleton instance.
     * @var OW_TextSearchManager
     */
    private static $classInstance;

    /**
     * Default storage instance     
     * @var BASE_CLASS_InterfaceSearchStorage
     */
    private static $defaultStorageInstance;

    /**
     * Active storage instance     
     * @var BASE_CLASS_InterfaceSearchStorage
     */
    private static $activeStorageInstance;

    /**
     * Default search storage
     * @var string
     */
    private static $defaultStorage = 'BASE_CLASS_MysqlSearchStorage';

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return OW_TextSearchManager
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
    private function __construct()
    {}

    /**
     * Add entity
     *
     * @param string $type
     * @param integer $id
     * @param string  $searchText
     * @param array $tags
     * @param boolean $isActive
     * @return boolean
     */
    public function addEntity( $type, $id, $searchText, array $tags = array(), $isActive = true )
    {
        $result =  self::getDefaultStorageInstance()->addEntity($type, $id, $searchText, $tags, $isActive);

        if ( $result && self::getActiveStorageInstance() )
        {
            $result =  self::getActiveStorageInstance()->addEntity($type, $id, $searchText, $tags, $isActive);
        }

        return $result;
    }

    /**
     * Delete entity
     *
     * @param string $type
     * @param integer $id
     * @return boolean
     */
    public function deleteEntity( $type, $id )
    {
        $result =  self::getDefaultStorageInstance()->deleteEntity($type, $id);

        if ( $result && self::getActiveStorageInstance() )
        {
            $result =  self::getActiveStorageInstance()->deleteEntity($type, $id);
        }

        return $result;
    }

    /**
     * Delete all entities
     *
     * @param string $type
     * @return boolean
     */
    public function deleteAllEntities( $type = null )
    {
        $result =  self::getDefaultStorageInstance()->deleteAllEntities($type);

        if ( $result && self::getActiveStorageInstance() )
        {
            $result =  self::getActiveStorageInstance()->deleteAllEntities($type);
        }

        return $result;
    }

    /**
     * Deactivate all entities
     *
     * @param string $type
     * @return boolean
     */
    public function deactivateAllEntities( $type = null )
    {
        $result = self::getDefaultStorageInstance()->deactivateAllEntities($type);

        if ( $result && self::getActiveStorageInstance() )
        {
            $result =  self::getActiveStorageInstance()->deactivateAllEntities($type);
        }

        return $result;
    }

    /**
     * Activate all entities
     *
     * @param string $type
     * @return boolean
     */
    public function activateAllEntities( $type = null )
    {
        $result = self::getDefaultStorageInstance()->activateAllEntities($type);

        if ( $result && self::getActiveStorageInstance() )
        {
            $result =  self::getActiveStorageInstance()->activateAllEntities($type);
        }

        return $result;
    }

    /**
     * Search entities
     *
     * @param string $searchText
     * @param integer $first
     * @param integer $limit
     * @param array $tags
     * @param boolean $sortByDate - sort by date or by relevance
     * @return array
     */
    public function searchEntities( $searchText, $first, $limit, array $tags = array(), $sortByDate = false )
    {
        if ( self::getActiveStorageInstance() )
        {
            return self::getActiveStorageInstance()->
                    searchEntities($searchText, $first, $limit, $tags, $sortByDate);
        }

        return self::getDefaultStorageInstance()->
                    searchEntities($searchText, $first, $limit, $tags, $sortByDate);
    }

    /**
     * Search entities count
     *
     * @param string $searchText
     * @param array $tags
     * @return integer
     */
    public function searchEntitiesCount( $searchText, array $tags = array() )
    {
        if ( self::getActiveStorageInstance() )
        {
            return self::getActiveStorageInstance()->searchEntitiesCount($searchText, $tags);
        }

        return self::getDefaultStorageInstance()->searchEntitiesCount($searchText, $tags);
    }

    /**
     * Get all entities
     *
     * @param integer $first
     * @param integer $limit
     * @param string $type
     * @return array
     */
    public function getAllEntities( $first, $limit, $type = null )
    {
        return self::getDefaultStorageInstance()->getAllEntities($first, $limit, $type);
    }

    /**
     * Get active storage instance
     * 
     * @return boolean|BASE_CLASS_InterfaceSearchStorage
     */
    private static function getActiveStorageInstance()
    {
        return false;
    }

    /**
     * Get default storage instance
     * 
     * @return BASE_CLASS_InterfaceSearchStorage
     */
    private static function getDefaultStorageInstance()
    {
        if ( self::$defaultStorageInstance === null )
        {
            self::$defaultStorageInstance = new self::$defaultStorage();
        }

        return self::$defaultStorageInstance;
    }
}
