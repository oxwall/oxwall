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
 * Data Access Object for `base_rate` table.
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_RateDao extends OW_BaseDao
{
    const ENTITY_ID = 'entityId';
    const ENTITY_TYPE = 'entityType';
    const USER_ID = 'userId';
    const SCORE = 'score';
    const UPDATE_TIME_STAMP = 'timeStamp';
    const ACTIVE = 'active';

    /**
     * Singleton instance.
     *
     * @var BOL_RateDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_RateDao
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
        return 'BOL_Rate';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_rate';
    }

    /**
     * Returns rate item for provided entity id, entity type and user id.
     *
     * @param integer $entityId
     * @param string $entityType
     * @param integer $userId
     * @return BOL_Rate
     */
    public function findRate( $entityId, $entityType, $userId )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::ENTITY_ID, $entityId);
        $example->andFieldEqual(self::ENTITY_TYPE, $entityType);
        $example->andFieldEqual(self::USER_ID, $userId);

        return $this->findObjectByExample($example);
    }

    /**
     * Returns entity item rate info.
     *
     * @param integer $entityId
     * @param string $entityType
     * @return array
     */
    public function findEntityItemRateInfo( $entityId, $entityType )
    {
        return $this->dbo->queryForRow("SELECT COUNT(*) as `rates_count`, AVG(`score`) as `avg_score`
			FROM " . $this->getTableName() . " WHERE `entityId` = :entityId AND `entityType` = :entityType
			GROUP BY `entityId`", array('entityId' => $entityId, 'entityType' => $entityType));
    }

    /**
     * Returns rate info for list of entities.
     *
     * @param array $entityIds
     * @param string $entityType
     */
    public function findRateInfoForEntityList( $entityType, $entityIdList )
    {
        if ( empty($entityIdList) )
        {
            return array();
        }

        $query = "SELECT COUNT(*) as `rates_count`, AVG(`score`) as `avg_score`, `" . self::ENTITY_ID . "`
			FROM " . $this->getTableName() . " WHERE `" . self::ENTITY_TYPE . "` = :entityType AND `" . self::ENTITY_ID . "` IN (" . $this->dbo->mergeInClause($entityIdList) . ")
			GROUP BY `entityId`";

        return $this->dbo->queryForList($query, array('entityType' => $entityType));
    }

    public function findMostRatedEntityList( $entityType, $first, $count, $exclude )
    {
        $queryParts = BOL_ContentService::getInstance()->getQueryFilter(array(
            BASE_CLASS_QueryBuilderEvent::TABLE_USER => 'r',
            BASE_CLASS_QueryBuilderEvent::TABLE_CONTENT => 'r'
        ), array(
            BASE_CLASS_QueryBuilderEvent::FIELD_USER_ID => 'userId',
            BASE_CLASS_QueryBuilderEvent::FIELD_CONTENT_ID => 'id'
        ), array(
            BASE_CLASS_QueryBuilderEvent::OPTION_METHOD => __METHOD__,
            BASE_CLASS_QueryBuilderEvent::OPTION_TYPE => $entityType
        ));

        $excludeCond = $exclude ? ' AND `' . self::ENTITY_ID . '` NOT IN (' . $this->dbo->mergeInClause($exclude) . ')' : '';

        $query = 'SELECT `r`.`' . self::ENTITY_ID . '` AS `id`, COUNT(*) as `ratesCount`, AVG(`r`.`score`) as `avgScore`
            FROM `' . $this->getTableName() . '` AS `r`
            ' . $queryParts['join'] . '
            WHERE `r`.`' . self::ENTITY_TYPE . '` = :entityType AND `r`.`' . self::ACTIVE . '` = 1 ' . $excludeCond . ' AND ' . $queryParts['where'] . '
            GROUP BY `r`.`' . self::ENTITY_ID . '`
            ORDER BY `avgScore` DESC, `ratesCount` DESC, MAX(`r`.`timeStamp`) DESC
            LIMIT :first, :count';
        $boundParams = array_merge(array('entityType' => $entityType, 'first' => (int) $first, 'count' => (int) $count), $queryParts['params']);
        return $this->dbo->queryForList($query, $boundParams);
    }

    public function findMostRatedEntityCount( $entityType, $exclude )
    {
        $excludeCond = $exclude ? ' AND `' . self::ENTITY_ID . '` NOT IN (' . $this->dbo->mergeInClause($exclude) . ')' : '';

        $query = "SELECT COUNT(DISTINCT `" . self::ENTITY_ID . "`) from `" . $this->getTableName() .
            "` WHERE `" . self::ENTITY_TYPE . "` = :entityType AND `" . self::ACTIVE . "` = 1" . $excludeCond;

        return (int) $this->dbo->queryForColumn($query, array('entityType' => $entityType));
    }

    public function updateEntityStatus( $entityType, $entityId, $status )
    {
        $query = "UPDATE `" . $this->getTableName() . "` SET `" . self::ACTIVE . "` = :status
                WHERE `" . self::ENTITY_TYPE . "` = :entityType AND `" . self::ENTITY_ID . "` = :entityId";

        $this->dbo->query($query, array('status' => $status, 'entityType' => $entityType, 'entityId' => $entityId));
    }

    /**
     * Deletes rate entries for provided params.
     *
     * @param integer $entityId
     * @param string $entityType
     */
    public function deleteEntityItemRates( $entityId, $entityType )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::ENTITY_ID, (int) $entityId);
        $example->andFieldEqual(self::ENTITY_TYPE, trim($entityType));

        $this->deleteByExample($example);
    }

    /**
     * Deletes rate entries for provided params.
     *
     * @param $userId
     */
    public function deleteUserRates( $userId )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::USER_ID, (int) $userId);

        $this->deleteByExample($example);
    }

    public function deleteByEntityType( $entityType )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::ENTITY_TYPE, trim($entityType));

        $this->deleteByExample($example);
    }

    public function findUserScore( $userId, $entityType, array $entityIdList )
    {
        if ( count($entityIdList) === 0 )
        {
            return array();
        }

        $sql = 'SELECT `' . self::ENTITY_ID . '`, `' . self::SCORE . '`
            FROM `' . $this->getTableName() . '`
            WHERE `' . self::USER_ID . '` = :userId AND
                `' . self::ENTITY_TYPE . '` = :entityType AND
                `' . self::ENTITY_ID . '` IN(' . implode(',', array_map('intval', array_unique($entityIdList))) . ')';

        return $this->dbo->queryForList($sql, array('userId' => $userId, 'entityType' => $entityType));
    }
}