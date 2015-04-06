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
    private $defaultStorageInstance;

    /**
     * Active storage instance     
     * @var BASE_CLASS_InterfaceSearchStorage
     */
    private $activeStorageInstance;

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
    {
        $this->defaultStorageInstance = new BASE_CLASS_MysqlSearchStorage;
        $this->activeStorageInstance = null;
    }

    /**
     * Add entity
     *
     * @param string $entityType
     * @param integer $entityId
     * @param string  $text
     * @param array $tags
     * @param boolean $isActive
     * @return boolean
     */
    public function addEntity( $entityType, $entityId, $text, array $tags = array(), $isActive = true )
    {
        $result =  $this->defaultStorageInstance->addEntity($entityType, $entityId, $text, $tags, $isActive);

        if ( $result && $this->activeStorageInstance )
        {
            $result =  $this->activeStorageInstance->addEntity($entityType, $entityId, $text, $tags, $isActive);
        }

        return $result;
    }

    /**
     * Set entity status
     * 
     * @param string $entityType
     * @param integer $entityId
     * @param boolean $isActive
     * @return boolean
     */
    public function setEntityStatus( $entityType, $entityId, $isActive = true )
    {
        $result =  $this->defaultStorageInstance->setEntityStatus($entityType, $entityId, $isActive);

        if ( $result && $this->activeStorageInstance )
        {
            $result =  $this->activeStorageInstance->setEntityStatus($entityType, $entityId, $isActive);
        }

        return $result;
    }

    /**
     * Delete entity
     *
     * @param string $entityType
     * @param integer $entityId
     * @return boolean
     */
    public function deleteEntity( $entityType, $entityId )
    {
        $result =  $this->defaultStorageInstance->deleteEntity($entityType, $entityId);

        if ( $result && $this->activeStorageInstance )
        {
            $result =  $this->activeStorageInstance->deleteEntity($entityType, $entityId);
        }

        return $result;
    }

    /**
     * Delete all entities
     *
     * @param string $entityType
     * @return boolean
     */
    public function deleteAllEntities( $entityType = null )
    {
        $result =  $this->defaultStorageInstance->deleteAllEntities($entityType);

        if ( $result && $this->activeStorageInstance )
        {
            $result =  $this->activeStorageInstance->deleteAllEntities($entityType);
        }

        return $result;
    }

    /**
     * Deactivate all entities
     *
     * @param string $entityType
     * @return boolean
     */
    public function deactivateAllEntities( $entityType = null )
    {
        $result = $this->defaultStorageInstance->deactivateAllEntities($entityType);

        if ( $result && $this->activeStorageInstance )
        {
            $result =  $this->activeStorageInstance->deactivateAllEntities($entityType);
        }

        return $result;
    }

    /**
     * Activate all entities
     *
     * @param string $entityType
     * @return boolean
     */
    public function activateAllEntities( $entityType = null )
    {
        $result = $this->defaultStorageInstance->activateAllEntities($entityType);

        if ( $result && $this->activeStorageInstance )
        {
            $result =  $this->activeStorageInstance->activateAllEntities($entityType);
        }

        return $result;
    }

    /**
     * Search entities
     *
     * @param string $text
     * @param integer $first
     * @param integer $limit
     * @param array $tags
     * @param boolean $sortByDate - sort by date or by relevance
     * @return array
     */
    public function searchEntities( $text, $first, $limit, array $tags = array(), $sortByDate = false )
    {
        if ( $this->activeStorageInstance )
        {
            return $this->activeStorageInstance->
                    searchEntities($text, $first, $limit, $tags, $sortByDate);
        }

        return $this->defaultStorageInstance->
                    searchEntities($text, $first, $limit, $tags, $sortByDate);
    }

    /**
     * Search entities count
     *
     * @param string $text
     * @param array $tags
     * @return integer
     */
    public function searchEntitiesCount( $text, array $tags = array() )
    {
        if ( $this->activeStorageInstance )
        {
            return $this->activeStorageInstance->searchEntitiesCount($text, $tags);
        }

        return $this->defaultStorageInstance->searchEntitiesCount($text, $tags);
    }

    /**
     * Get all entities
     *
     * @param integer $first
     * @param integer $limit
     * @param string $entityType
     * @return array
     */
    public function getAllEntities( $first, $limit, $entityType = null )
    {
        return $this->defaultStorageInstance->getAllEntities($first, $limit, $entityType);
    }
}