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
 * Abstract search storage
 *
 * @author Alex Ermashev <alexermashev@gmail.com>
 * @package ow_system_plugins.base.classes
 * @since 1.0
 */
abstract class BASE_CLASS_AbstractSearchStorage
{
    /**
     * Sort by date
     */
    CONST SORT_BY_DATE = 'date';

    /**
     * Sort by relevance
     */
    CONST SORT_BY_RELEVANCE = 'relevance';

    /**
     * Active entity status 
     */
    CONST ENTITY_STATUS_ACTIVE = 'active';

    /**
     * Not active entity status
     */
    CONST ENTITY_STATUS_NOT_ACTIVE = 'not_active';

    /**
     * Entity activated
     */
    CONST ENTITY_ACTIVATED = 1;
    
    /**
     * Entity not activated
     */
    CONST ENTITY_NOT_ACTIVATED = 0;

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
    abstract public function addEntity( $entityType, $entityId, $text, $timeStamp, array $tags = array(), $status = null );

    /**
     * Set entities status
     * 
     * @param string $entityType
     * @param integer $entityId
     * @param string $status
     * @throws Exception
     * @return void
     */
    abstract public function setEntitiesStatus( $entityType, $entityId, $status = self::ENTITY_STATUS_ACTIVE );

    /**
     * Set entities status by tags
     * 
     * @param array $tags
     * @param string $status
     * @throws Exception
     * @return void
     */
    abstract public function setEntitiesStatusByTags( array $tags, $status = self::ENTITY_STATUS_ACTIVE );

    /**
     * Delete entity
     *
     * @param string $entityType 
     * @param integer $entityId
     * @throws Exception
     * @return void
     */
    abstract public function deleteEntity( $entityType, $entityId );

    /**
     * Delete all entities
     *
     * @param string $entityType
     * @throws Exception
     * @return void
     */
    abstract public function deleteAllEntities( $entityType = null );

    /**
     * Delete all entities by tags
     *
     * @param array $tags
     * @throws Exception
     * @return void
     */
    abstract public function deleteAllEntitiesByTags( array $tags );

    /**
     * Deactivate all entities
     *
     * @param string $entityType
     * @throws Exception
     * @return void
     */
    abstract public function deactivateAllEntities( $entityType = null );

    /**
     * Deactivate all entities by tags
     *
     * @param array $tags
     * @throws Exception
     * @return void
     */
    abstract public function deactivateAllEntitiesByTags( array $tags );

    /**
     * Activate all entities
     *
     * @param string $entityType
     * @throws Exception
     * @return void
     */
    abstract public function activateAllEntities( $entityType = null );

    /**
     * Activate all entities by tags
     *
     * @param array $tags
     * @throws Exception
     * @return void
     */
    abstract public function activateAllEntitiesByTags( array $tags );

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
    abstract public function searchEntitiesCount( $text, array $tags = array(), $timeStart = 0, $timeEnd = 0);

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
    abstract public function searchEntities( $text, $first, $limit, 
            array $tags = array(), $sort = self::SORT_BY_RELEVANCE, $sortDesc = true, $timeStart = 0, $timeEnd = 0 );

    /**
     * Search entities count by tags
     *
     * @param array $tags
     * @param integer $timeStart
     * @param integer $timeEnd
     * @throws Exception
     * @return integer
     */
    abstract public function searchEntitiesCountByTags( array $tags, $timeStart = 0, $timeEnd = 0);

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
    abstract public function searchEntitiesByTags( array $tags, $first, $limit,
            $sort = self::SORT_BY_DATE, $sortDesc = true, $timeStart = 0, $timeEnd = 0 );

    /**
     * Get all entities
     *
     * @param integer $first
     * @param integer $limit
     * @param string $entityType
     * @throws Exception
     * @return array
     */
    abstract public function getAllEntities( $first, $limit, $entityType = null );

    /**
     * Clean search text
     * 
     * @param string $text
     * @return string
     */
    protected function cleanSearchText( $text )
    {
        return mb_strtolower(trim(preg_replace('/[^\pL\pN]+/u', 
                ' ', str_replace('&nbsp;', ' ', strip_tags($text)))));
    }
}