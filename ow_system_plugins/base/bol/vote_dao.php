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
 * Data Access Object for `base_vote` table.  
 * 
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_VoteDao extends OW_BaseDao
{
    const USER_ID = 'userId';
    const ENTITY_ID = 'entityId';
    const ENTITY_TYPE = 'entityType';
    const VOTE = 'vote';
    const TIME_STAMP = 'timeStamp';
    const ACTIVE = 'active';

    /**
     * Singleton instance.
     *
     * @var BOL_VoteDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_VoteDao
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
        return 'BOL_Vote';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_vote';
    }

    /**
     * Returns vote item for user.
     * 
     * @param integer $entityId
     * @param string $entityType
     * @param integer $userId
     * @return BOL_Vote
     */
    public function findUserVote( $entityId, $entityType, $userId )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::ENTITY_ID, $entityId);
        $example->andFieldEqual(self::ENTITY_TYPE, $entityType);
        $example->andFieldEqual(self::USER_ID, $userId);

        return $this->findObjectByExample($example);
    }

    /**
     * Returns vote item for user and items list.
     * 
     * @param array $entityIdList
     * @param string $entityType
     * @param integer $userId
     * @return array
     */
    public function findUserVoteForList( $entityIdList, $entityType, $userId )
    {
        if ( empty($entityIdList) )
        {
            return array();
        }

        $example = new OW_Example();

        $example->andFieldInArray(self::ENTITY_ID, $entityIdList);
        $example->andFieldEqual(self::ENTITY_TYPE, $entityType);
        $example->andFieldEqual(self::USER_ID, $userId);

        return $this->findListByExample($example);
    }

    /**
     * Returns counted votes sum.
     * 
     * @param integer $entityId
     * @param string $entityType
     * @return integer
     */
    public function findTotalVote( $entityId, $entityType )
    {
        $query = "
			SELECT 
				SUM(`" . self::VOTE . "`) AS `sum`,
				COUNT(if(`" . self::VOTE . "`>0, `" . self::VOTE . "`, NULL)) AS `up`,
				COUNT(if(`" . self::VOTE . "`<0, `" . self::VOTE . "`,NULL)) AS `down`
			FROM `" . $this->getTableName() . "`
			WHERE `" . self::ENTITY_ID . "` = :entityId AND `" . self::ENTITY_TYPE . "` = :entityType";

        return $this->dbo->queryForRow($query, array('entityId' => $entityId, 'entityType' => $entityType));
    }

    /**
     * Returns counted votes sum for items list.
     * 
     * @param array $entityIdList
     * @param string $entityType
     * @return array
     */
    public function findTotalVoteForList( $entityIdList, $entityType )
    {
        $query = "
	    SELECT `" . self::ENTITY_ID . "` AS `id`, SUM(`" . self::VOTE . "`) AS `sum`, COUNT(*) AS `count`,
            count(if(`vote` > 0, 1, NULL)) as up,
	    	count(if(`vote` < 0, 1, NULL)) as down
	    FROM `" . $this->getTableName() . "`
	    WHERE `" . self::ENTITY_ID . "` IN (" . $this->dbo->mergeInClause($entityIdList) . ") AND `" . self::ENTITY_TYPE . "` = :entityType
	    GROUP BY `" . self::ENTITY_ID . "`";
        return $this->dbo->queryForList($query, array('entityType' => $entityType));
    }

    public function findMostVotedEntityList( $entityType, $first, $count )
    {
        $query = "SELECT `" . self::ENTITY_ID . "` AS `id`, COUNT(*) as `count`, SUM(`" . self::VOTE . "`) AS `sum`
			FROM " . $this->getTableName() . "
                        WHERE `" . self::ENTITY_TYPE . "` = :entityType AND `" . self::ACTIVE . "` = 1
			GROUP BY `" . self::ENTITY_ID . "`
                        ORDER BY `sum` DESC
                        LIMIT :first, :count";

        return $this->dbo->queryForList($query, array('entityType' => $entityType, 'first' => $first, 'count' => $count));
    }

    public function findMostVotedEntityCount( $entityType )
    {
        $query = "SELECT COUNT(DISTINCT `" . self::ENTITY_ID . "`) from `" . $this->getTableName() . "` WHERE `" . self::ENTITY_TYPE . "` = :entityType AND `" . self::ACTIVE . "` = 1";

        return (int) $this->dbo->queryForColumn($query, array('entityType' => $entityType));
    }

    public function updateEntityStatus( $entityType, $entityId, $status )
    {
        $query = "UPDATE `" . $this->getTableName() . "` SET `" . self::ACTIVE . "` = :status
                WHERE `" . self::ENTITY_TYPE . "` = :entityType AND `" . self::ENTITY_ID . "` = :entityId";

        $this->dbo->query($query, array('status' => $status, 'entityType' => $entityType, 'entityId' => $entityId));
    }

    /**
     * Deletes all votes for entity item.
     *
     * @param integer $entityId
     * @param string $entityType
     */
    public function deleteEntityItemVotes( $entityId, $entityType )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::ENTITY_ID, $entityId);
        $example->andFieldEqual(self::ENTITY_TYPE, $entityType);

        $this->deleteByExample($example);
    }

    public function deleteUserVotes( $userId )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::USER_ID, (int) $userId);

        $this->deleteByExample($example);
    }

    public function deleteByEntityType( $entityType )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::ENTITY_TYPE, trim($entityType));

        $this->deleteByExample($entityType);
    }

    /**
     * Gets all votes for provided entity type and list of entity id
     * 
     * @param array<int> $idList
     * @param string $entityType
     * @return array<BOL_Vote>
     */
    public function getEntityTypeVotes( array $idList, $entityType )
    {
        if ( empty($idList) || empty($entityType) )
        {
            return array();
        }

        $example = new OW_Example();
        $example->andFieldEqual(BOL_VoteDao::ENTITY_TYPE, $entityType);
        $example->andFieldInArray(BOL_VoteDao::ENTITY_ID, $idList);
        $example->andFieldEqual(BOL_VoteDao::ACTIVE, 1);
        return BOL_VoteDao::getInstance()->findListByExample($example);
    }
}
