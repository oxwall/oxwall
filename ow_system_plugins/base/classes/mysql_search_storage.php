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
     * 
     * @param object $activeStorage
     */
    public function __construct( BASE_CLASS_InterfaceSearchStorage $activeStorage = null ) 
    {
        parent::__construct($activeStorage);

        $this->searchEntityDao = BOL_SearchEntityDao::getInstance();
        $this->searchEntityTagDao = BOL_SearchEntityTagDao::getInstance();
    }

    /**
     * Add entity
     *
     * @param string $type
     * @param integer $id
     * @param string $searchText
     * @param array $tags
     * @param boolean $isActive
     * @return boolean
     */
    public function addEntity( $type, $id, $searchText, array $tags = array(), $isActive = true )
    {
        try 
        {
            $dto = new BOL_SearchEntity;
            $dto->entityType = $type; 
            $dto->entityId   = $id;
            $dto->entityText = $this->cleanSearchText($searchText); 
            $dto->entityCreated = time();
            $dto->entityActive = $isActive 
                    ? BOL_SearchEntityDao::ENTITY_ACTIVE_STATE 
                    : BOL_SearchEntityDao::ENTITY_NOT_ACTIVE_STATE;

            $this->searchEntityDao->save($dto);
            $newEntityId = $dto->id;

            // add tags
            if ( $tags ) 
            {
                foreach ($tags as $tag)
                {
                    $dto = new BOL_SearchEntityTag;
                    $dto->entityTag = $tag;
                    $dto->entityId = $newEntityId;
                    $this->searchEntityTagDao->save($dto);
                }
            }

            if ( $this->activeStorage )
            {
                return $this->activeStorage->addEntity($type, $id, $searchText, $tags, $isActive);
            }
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
     * @param string $type
     * @param integer $id
     * @return boolean
     */
    public function deleteEntity( $type, $id )
    {
        try 
        {
            // get all entity's parts
            if ( null == ($entityParts = $this->searchEntityDao->findEntityParts($type, $id)) ) 
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

            if ( $this->activeStorage )
            {
                return $this->activeStorage->deleteEntity($type, $id);
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
     * @param string $type
     * @return boolean
     */
    public function deleteAllEntities( $type = null )
    {
        try 
        {
            if ( !$type ) 
            {
                // truncate all entities and their tags
                $this->searchEntityDao->deleteAllEntities();
                $this->searchEntityTagDao->deleteAllTags();
            }
            else
            {
                // delete only specific entities
                if ( null != ($entityParts = $this->searchEntityDao->findEntityParts($type)) ) {
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

            if ( $this->activeStorage )
            {
                return $this->activeStorage->deleteAllEntities($type);
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
     * @param string $type
     * @return boolean
     */
    public function deactivateAllEntities( $type = null )
    {
        try 
        {
            $this->searchEntityDao->setEntitiesStatus($type, false);

            if ( $this->activeStorage )
            {
                return $this->activeStorage->deactivateAllEntities($type);
            }
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
     * @param string $type
     * @return boolean
     */
    public function activateAllEntities( $type = null )
    {
        try 
        {
            $this->searchEntityDao->setEntitiesStatus($type);

            if ( $this->activeStorage )
            {
                return $this->activeStorage->activateAllEntities($type);
            }
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
     * @param string $searchText
     * @param integer $first
     * @param integer $limit
     * @param array $tags
     * @param boolean $sortByDate - sort by date or by relevance
     * @return array
     */
    public function searchEntities( $searchText, $first, $limit, array $tags = array(), $sortByDate = false )
    {
        if ( $this->activeStorage )
        {
            return $this->activeStorage->searchEntities($searchText, $first, $limit, $tags, $sortByDate);
        }

        return $this->searchEntityDao->findEntitiesByText($searchText, $first, $limit, $tags, $sortByDate);
    }

    /**
     * Get all entities
     *
     * @param integer $first
     * @param integer $limit
     * @param string $type
     * @return array
     */
    public function getAllEntities(  $first, $limit, $type = null )
    {
         if (null != ($entities = $this->searchEntityDao->findAllEntities($first, $limit, $type)))  
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