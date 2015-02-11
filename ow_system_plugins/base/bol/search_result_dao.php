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
 * Data Access Object for `base_search_result` table.
 *
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_SearchResultDao extends OW_BaseDao
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
     * @var BOL_SearchDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_Search
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
        return 'BOL_SearchResult';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_search_result';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function saveSearchResult( $searchId, array $idList )
    {
        $query = " INSERT INTO " . $this->getTableName() . " ( searchId, userId, sortOrder ) VALUES ";
        $count = 0;
        $values = '';

        $valuesList = array();

        foreach ( $idList as $order => $userId )
        {
            if ( $count > 0 )
            {
                $values .= ",";
            }

            $values .= " ( ?, ?, ? )";

            $valuesList[] = $searchId;
            $valuesList[] = $userId;
            $valuesList[] = $order;

            $count++;

            if ( $count >= 100 )
            {
                $this->dbo->query($query . $values, $valuesList);
                $count = 0;
                $values = '';
                $valuesList = array();
            }
        }

        if ( $count > 0 )
        {
            $this->dbo->query($query . $values, $valuesList);
        }
    }

    /**
     * Return search result item count
     *
     * @param int $listId
     * @param int $first
     * @param int $count
     * return array
     */
    public function getUserIdList( $listId, $first, $count, $excludeList = array() )
    {
        $example = new OW_Example();
        $example->andFieldEqual('searchId', (int) $listId);
        $example->setOrder(' sortOrder ');
        $example->setLimitClause($first, $count);
        
        if ( !empty($excludeList) )
        {
            $example->andFieldNotInArray('userId', $excludeList);
        }

        $results = $this->findListByExample($example);

        $userIdList = array();

        foreach ( $results as $result )
        {
            $userIdList[] = $result->userId;
        }

        return $userIdList;
    }


    /**
     * Return search result item count
     *
     * @param int $listId
     * return int
     */
    public function countSearchResultItem( $listId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('searchId', (int) $listId);

        return $this->countByExample($example);
    }

    /**
     * Return search result item count
     *
     * @param array $listId
     */
    public function deleteSearchResultItems( array $listId )
    {
        if ( empty($listId) )
        {
            return;
        }

        $example = new OW_Example();
        $example->andFieldInArray('searchId', $listId);

        $this->deleteByExample($example);
    }
}