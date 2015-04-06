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
 * MySql search storage
 *
 * @author Alex Ermashev <alexermashev@gmail.com>
 * @package ow_system_plugins.base.classes
 * @since 1.0
 */
class BASE_CLASS_MysqlSearchStorage extends BASE_CLASS_AbstractSearchStorage
{
    /**
     * Search entity dao
     * @var BOL_SearchEntityDao
     */
    private $searchEntityDao;

    /**
     * Search entity tag dao
     * @var BOL_SearchEntityTagDao
     */
    private $searchEntityTagDao;
    
    /**
     *  Class constructor
     */
    public function __construct() 
    {
        $this->searchEntityDao = BOL_SearchEntityDao::getInstance();
        $this->searchEntityTagDao = BOL_SearchEntityTagDao::getInstance();
    }

    /**
     * Add entity
     *
     * @param string $entityType
     * @param integer $entityId
     * @param string $text
     * @param array $tags
     * @return boolean
     */
    public function addEntity( $entityType, $entityId, $text, array $tags = array() )
    {
        try 
        {
            $dto = new BOL_SearchEntity;
            $dto->entityType = $entityType; 
            $dto->entityId   = $entityId;
            $dto->text = $this->cleanSearchText($text); 
            $dto->timeStamp = time();
            $dto->status = BOL_SearchEntityDao::ENTITY_ACTIVE_STATUS;

            $this->searchEntityDao->save($dto);
            $searchEntityId = $dto->id;

            // add tags
            if ( $tags ) 
            {
                foreach ($tags as $tag)
                {
                    $dto = new BOL_SearchEntityTag;
                    $dto->entityTag = $tag;
                    $dto->searchEntityId = $searchEntityId;
                    $this->searchEntityTagDao->save($dto);
                }
            }
        }
        catch ( Exception $e ) 
        {
            return false;
        }

        return true;
    }

    /**
     * Set entity status
     * 
     * @param string $entityType
     * @param integer $entityId
     * @param integer $status
     * @return boolean
     */
    public function setEntityStatus( $entityType, $entityId, $status = self::ENTITY_ACTIVE_STATUS )
    {
        try 
        {
            $this->searchEntityDao->setEntitiesStatus($entityType, $status, $entityId);
        }
        catch ( Exception $e ) 
        {
            return false;
        }

        return true;
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
        try 
        {
            // get all entity's parts
            if ( null == ($entityParts = 
                    $this->searchEntityDao->findEntityParts($entityType, $entityId)) ) 
            {
                return false;
            }

            foreach ( $entityParts as $entity )
            {
                // get tags list
                $tags = $this->searchEntityTagDao->findTags($entity->id);

                // delete assigned tags
                foreach ($tags as $tag) 
                {
                    $this->searchEntityTagDao->deleteById($tag->id);
                }

                // delete an entity part
                $this->searchEntityDao->deleteById($entity->id);
            }
        }
        catch ( Exception $e )
        {
            return false;
        }

        return true;
    }

    /**
     * Delete all entities
     *
     * @param string $entityType
     * @return boolean
     */
    public function deleteAllEntities( $entityType = null )
    {
        try 
        {
            if ( !$entityType ) 
            {
                // truncate all entities and their tags
                $this->searchEntityDao->deleteAllEntities();
                $this->searchEntityTagDao->deleteAllTags();
            }
            else
            {
                // delete only specific entities
                if ( null != ($entityParts = $this->searchEntityDao->findEntityParts($entityType)) ) {
                    foreach ($entityParts as $entity)
                    {
                        // get tags list
                        $tags = $this->searchEntityTagDao->findTags($entity->id);

                        // delete assigned tags
                        foreach ($tags as $tag) 
                        {
                            $this->searchEntityTagDao->deleteById($tag->id);
                        }

                        // delete an entity part
                        $this->searchEntityDao->deleteById($entity->id);
                    }
                }
            }
        }
        catch ( Exception $e ) 
        {
            return false;
        }

        return true;
    }

    /**
     * Deactivate all entities
     *
     * @param string $entityType
     * @return boolean
     */
    public function deactivateAllEntities( $entityType = null )
    {
        try 
        {
            $this->searchEntityDao->setEntitiesStatus($entityType, self::ENTITY_NOT_ACTIVE_STATUS);
        }
        catch ( Exception $e ) 
        {
            return false;
        }

        return true;
    }

    /**
     * Activate all entities
     *
     * @param string $entityType
     * @return boolean
     */
    public function activateAllEntities( $entityType = null )
    {
        try 
        {
            $this->searchEntityDao->setEntitiesStatus($entityType);
        }
        catch ( Exception $e ) 
        {
            return false;
        }

        return true;
    }

    /**
     * Search entities
     *
     * @param string $text
     * @param integer $first
     * @param integer $limit
     * @param array $tags
     * @param string $sort
     * @return array
     */
    public function searchEntities( $text, $first, $limit, array $tags = array(), $sort = self::SORT_BY_RELEVANCE )
    {
        return $this->searchEntityDao->
                findEntitiesByText($text, $first, $limit, $tags, $sort);
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
        return $this->searchEntityDao->findEntitiesCountByText($text, $tags);
    }

    /**
     * Get all entities
     *
     * @param integer $first
     * @param integer $limit
     * @param string $entityType
     * @return array
     */
    public function getAllEntities(  $first, $limit, $entityType = null )
    {
         if (null != ($entities = $this->
                 searchEntityDao->findAllEntities($first, $limit, $entityType)))  
         {
             // get entities' tags
            foreach ($entities as &$entity)
            {
                if (null != ($tags = $this->searchEntityTagDao->findTags($entity['id']))) 
                {
                    foreach ($tags as $tag)
                    {
                        $entity['tags'][] = $tag->entityTag;
                    }
                }
            }
         }

         return $entities;
    }
}