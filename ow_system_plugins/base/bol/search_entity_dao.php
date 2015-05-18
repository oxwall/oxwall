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
    /**
     * Entity type
     */
    const ENTITY_TYPE = 'entityType';

    /**
     * Entity id
     */
    const ENTITY_ID = 'entityId';

    /**
     * Text
     */
    const TEXT = 'text';

    /**
     * Status
     */
    const STATUS = 'status';

    /**
     * Timestamp
     */
    const TIMESTAMP = 'timeStamp';

    /**
     * Activated
     */
    const ACTIVATED = 'activated';

    /**
     * Entity deleted status
     */
    const ENTITY_STATUS_DELETED = 'deleted';

    /**
     * Entity active status
     */
    const ENTITY_STATUS_ACTIVE = 'active';

    /**
     * Entity not active status
     */
    const ENTITY_STATUS_NOT_ACTIVE = 'not_active';

    /**
     * Entity activated
     */
    CONST ENTITY_ACTIVATED = 1;
    
    /**
     * Entity not activated
     */
    CONST ENTITY_NOT_ACTIVATED = 0;

    /**
     * Sort by date
     */
    CONST SORT_BY_DATE = 'date';

    /**
     * Sort by relevance
     */
    CONST SORT_BY_RELEVANCE = 'relevance';

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
     * Finds deleted entities
     *
     * @param integer $limit
     * @return OW_Entity
     */
    public function findDeletedEntities($limit = 1000)
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::STATUS, self::ENTITY_STATUS_DELETED);
        $example->setLimitClause(0, $limit);

        return $this->findListByExample($example);
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
        $params = array(
            self::ENTITY_STATUS_DELETED
        );

        $sql = 'SELECT * FROM ' . $this->getTableName() . ' WHERE status <> ?';

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
     * Set entities status
     * 
     * @param string $entityType
     * @param string $status
     * @param integer $entityId
     * @return void
     */
    public function setEntitiesStatus( $entityType = null, $status = self::ENTITY_STATUS_ACTIVE, $entityId = null )
    {
        $params = array(
            $status,
            self::ENTITY_STATUS_DELETED
        );

        $sql = 'UPDATE `' . $this->
                getTableName() . '` SET `' . self::STATUS . '` = ? WHERE `' . self::STATUS . '` <> ?';

        if ( $entityType ) 
        {
            $sql .=  ' AND `' . self::ENTITY_TYPE . '` = ? ';
            $params = array_merge($params, array(
                $entityType
            ));
        }

        if ( $entityId ) 
        {
            $sql .=  ' AND `' . self::ENTITY_ID . '` = ? ';
            $params = array_merge($params, array(
                $entityId
            ));
        }

        $this->dbo->query($sql, $params);
    }

    /**
     * Change activation status
     * 
     * @param string $entityType
     * @param boolean $activate
     * @return void
     */
    public function changeActivationStatus( $entityType = null, $activate = true )
    {
        $params = array(
            ($activate ? self::ENTITY_ACTIVATED : self::ENTITY_NOT_ACTIVATED)
        );

        $sql = 'UPDATE `' . $this->
                getTableName() . '` SET `' . self::ACTIVATED . '` = ? WHERE 1';

        if ( $entityType ) 
        {
            $sql .=  ' AND `' . self::ENTITY_TYPE . '` = ? ';
            $params = array_merge($params, array(
                $entityType
            ));
        }

        $this->dbo->query($sql, $params);
    }

    /**
     * Change activation status by tags
     * 
     * @param array $tags    
     * @param boolean $activate
     * @return void
     */
    public function changeActivationStatusByTags( array $tags, $activate = true )
    {
        $enityTags = BOL_SearchEntityTagDao::getInstance();

        $params = array(
            ($activate ? self::ENTITY_ACTIVATED : self::ENTITY_NOT_ACTIVATED)
        );

        $query = '
            UPDATE 
                ' . $enityTags->getTableName() . ' AS a
            INNER JOIN
                ' . $this->getTableName() . ' AS b
            ON
                a.' . BOL_SearchEntityTagDao::ENTITY_SEARCH_ID . ' = b.id
            SET 
               b.' . self::ACTIVATED . ' = ?                
            WHERE       
                a.' . BOL_SearchEntityTagDao::ENTITY_TAG . ' IN (' . $this->dbo->mergeInClause($tags) . ')';

         $this->dbo->query($query, $params);
    }

    /**
     * Find entities count by text
     * 
     * @param string $text
     * @param array $tags
     * @param integer $timeStart
     * @param integer $timeEnd
     * @return integer
     */
    public function findEntitiesCountByText(  $text, array $tags = array(), $timeStart = 0, $timeEnd = 0)
    {
        // sql params
        $queryParams = array(
            ':search' => $text,
            ':status' => self::ENTITY_STATUS_ACTIVE,
            ':activated' => self::ENTITY_ACTIVATED
        );

        $subQueryTimeStampFilter = null;

        // filter by timestamp
        if ( $timeStart || $timeEnd )
        {
            if ( $timeStart )
            {
                $queryParams = array_merge($queryParams, array(
                    ':timeStampStart' => $timeStart
                ));

                $subQueryTimeStampFilter .= ' AND b.timeStamp >= :timeStampStart';
            }

            if ( $timeEnd )
            {
                $queryParams = array_merge($queryParams, array(
                    ':timeStampEnd' => $timeEnd
                ));

                $subQueryTimeStampFilter .= ' AND b.timeStamp <= :timeStampEnd';
            }
        }

        // search without tags
        if ( !$tags ) 
        {
            $subQuery = '
                SELECT 
                    b.' . self::ENTITY_TYPE . ', 
                    b.' . self::ENTITY_ID . ' 
                FROM 
                    ' . $this->getTableName() . ' b
                WHERE 
                    b.'. self::STATUS  . ' = :status AND b.' . self::ACTIVATED . ' = :activated' . $subQueryTimeStampFilter . ' 
                        AND
                    MATCH (b.' . self::TEXT . ') AGAINST (:search ' . $this->getFullTextSearchMode() . ')';
        }
        else
        {
            $enityTags = BOL_SearchEntityTagDao::getInstance();

            $subQuery = '
                SELECT 
                    b.' . self::ENTITY_TYPE . ', 
                    b.' . self::ENTITY_ID . ' 
                FROM 
                    ' . $enityTags->getTableName() . ' a
                INNER JOIN
                    ' . $this->getTableName() . ' b
                ON
                    a.' . BOL_SearchEntityTagDao::ENTITY_SEARCH_ID . ' = b.id 
                        AND 
                    b.'. self::STATUS  . ' = :status AND b.' . self::ACTIVATED . ' = :activated' . $subQueryTimeStampFilter . ' 
                        AND
                    MATCH (b.' . self::TEXT . ') AGAINST (:search ' . $this->getFullTextSearchMode() . ')
                WHERE 
                    a.' . BOL_SearchEntityTagDao::ENTITY_TAG . ' IN (' . $this->dbo->mergeInClause($tags) . ')';
        }

        // build main query
        $query = '
            SELECT
                COUNT(*) as rowsCount
            FROM (
                SELECT 
                    DISTINCT ' .  
                    self::ENTITY_TYPE . ', ' . 
                    self::ENTITY_ID . '
                FROM 
                    (' . $subQuery . ') result
            )as rows';

        $result = $this->dbo->queryForRow($query, $queryParams);

        return !empty($result['rowsCount']) ? $result['rowsCount'] : 0;
    }

    /**
     * Find entities by text
     * 
     * @param string $text
     * @param integer $first
     * @param integer $limit
     * @param array $tags
     * @param string $sort
     * @param boolean $sortDesc
     * @param integer $timeStart
     * @param integer $timeEnd
     * @return array
     */
    public function findEntitiesByText(  $text, $first, $limit, 
            array $tags = array(), $sort = self::SORT_BY_RELEVANCE, $sortDesc = true, $timeStart = 0, $timeEnd = 0)
    {
        // sql params
        $queryParams = array(
            ':search' => $text,
            ':first' => $first,
            ':limit' => $limit,
            ':status' => self::ENTITY_STATUS_ACTIVE,
            ':activated' => self::ENTITY_ACTIVATED
        );

        $subQueryTimeStampFilter = null;

        // filter by timestamp
        if ( $timeStart || $timeEnd )
        {
            if ( $timeStart )
            {
                $queryParams = array_merge($queryParams, array(
                    ':timeStampStart' => $timeStart
                ));

                $subQueryTimeStampFilter .= ' AND b.timeStamp >= :timeStampStart';
            }

            if ( $timeEnd )
            {
                $queryParams = array_merge($queryParams, array(
                    ':timeStampEnd' => $timeEnd
                ));

                $subQueryTimeStampFilter .= ' AND b.timeStamp <= :timeStampEnd';
            }
        }

        // search without tags
        if ( !$tags ) 
        {
            $subQuery = '
                SELECT 
                    b.' . self::ENTITY_TYPE . ', 
                    b.' . self::ENTITY_ID . ',
                    MATCH (b.' . self::TEXT . ') AGAINST (:search ' . $this->getFullTextSearchMode() . ') as relevance
                FROM 
                    ' . $this->getTableName() . ' b
                WHERE 
                    b.'. self::STATUS  . ' = :status AND b.' . self::ACTIVATED . ' = :activated' . $subQueryTimeStampFilter . ' 
                        AND
                    MATCH (b.' . self::TEXT . ') AGAINST (:search ' . $this->getFullTextSearchMode() . ')
                ORDER BY 
                    ' . ($sort == self::SORT_BY_DATE ? 'b.' . self::TIMESTAMP : 'relevance') . ($sortDesc ? ' DESC' : null);
        }
        else
        {
            $enityTags = BOL_SearchEntityTagDao::getInstance();

            $subQuery = '
                SELECT 
                    b.' . self::ENTITY_TYPE . ', 
                    b.' . self::ENTITY_ID . ',
                    MATCH (b.' . self::TEXT . ') AGAINST (:search ' . $this->getFullTextSearchMode() . ') as relevance
                FROM 
                    ' . $enityTags->getTableName() . ' a
                INNER JOIN
                    ' . $this->getTableName() . ' b
                ON
                    a.' . BOL_SearchEntityTagDao::ENTITY_SEARCH_ID . ' = b.id 
                        AND 
                    b.'. self::STATUS  . ' = :status AND b.' . self::ACTIVATED . ' = :activated' . $subQueryTimeStampFilter . ' 
                        AND
                    MATCH (b.' . self::TEXT . ') AGAINST (:search ' . $this->getFullTextSearchMode() . ')
                WHERE 
                    a.' . BOL_SearchEntityTagDao::ENTITY_TAG . ' IN (' . $this->dbo->mergeInClause($tags) . ')
                ORDER BY 
                    ' . ($sort == self::SORT_BY_DATE ? 'b.' . self::TIMESTAMP : 'relevance') . ($sortDesc ? ' DESC' : null);
        }

        // build main query
        $query = '
            SELECT 
                DISTINCT ' .  
                self::ENTITY_TYPE . ', ' . 
                self::ENTITY_ID . '
            FROM 
                (' . $subQuery . ') result
            LIMIT 
                :first, 
                :limit';

        return $this->dbo->queryForList($query, $queryParams);
    }

    /**
     * Find entities count by tags
     * 
     * @param array $tags
     * @param integer $timeStart
     * @param integer $timeEnd
     * @return integer
     */
    public function findEntitiesCountByTags(  array $tags, $timeStart = 0, $timeEnd = 0)
    {
        $enityTags = BOL_SearchEntityTagDao::getInstance();

        // sql params
        $queryParams = array(
            ':status' => self::ENTITY_STATUS_ACTIVE,
            ':activated' => self::ENTITY_ACTIVATED
        );

        $subQueryTimeStampFilter = null;

        // filter by timestamp
        if ( $timeStart || $timeEnd )
        {
            if ( $timeStart )
            {
                $queryParams = array_merge($queryParams, array(
                    ':timeStampStart' => $timeStart
                ));

                $subQueryTimeStampFilter .= ' AND b.timeStamp >= :timeStampStart';
            }

            if ( $timeEnd )
            {
                $queryParams = array_merge($queryParams, array(
                    ':timeStampEnd' => $timeEnd
                ));

                $subQueryTimeStampFilter .= ' AND b.timeStamp <= :timeStampEnd';
            }
        }

        $subQuery = '
            SELECT 
                b.' . self::ENTITY_TYPE . ', 
                b.' . self::ENTITY_ID . ' 
            FROM 
                ' . $enityTags->getTableName() . ' a
            INNER JOIN
                ' . $this->getTableName() . ' b
            ON
                a.' . BOL_SearchEntityTagDao::ENTITY_SEARCH_ID . ' = b.id 
                    AND 
                b.'. self::STATUS  . ' = :status AND b.' . self::ACTIVATED . ' = :activated' . $subQueryTimeStampFilter . ' 
            WHERE 
                a.' . BOL_SearchEntityTagDao::ENTITY_TAG . ' IN (' . $this->dbo->mergeInClause($tags) . ')';

        // build the main query
        $query = '
            SELECT
                COUNT(*) as rowsCount
            FROM (
                SELECT 
                    DISTINCT ' .  
                    self::ENTITY_TYPE . ', ' . 
                    self::ENTITY_ID . '
                FROM 
                    (' . $subQuery . ') result
            )as rows';

        $result = $this->dbo->queryForRow($query, $queryParams);

        return !empty($result['rowsCount']) ? $result['rowsCount'] : 0;
    }

    /**
     * Find entities by tags
     * 
     * @param array $tags
     * @param integer $first
     * @param integer $limit
     * @param string $sort
     * @param boolean $sortDesc
     * @param integer $timeStart
     * @param integer $timeEnd
     * @return array
     */
    public function findEntitiesByTags( array $tags, $first, $limit, 
            $sort = self::SORT_BY_DATE, $sortDesc = true, $timeStart = 0, $timeEnd = 0)
    {
        // sql params
        $queryParams = array(
            ':first' => $first,
            ':limit' => $limit,
            ':status' => self::ENTITY_STATUS_ACTIVE,
            ':activated' => self::ENTITY_ACTIVATED
        );

        $subQueryTimeStampFilter = null;

        // filter by timestamp
        if ( $timeStart || $timeEnd )
        {
            if ( $timeStart )
            {
                $queryParams = array_merge($queryParams, array(
                    ':timeStampStart' => $timeStart
                ));

                $subQueryTimeStampFilter .= ' AND b.timeStamp >= :timeStampStart';
            }

            if ( $timeEnd )
            {
                $queryParams = array_merge($queryParams, array(
                    ':timeStampEnd' => $timeEnd
                ));

                $subQueryTimeStampFilter .= ' AND b.timeStamp <= :timeStampEnd';
            }
        }

        $enityTags = BOL_SearchEntityTagDao::getInstance();

        $subQuery = '
            SELECT 
                b.' . self::ENTITY_TYPE . ', 
                b.' . self::ENTITY_ID . '
            FROM 
                ' . $enityTags->getTableName() . ' a
            INNER JOIN
                ' . $this->getTableName() . ' b
            ON
                a.' . BOL_SearchEntityTagDao::ENTITY_SEARCH_ID . ' = b.id 
                    AND 
                b.'. self::STATUS  . ' = :status AND b.' . self::ACTIVATED . ' = :activated' . $subQueryTimeStampFilter . '
            WHERE 
                a.' . BOL_SearchEntityTagDao::ENTITY_TAG . ' IN (' . $this->dbo->mergeInClause($tags) . ')
            ORDER BY 
                b.' . self::TIMESTAMP . ($sortDesc ? ' DESC' : null);

        // build main query
        $query = '
            SELECT 
                DISTINCT ' .  
                self::ENTITY_TYPE . ', ' . 
                self::ENTITY_ID . '
            FROM 
                (' . $subQuery . ') result
            LIMIT 
                :first, 
                :limit';

        return $this->dbo->queryForList($query, $queryParams);
    }

    /**
     * Set entities status by tags
     * 
     * @param array $tags
     * @param string $status
     * @return void
     */
    public function setEntitiesStatusByTags( array $tags, $status )
    {
        $enityTags = BOL_SearchEntityTagDao::getInstance();

        $params = array(
            ':deleted_status' => self::ENTITY_STATUS_DELETED,
            ':status' => $status
        );

        $query = '
            UPDATE 
                ' . $enityTags->getTableName() . ' AS a
            INNER JOIN
                ' . $this->getTableName() . ' AS b
            ON
                a.' . BOL_SearchEntityTagDao::ENTITY_SEARCH_ID . ' = b.id 
                    AND 
                b.'. self::STATUS  . ' <> :deleted_status
            SET 
               b.' . self::STATUS . ' = :status             
            WHERE       
                a.' . BOL_SearchEntityTagDao::ENTITY_TAG . ' IN (' . $this->dbo->mergeInClause($tags) . ')';

         $this->dbo->query($query, $params);
    }

    /**
     * Optimize table
     * 
     * @return void
     */
    public function optimizeTable()
    {
        $this->dbo->query('OPTIMIZE TABLE ' . $this->getTableName());
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