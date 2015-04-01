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
 * Data Access Object for `base_search_entity` table.
 * 
 * @author Alex Ermashev <alexermashev@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_SearchEntityDao extends OW_BaseDao
{
    const ENTITY_TYPE = 'entityType';
    const ENTITY_ID = 'entityId';
    const ENTITY_TEXT = 'entityText';
    const ENTITY_ACTIVE = 'entityActive';
    const ENTITY_CREATED = 'entityCreated';

    const ENTITY_ACTIVE_STATE = 1;
    const ENTITY_NOT_ACTIVE_STATE = null;

    /**
     * Singleton instance.
     *
     * @var BOL_SearchEntityDao
     */
    private static $classInstance;

    /**
     * Full text search in boolean mode
     * @var boolean
     */
    private $fullTextSearchBooleanMode = true;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_SearchEntityDao
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
        return 'BOL_SearchEntity';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_search_entity';
    }

    /**
     * Finds entity parts
     *
     * @param string $entityType
     * @param int $entityId
     * @return OW_Entity
     */
    public function findEntityParts( $entityType, $entityId = null)
    {
        $params = array(
            $entityType
        );

        $sql = 'SELECT * FROM ' . $this->getTableName() . ' WHERE `' . self::ENTITY_TYPE . '` = ?';

        if ( $entityId ) 
        {
            $sql .=  ' AND `' . self::ENTITY_ID . '` = ? ';
            $params = array_merge($params, array(
                $entityId
            ));
        }

        return $this->dbo->queryForObjectList($sql, $this->getDtoClassName(), $params);
    }

    /**
     * Finds all entities
     *
     * @param integer $first
     * @param integer $limit 
     * @param string $entityType 
     * @return array
     */
    public function findAllEntities( $first, $limit, $entityType = null )
    {
        $params = array();
        $sql = 'SELECT * FROM ' . $this->getTableName() . ' WHERE 1';

        if ( $entityType ) 
        {
            $sql .=  ' AND `' . self::ENTITY_TYPE . '` = ? ';
            $params = array_merge($params, array(
                $entityType
            ));
        }

        $params = array_merge($params, array(
            $first,
            $limit
        ));

        $sql .= ' LIMIT ?, ?';

        return $this->dbo->queryForList($sql, $params);
    }

    /**
     * Delete all entities
     * 
     * @return void
     */
    public function deleteAllEntities()
    {
        $this->dbo->delete('TRUNCATE TABLE ' . $this->getTableName());
    }

    /**
     * Set entities status
     * 
     * @param string $entityType
     * @param boolean $active
     */
    public function setEntitiesStatus( $entityType = null, $active = true )
    {
        $params = array(
            ($active ? self::ENTITY_ACTIVE_STATE : self::ENTITY_NOT_ACTIVE_STATE)
        );

        $sql = 'UPDATE `' . $this->getTableName() . '` SET `' . self::ENTITY_ACTIVE . '` = ?';

        if ( $entityType ) 
        {
            $sql .=  ' WHERE `' . self::ENTITY_TYPE . '` = ? ';
            $params = array_merge($params, array(
                $entityType
            ));
        }

        $this->dbo->query($sql, $params);
    }

    /**
     * Find entities by text
     * 
     * @param string $searchText
     * @param integer $first
     * @param integer $limit
     * @param array $tags
     * @param boolean $sortByDate - sort by date or by relevance
     * @return array
     */
    public function findEntitiesByText(  $searchText, $first, $limit, array $tags = array(), $sortByDate = false )
    {
        // sql params
        $queryParams = array(
            ':search' => $searchText,
            ':first' => $first,
            ':limit' => $limit,
            ':status' => self::ENTITY_ACTIVE_STATE
        );

        // build the sub query
        $searchFilter = 'MATCH (b.' . self::ENTITY_TEXT . ') AGAINST (:search ' . $this->getFullTextSearchMode() . ')';
        $subQuery  = 'SELECT 
                b.' . self::ENTITY_TYPE . ', 
                b.' . self::ENTITY_ID . ', ' . 
                $searchFilter . ' as relevance';

        // search without tags
        if ( !$tags ) 
        {
            $subQuery .= ' FROM ' . $this->getTableName() . ' b WHERE ' . $searchFilter .  ' AND b.'. self::ENTITY_ACTIVE  . ' = :status';
        }
        else 
        {
            $enityTags = BOL_SearchEntityTagDao::getInstance();

            $subQuery .= ' FROM ' . $enityTags->getTableName() . ' a';
            $subQuery .= ' INNER JOIN ' . $this->getTableName() . ' b';
            $subQuery .= ' ON a.entityId = b.id AND ' . 
                    $searchFilter . ' AND b.'. self::ENTITY_ACTIVE  . ' = :status';

            $subQuery .= ' WHERE a.' . BOL_SearchEntityTagDao::ENTITY_TAG . ' IN (' . $this->dbo->mergeInClause($tags) . ')';
        }

        $subQuery .= ' ORDER BY ' . ($sortByDate ? 'b.' . self::ENTITY_CREATED : 'relevance') . ' DESC';

        // build the primary query
        $query  = 'SELECT DISTINCT ' .  self::ENTITY_TYPE. ', ' . self::ENTITY_ID;
        $query .= ' FROM (' . $subQuery . ') result LIMIT :first, :limit';

        return $this->dbo->queryForList($query, $queryParams);
    }

    /**
     * Get full text search mode
     * 
     * @return string
     */
    protected function getFullTextSearchMode()
    {
        return $this->fullTextSearchBooleanMode ? 'IN BOOLEAN MODE' : 'IN NATURAL LANGUAGE MODE';
    }
}