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
 * @method static OW_TextSearchManager getInstance()
 * @since 1.0
 */
class OW_TextSearchManager
{
    /**
     * Sort by date
     */
    CONST SORT_BY_DATE = BASE_CLASS_AbstractSearchStorage::SORT_BY_DATE;

    /**
     * Sort by relevance
     */
    CONST SORT_BY_RELEVANCE = BASE_CLASS_AbstractSearchStorage::SORT_BY_RELEVANCE;

    /**
     * Active entity status 
     */
    CONST ENTITY_STATUS_ACTIVE = BASE_CLASS_AbstractSearchStorage::ENTITY_STATUS_ACTIVE;

    /**
     * Not active entity status
     */
    CONST ENTITY_STATUS_NOT_ACTIVE = BASE_CLASS_AbstractSearchStorage::ENTITY_STATUS_NOT_ACTIVE;

    use OW_Singleton;

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
     * @param integer $timeStamp
     * @param array $tags
     * @param string $status
     * @throws Exception
     * @return void
     */
    public function addEntity( $entityType, $entityId, $text, $timeStamp, array $tags = array(), $status = null )
    {
        $this->defaultStorageInstance->addEntity($entityType, $entityId, $text, $timeStamp, $tags, $status);

        if ( $this->activeStorageInstance )
        {
            $this->activeStorageInstance->addEntity($entityType, $entityId, $text, $timeStamp, $tags, $status);
        }
    }

    /**
     * Set entities status
     * 
     * @param string $entityType
     * @param integer $entityId
     * @param string $status
     * @throws Exception
     * @return void
     */
    public function setEntitiesStatus( $entityType, $entityId, $status = self::ENTITY_STATUS_ACTIVE )
    {
        $this->defaultStorageInstance->setEntitiesStatus($entityType, $entityId, $status);

        if ( $this->activeStorageInstance )
        {
            $this->activeStorageInstance->setEntitiesStatus($entityType, $entityId, $status);
        }
    }

    /**
     * Set entities status by tags
     * 
     * @param array $tags
     * @param string $status
     * @throws Exception
     * @return void
     */
    public function setEntitiesStatusByTags( array $tags, $status = self::ENTITY_STATUS_ACTIVE )
    {
        $this->defaultStorageInstance->setEntitiesStatusByTags($tags, $status);

        if ( $this->activeStorageInstance )
        {
            $this->activeStorageInstance->setEntitiesStatusByTags($tags, $status);
        }
    }

    /**
     * Delete entity
     *
     * @param string $entityType
     * @param integer $entityId
     * @throws Exception
     * @return void
     */
    public function deleteEntity( $entityType, $entityId )
    {
        $this->defaultStorageInstance->deleteEntity($entityType, $entityId);

        if ( $this->activeStorageInstance )
        {
            $this->activeStorageInstance->deleteEntity($entityType, $entityId);
        }
    }

    /**
     * Delete all entities
     *
     * @param string $entityType
     * @throws Exception
     * @return void
     */
    public function deleteAllEntities( $entityType = null )
    {
        $this->defaultStorageInstance->deleteAllEntities($entityType);

        if ( $this->activeStorageInstance )
        {
            $this->activeStorageInstance->deleteAllEntities($entityType);
        }
    }

    /**
     * Delete all entities by tags
     *
     * @param array $tags
     * @throws Exception
     * @return void
     */
    public function deleteAllEntitiesByTags( array $tags )
    {
        $this->defaultStorageInstance->deleteAllEntitiesByTags($tags);

        if ( $this->activeStorageInstance )
        {
            $this->activeStorageInstance->deleteAllEntitiesByTags($tags);
        }   
    }

    /**
     * Deactivate all entities
     *
     * @param string $entityType
     * @throws Exception
     * @return void
     */
    public function deactivateAllEntities( $entityType = null )
    {
        $this->defaultStorageInstance->deactivateAllEntities($entityType);

        if ( $this->activeStorageInstance )
        {
            $this->activeStorageInstance->deactivateAllEntities($entityType);
        }
    }
    
    /**
     * Deactivate all entities by tags
     *
     * @param array $tags
     * @throws Exception
     * @return void
     */
    public function deactivateAllEntitiesByTags( array $tags )
    {
        $this->defaultStorageInstance->deactivateAllEntitiesByTags($tags);

        if ( $this->activeStorageInstance )
        {
            $this->activeStorageInstance->deactivateAllEntitiesByTags($tags);
        }
    }

    /**
     * Activate all entities
     *
     * @param string $entityType
     * @throws Exception
     * @return void
     */
    public function activateAllEntities( $entityType = null )
    {
        $this->defaultStorageInstance->activateAllEntities($entityType);

        if ( $this->activeStorageInstance )
        {
            $this->activeStorageInstance->activateAllEntities($entityType);
        }
    }

    /**
     * Activate all entities by tags
     *
     * @param array $tags
     * @throws Exception
     * @return void
     */
    public function activateAllEntitiesByTags( array $tags )
    {
        $this->defaultStorageInstance->activateAllEntitiesByTags($tags);

        if ( $this->activeStorageInstance )
        {
            $this->activeStorageInstance->activateAllEntitiesByTags($tags);
        }
    }

    /**
     * Search entities
     *
     * @param string $text
     * @param integer $first
     * @param integer $limit
     * @param array $tags
     * @param string $sort
     * @param boolean $sortDesc
     * @param integer $timeStart
     * @param integer $timeEnd
     * @throws Exception
     * @return array
     */
    public function searchEntities( $text, $first, $limit, 
            array $tags = array(), $sort = self::SORT_BY_RELEVANCE, $sortDesc = true, $timeStart = 0, $timeEnd = 0 )
    {
        if ( $this->activeStorageInstance )
        {
            return $this->activeStorageInstance->
                    searchEntities($text, $first, $limit, $tags, $sort, $sortDesc, $timeStart, $timeEnd);
        }

        return $this->defaultStorageInstance->
                    searchEntities($text, $first, $limit, $tags, $sort, $sortDesc, $timeStart, $timeEnd);
    }

    /**
     * Search entities count
     *
     * @param string $text
     * @param array $tags
     * @param integer $timeStart
     * @param integer $timeEnd
     * @throws Exception
     * @return integer
     */
    public function searchEntitiesCount( $text, array $tags = array(), $timeStart = 0, $timeEnd = 0 )
    {
        if ( $this->activeStorageInstance )
        {
            return $this->activeStorageInstance->
                    searchEntitiesCount($text, $tags, $timeStart, $timeEnd);
        }

        return $this->defaultStorageInstance->
                searchEntitiesCount($text, $tags, $timeStart, $timeEnd);
    }

    /**
     * Search entities by tags
     *
     * @param array $tags
     * @param integer $first
     * @param integer $limit     
     * @param string $sort
     * @param boolean $sortDesc
     * @param integer $timeStart
     * @param integer $timeEnd
     * @throws Exception
     * @return array
     */
    public function searchEntitiesByTags( array $tags, $first, $limit, 
            $sort = self::SORT_BY_DATE, $sortDesc = true, $timeStart = 0, $timeEnd = 0 )
    {
        if ( $this->activeStorageInstance )
        {
            return $this->activeStorageInstance->
                    searchEntitiesByTags($tags, $first, $limit, $sort, $sortDesc, $timeStart, $timeEnd);
        }

        return $this->defaultStorageInstance->
                    searchEntitiesByTags($tags, $first, $limit, $sort, $sortDesc, $timeStart, $timeEnd);
    }

    /**
     * Search entities count by tags
     *
     * @param array $tags
     * @param integer $timeStart
     * @param integer $timeEnd
     * @throws Exception
     * @return integer
     */
    public function searchEntitiesCountByTags( array $tags, $timeStart = 0, $timeEnd = 0 )
    {
        if ( $this->activeStorageInstance )
        {
            return $this->activeStorageInstance->
                    searchEntitiesCountByTags($tags, $timeStart, $timeEnd);
        }

        return $this->defaultStorageInstance->
                searchEntitiesCountByTags($tags, $timeStart, $timeEnd);
    }

    /**
     * Get all entities
     *
     * @param integer $first
     * @param integer $limit
     * @param string $entityType
     * @throws Exception
     * @return array
     */
    public function getAllEntities( $first, $limit, $entityType = null )
    {
        return $this->defaultStorageInstance->getAllEntities($first, $limit, $entityType);
    }
}
