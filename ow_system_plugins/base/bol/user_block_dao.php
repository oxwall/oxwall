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
 * Data Access Object for `user_block` table.  
 * 
 * @author Zarif Safiullin <zaph.saph@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_UserBlockDao extends OW_BaseDao
{
    const USER_ID = 'userId';
    const BLOCKED_USER_ID = 'blockedUserId';
    const CACHE_TAG_BLOCKED_USER = 'base.blocked_user';
    const CACHE_LIFE_TIME = 86400; //24 hour

    /**
     * Constructor.
     */
    protected function __construct()
    {
        parent::__construct();
    }
    /**
     * Singleton instance.
     * 
     * @var BOL_UserOnlineDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_UserOnlineDao
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
        return 'BOL_UserBlock';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_user_block';
    }

    public function findBlockedUserList($userId, $first, $count)
    {
        $queryParts = BOL_UserService::getInstance()->getQueryFilter(array(
            BASE_CLASS_QueryBuilderEvent::TABLE_USER => 'u'
        ), array(
            BASE_CLASS_QueryBuilderEvent::FIELD_USER_ID => 'blockedUserId'
        ), array(
            BASE_CLASS_QueryBuilderEvent::OPTION_METHOD => __METHOD__
        ));

        $query = "SELECT u.* FROM " . $this->getTableName() . " u " . $queryParts["join"]
            . " WHERE " . $queryParts["where"] . " AND u.userId=:userId  LIMIT :lf, :lc";

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array(
            "userId" => $userId,
            "lf" => $first,
            "lc" => $count
        ));
    }

    /**
     * 
     * @param integer $userId
     * @return BOL_UserOnline
     */
    public function findBlockedUser( $userId, $blockedUserId )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::USER_ID, (int) $userId);
        $example->andFieldEqual(self::BLOCKED_USER_ID, (int) $blockedUserId);

        return $this->findObjectByExample($example, BOL_UserBlockDao::CACHE_LIFE_TIME, array(BOL_UserBlockDao::CACHE_TAG_BLOCKED_USER));
    }

    public function findBlockedList( $userId, $userIdList )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::USER_ID, (int) $userId);
        $example->andFieldInArray(self::BLOCKED_USER_ID, $userIdList);

        return $this->findListByExample($example, BOL_UserBlockDao::CACHE_LIFE_TIME, array(BOL_UserBlockDao::CACHE_TAG_BLOCKED_USER));
    }

    public function deleteBlockedUser( $userId, $blockedUserId )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::USER_ID, (int) $userId);
        $example->andFieldEqual(self::BLOCKED_USER_ID, (int) $blockedUserId);

        $this->deleteByExample($example);
    }

    public function countBlockedUsers( $userId )
    {
        $queryParts = BOL_UserService::getInstance()->getQueryFilter(array(
            BASE_CLASS_QueryBuilderEvent::TABLE_USER => 'u'
        ), array(
            BASE_CLASS_QueryBuilderEvent::FIELD_USER_ID => 'blockedUserId'
        ), array(
            BASE_CLASS_QueryBuilderEvent::OPTION_METHOD => __METHOD__
        ));

        $query = "SELECT COUNT(DISTINCT u.blockedUserId) FROM " . $this->getTableName() . " u " . $queryParts["join"]
            . " WHERE " . $queryParts["where"] . " AND u.userId=:userId";

        return $this->dbo->queryForColumn($query, array(
            "userId" => $userId
        ));
    }

    public function deleteByUserId( $userId )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::USER_ID, (int) $userId);
        $this->deleteByExample($example);
        
        $example = new OW_Example();
        $example->andFieldEqual(self::BLOCKED_USER_ID, (int) $userId);
        $this->deleteByExample($example);
    }

    protected function clearCache()
    {
        OW::getCacheManager()->clean(array(BOL_UserBlockDao::CACHE_TAG_BLOCKED_USER));
    }
}