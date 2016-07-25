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
 * Data Access Object for `user` table.
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_UserDao extends OW_BaseDao
{
    const EMAIL = 'email';
    const USERNAME = 'username';
    const PASSWORD = 'password';
    const JOIN_STAMP = 'joinStamp';
    const ACTIVITY_STAMP = 'activityStamp';
    const CACHE_TAG_ALL_USER_LIST = 'base.all_user_list';
    const CACHE_TAG_FEATURED_LIST = 'base.featured_list';
    const CACHE_TAG_LATEST_LIST = 'base.latest_list';
    const CACHE_LIFE_TIME = 86400; //24 hour
    
    const EVENT_QUERY_FILTER = "base.query.user_filter";

    /**
     * Singleton instance.
     *
     * @var BOL_UserDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_UserDao
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
        return 'BOL_User';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_user';
    }

    
    /**
     * Returns query parts for filtering users ( by default: suspended, not approved, not verified ). 
     * Result array includes strings: join, where, order
     * 
     * @param array $tables
     * @param array $fields
     * @param array $options
     * @return array
     */
    public function getQueryFilter( array $tables, array $fields, $options = array() )
    {
        if ( empty($tables[BASE_CLASS_QueryBuilderEvent::TABLE_USER]) 
                || empty($fields[BASE_CLASS_QueryBuilderEvent::FIELD_USER_ID]) )
        {
            throw new InvalidArgumentException("User table name or key field were not provided.");
        }
        
        $tableAlias = $tables[BASE_CLASS_QueryBuilderEvent::TABLE_USER];
        $keyField = $fields[BASE_CLASS_QueryBuilderEvent::FIELD_USER_ID];
        
        $event = new BASE_CLASS_QueryBuilderEvent(self::EVENT_QUERY_FILTER, array_merge(array(
            "tables" => $tables,
            "fields" => $fields
        ), $options));

        $userTable = "base_user_table_alias";
        $event->addJoin("INNER JOIN `" . $this->getTableName() . "` $userTable ON $userTable.`id` = `$tableAlias`.`$keyField`");

        $suspendTable = "base_user_suspend_table_alias";
        $event->addJoin("LEFT JOIN `" . BOL_UserSuspendDao::getInstance()->getTableName() . "` $suspendTable ON $suspendTable.`userId` = `$userTable`.`id`");
        $event->addWhere("`$suspendTable`.`id` IS NULL");

        if ( OW::getConfig()->getValue('base', 'mandatory_user_approve') )
        {
            $approveTable = "base_user_approve_table_alias";
            $event->addJoin("LEFT JOIN `" . BOL_UserApproveDao::getInstance()->getTableName() . "` $approveTable ON $approveTable.`userId` = `$userTable`.`id`");
            $event->addWhere("`$approveTable`.`id` IS NULL");
        }

        if ( OW::getConfig()->getValue('base', 'confirm_email') )
        {
            $event->addWhere("`$userTable`.`emailVerify` = 1");
        }

        OW::getEventManager()->trigger($event);

        return array(
            "join" => $event->getJoin(),
            "where" => $event->getWhere(),
            "order" => $event->getOrder()
        );
    }

    /**
     * Returns query parts for filtering users ( by default: suspended, not approved, not verified ). 
     * Result array includes strings: join, where, order
     * 
     * @deprecated since version 1.8.1; use getQueryFilter instead
     * 
     * @param string $tableAlias
     * @param string $keyField
     * @param array $params
     * @return array
     */
    public function getUserQueryFilter( $tableAlias, $keyField, $params = array() )
    {
        return $this->getQueryFilter(array(
            BASE_CLASS_QueryBuilderEvent::TABLE_USER => $tableAlias
        ), array(
            BASE_CLASS_QueryBuilderEvent::FIELD_USER_ID => $keyField
        ), $params);
    }
    
    /**
     * Returns user for provided username/email.
     *
     * @param string $var
     * @param string $password
     * @return BOL_User
     */
    public function findUserByUsernameOrEmail( $var )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::USERNAME, trim($var));

        $result = $this->findObjectByExample($example);

        if ( $result !== null )
        {
            return $result;
        }

        $example = new OW_Example();
        $example->andFieldEqual(self::EMAIL, trim($var));

        $result = $this->findObjectByExample($example);

        return $result;
    }

    public function findByUserName( $username )
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('username', $username);

        return $this->findObjectByExample($ex);
    }

    public function findByUseEmail( $email )
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('email', $email);

        return $this->findObjectByExample($ex);
    }

    /**
     * Find latest user ids list
     *
     * @param integer $offset
     * @param integer $count
     * @return array
     */
    public function findLatestUserIdsList( $offset, $count )
    {
        $example = new OW_Example();
        $example->setOrder('joinStamp DESC')
            ->setLimitClause($offset, $count);

        return $this->findIdListByExample($example);
    }

    public function findList( $first, $count, $admin = false )
    {
        if ( $admin === true )
        {
            $ex = new OW_Example();
            $ex->setOrder('joinStamp DESC')
                ->setLimitClause($first, $count);

            return $this->findListByExample($ex);
        }

        $queryParts = $this->getUserQueryFilter("u", "id", array(
            "method" => "BOL_UserDao::findList"
        ));

        $query = "SELECT `u`.*
    		FROM `{$this->getTableName()}` as `u`
    		{$queryParts["join"]}

            WHERE {$queryParts["where"]}
    		ORDER BY " . ( !empty($queryParts["order"]) ? $queryParts["order"] . ", " : "" ) . " `u`.`joinStamp` DESC
    		LIMIT ?,? ";

        $cacheLifeTime = 0;
        $tag = array();

        // cached latest list query
        //TODO: create cache for other pages, now cached only first page.
        if ( $first == 0 )
        {
            //TODO: Enable cache
            $cacheLifeTime = 0;
            $tag = array(self::CACHE_TAG_LATEST_LIST, self::CACHE_TAG_ALL_USER_LIST);
        }

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array($first, $count), $cacheLifeTime, $tag);
    }

    public function findRecentlyActiveList( $first, $count, $admin = false )
    {
        $queryParts = $this->getUserQueryFilter("u", "id", array(
            "method" => "BOL_UserDao::findRecentlyActiveList"
        ));

        $query = "SELECT `u`.* FROM `{$this->getTableName()}` AS `u`"
            . ( $admin === true 
                ? "" 
                : " {$queryParts["join"]} WHERE {$queryParts["where"]} "
            ) .
            "ORDER BY `u`.`activityStamp` DESC" . ( !empty($queryParts["order"]) ? ", " . $queryParts["order"] : "" ) . "
            LIMIT ?,?";

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array($first, $count));
    }

    public function getRecentlyActiveOrderedIdList( $userIdList )
    {
        if ( empty($userIdList) )
        {
            return array();
        }

        $query = "SELECT `u`.id
            FROM `{$this->getTableName()}` as `u`
            WHERE `u`.id IN ( " . $this->dbo->mergeInClause($userIdList) . " )
            ORDER BY `u`.`activityStamp` DESC";

        return $this->dbo->queryForList($query);
    }

    public function count( $isAdmin = false )
    {
        $cacheLifeTime = 0;
        $tag = array();

        if ( $isAdmin == true )
        {
            $query = "SELECT COUNT(*)
	    		FROM `{$this->getTableName()}`";
        }
        else
        {
            $queryParts = $this->getUserQueryFilter("u", "id", array(
                "method" => "BOL_UserDao::count"
            ));

            $query = "SELECT COUNT(*) FROM `{$this->getTableName()}` as `u`
                {$queryParts["join"]} WHERE {$queryParts["where"]}";

            // cached latest list query count
            //TODO: create cache for other pages, now cached only first page.
            $cacheLifeTime = self::CACHE_LIFE_TIME;
            $tag = array(self::CACHE_TAG_LATEST_LIST, self::CACHE_TAG_ALL_USER_LIST);
        }

        return $this->dbo->queryForColumn($query, null, $cacheLifeTime, $tag);
    }

    public function findFeaturedList( $first, $count )
    {
        $queryParts = $this->getUserQueryFilter("u", "id", array(
            "method" => "BOL_UserDao::findFeaturedList"
        ));

        $query = "SELECT `u`.* FROM `{$this->getTableName()}` AS `u`
            {$queryParts["join"]}
            INNER JOIN `" . BOL_UserFeaturedDao::getInstance()->getTableName() . "` AS `f`
                ON( `u`.`id` = `f`.`userId` )
            WHERE {$queryParts["where"]}
            " . ( !empty($queryParts["order"]) ? " ORDER BY " . $queryParts["order"] : "" ) . "
            LIMIT ?,?";

        // cached featured list query
        $cacheLifeTime = 0;
        $tag = array();

        //TODO: create cache for other pages, now cached only first page.
        if ( $first == 0 )
        {
            //TODO: Enable cache
            $cacheLifeTime = 0;
            $tag = array(self::CACHE_TAG_FEATURED_LIST, self::CACHE_TAG_ALL_USER_LIST);
        }

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array($first, $count), $cacheLifeTime, $tag);
    }

    public function countFeatured()
    {
        $queryParts = $this->getUserQueryFilter("u", "id", array(
            "method" => "BOL_UserDao::countFeatured"
        ));

        $query = "SELECT COUNT(*) FROM `{$this->getTableName()}` AS `u`
            {$queryParts["join"]}
            INNER JOIN `" . BOL_UserFeaturedDao::getInstance()->getTableName() . "` AS `f`
                ON( `u`.`id` = `f`.`userId` )
            WHERE {$queryParts["where"]}";

        // cached featured users count query
        $cacheLifeTime = self::CACHE_LIFE_TIME;
        $tag = array(self::CACHE_TAG_FEATURED_LIST, self::CACHE_TAG_ALL_USER_LIST);

        return $this->dbo->queryForColumn($query, null, $cacheLifeTime, $tag);
    }

    public function findOnlineList( $first, $count )
    {
        $queryParts = $this->getUserQueryFilter("u", "id", array(
            "method" => "BOL_UserDao::findOnlineList"
        ));

        $query = "SELECT `u`.* FROM `{$this->getTableName()}` AS `u`
            {$queryParts["join"]}
            INNER JOIN `" . BOL_UserOnlineDao::getInstance()->getTableName() . "` AS `o`
                ON(`u`.`id` = `o`.`userId`)
            WHERE {$queryParts["where"]}
            ORDER BY " . ( !empty($queryParts["order"]) ? $queryParts["order"] . ", " : "" ) . " `o`.`activityStamp` DESC
            LIMIT ?, ?";

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array($first, $count));
    }

    public function countOnline()
    {
        $queryParts = $this->getUserQueryFilter("u", "id", array(
            "method" => "BOL_UserDao::countOnline"
        ));

        $query = "SELECT  COUNT(*) FROM `{$this->getTableName()}` AS `u`
            {$queryParts["join"]}
            INNER JOIN `" . BOL_UserOnlineDao::getInstance()->getTableName() . "` AS `o`
                ON(`u`.`id` = `o`.`userId`)
            WHERE {$queryParts["where"]}";

        return $this->dbo->queryForColumn($query);
    }

    public function findSuspendedList( $first, $count )
    {
        $query = "SELECT `u`.*
            FROM `{$this->getTableName()}` as `u`
            LEFT JOIN `" . BOL_UserSuspendDao::getInstance()->getTableName() . "` as `s`
                ON( `u`.`id` = `s`.`userId` )
            WHERE `s`.`id` IS NOT NULL
            ORDER BY `u`.`activityStamp` DESC
            LIMIT ?,?
        ";

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array($first, $count));
    }

    public function countSuspended()
    {
        $query = "SELECT COUNT(*)
            FROM `{$this->getTableName()}` as `u`
            LEFT JOIN `" . BOL_UserSuspendDao::getInstance()->getTableName() . "` as `s`
                ON( `u`.`id` = `s`.`userId` )
            WHERE `s`.`id` IS NOT NULL
        ";

        return $this->dbo->queryForColumn($query);
    }

    public function findUnverifiedList( $first, $count )
    {
        $query = "
            SELECT `u`.*
            FROM `{$this->getTableName()}` AS `u`
            WHERE `u`.`emailVerify` = 0
            ORDER BY `u`.`activityStamp` DESC
            LIMIT ?, ?
        ";

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array($first, $count));
    }

    public function countUnverified()
    {
        $query = "SELECT COUNT(*)
            FROM `{$this->getTableName()}` as `u`
            WHERE `u`.`emailVerify` = 0
        ";

        return $this->dbo->queryForColumn($query);
    }

    public function findUnapprovedList( $first, $count )
    {
        $query = "SELECT `u`.*
            FROM `{$this->getTableName()}` as `u`
            LEFT JOIN `" . BOL_UserApproveDao::getInstance()->getTableName() . "` as `d`
                ON( `u`.`id` = `d`.`userId` )
            WHERE `d`.`id` IS NOT NULL
            ORDER BY `u`.`activityStamp` DESC
            LIMIT ?,?
        ";

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array($first, $count));
    }

    public function countUnapproved()
    {
        $query = "SELECT COUNT(*)
            FROM `{$this->getTableName()}` as `u`
            LEFT JOIN `" . BOL_UserApproveDao::getInstance()->getTableName() . "` as `d`
                ON( `u`.`id` = `d`.`userId` )
            WHERE `d`.`id` IS NOT NULL
        ";

        return $this->dbo->queryForColumn($query);
    }

    public function replaceAccountTypeForUsers( $oldType, $newType )
    {
        $sql = "UPDATE `{$this->getTableName()}` SET `accountType`=? WHERE `accountType`=?";
        $this->dbo->update($sql, array($newType, $oldType));
    }

    public function findMassMailingUsers( $start, $count, $ignoreUnsubscribe = false, $userRoles = array() )
    {
        $join = '';
        $where = '';

        $queryParts = $this->getUserQueryFilter("u", "id", array(
            "method" => "BOL_UserDao::findMassMailingUsers"
        ));

        if ( $ignoreUnsubscribe !== true )
        {
            $join .= " LEFT JOIN `" . (BOL_PreferenceDataDao::getInstance()->getTableName()) . "` AS `preference`
                    ON (`u`.`id` = `preference`.`userId` AND `preference`.`key` = 'mass_mailing_subscribe') ";
            $where .= " AND  ( `preference`.`value` = 'true' OR `preference`.`id` IS NULL ) ";
        }

        if ( !empty($userRoles) && is_array($userRoles) )
        {
            $join .= " INNER JOIN `" . (BOL_AuthorizationUserRoleDao::getInstance()->getTableName()) . "` AS `userRole`
                    ON (`u`.`id` = `userRole`.`userId`)
                    INNER JOIN `" . (BOL_AuthorizationRoleDao::getInstance()->getTableName()) . "` AS `role`
                        ON (`userRole`.`roleId` = `role`.`id`) ";
            $where .= " AND  ( `role`.`name` IN ( " . OW::getDbo()->mergeInClause($userRoles) . " ) ) ";
        }

        $query = "
            SELECT  DISTINCT `u`.* FROM `{$this->getTableName()}` AS `u`
                {$queryParts["join"]}
                {$join}
            WHERE {$queryParts["where"]} {$where}
            LIMIT :start, :count ";

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array('start' => (int) $start, 'count' => (int) $count));
    }

    public function findMassMailingUserCount( $ignoreUnsubscribe = false, $userRoles = array() )
    {
        $join = '';
        $where = '';

        $queryParts = $this->getUserQueryFilter("u", "id", array(
            "method" => "BOL_UserDao::findMassMailingUserCount"
        ));

        if ( $ignoreUnsubscribe !== true )
        {
            $join .= " LEFT JOIN `" . (BOL_PreferenceDataDao::getInstance()->getTableName()) . "` AS `preference`
                    ON (`u`.`id` = `preference`.`userId` AND `preference`.`key` = 'mass_mailing_subscribe') ";
            $where .= " AND  ( `preference`.`value` = 'true' OR `preference`.`id` IS NULL ) ";
        }

        if ( !empty($userRoles) && is_array($userRoles) )
        {
            $join .= " INNER JOIN `" . (BOL_AuthorizationUserRoleDao::getInstance()->getTableName()) . "` AS `userRole`
                    ON (`u`.`id` = `userRole`.`userId`)
                    INNER JOIN `" . (BOL_AuthorizationRoleDao::getInstance()->getTableName()) . "` AS `role`
                        ON (`userRole`.`roleId` = `role`.`id`) ";
            $where .= " AND  ( `role`.`name` IN ( " . OW::getDbo()->mergeInClause($userRoles) . " ) ) ";
        }

        $query = "
            SELECT  COUNT( DISTINCT `u`.`id`) FROM `{$this->getTableName()}` AS `u`
                {$queryParts["join"]}
                {$join}
            WHERE {$queryParts["where"]}  {$where} ";

        return $this->dbo->queryForColumn($query);
    }

    public function updateEmail( $userId, $email )
    {
        $userId = (int) $userId;
        $email = trim($email);

        $sql = " UPDATE `{$this->getTableName()}` SET email = ? WHERE id = ? LIMIT 1 ";
        $this->dbo->update($sql, array($email, $userId));
    }

    public function updatePassword( $userId, $password )
    {
        $userId = (int) $userId;

        $sql = " UPDATE `{$this->getTableName()}` SET password = ? WHERE id = ? LIMIT 1 ";
        $this->dbo->update($sql, array($password, $userId));
    }

    public function findListByRoleId( $roleId, $first, $count )
    {
        $query = "SELECT `u`.*
    		 FROM `{$this->getTableName()}` as `u`

    		 INNER JOIN `" . BOL_AuthorizationUserRoleDao::getInstance()->getTableName() . "` as `ur`
    		 	ON( `u`.`id` = `ur`.`userId` )
    		 WHERE `ur`.`roleId` = ?
    		 LIMIT ?, ?";

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array($roleId, $first, $count));
    }

    public function countByRoleId( $roleId )
    {
        $query = "SELECT COUNT(*)
    		 FROM `{$this->getTableName()}` as `u`

    		 INNER JOIN `" . BOL_AuthorizationUserRoleDao::getInstance()->getTableName() . "` as `ur`
    		 	ON( `u`.`id` = `ur`.`userId` )
    		 WHERE `ur`.`roleId` = ? ";

        return $this->dbo->queryForColumn($query, array($roleId));
    }

    public function findListByEmailList( $emailList )
    {
        $ex = new OW_Example();
        $ex->andFieldInArray('email', $emailList);

        return $this->findListByExample($ex);
    }

    public function findDisapprovedList( $first, $count )
    {
        $q = "SELECT `u`.* FROM `{$this->getTableName()}` as `u`
    		INNER JOIN `" . BOL_UserApproveDao::getInstance()->getTableName() . '` as `ud`
    			ON(`u`.`id` = `ud`.`userId`)
    		LIMIT ?, ?
    		';

        return $this->dbo->queryForObjectList($q, $this->getDtoClassName(), array((int) $first, (int) $count));
    }

    public function countDisapproved()
    {
        $q = "SELECT COUNT(*) FROM `{$this->getTableName()}` as `u`
    		INNER JOIN `" . BOL_UserApproveDao::getInstance()->getTableName() . '` as `ud`
    			ON(`u`.`id` = `ud`.`userId`)
    		';

        return (int) $this->dbo->queryForColumn($q);
    }

    public function findUnverifyStatusForUserList( $idList )
    {
        $query = "SELECT `id` FROM `" . $this->getTableName() . "`
            WHERE `emailVerify` = 0
            AND `id` IN (" . $this->dbo->mergeInClause($idList) . ")";

        return $this->dbo->queryForColumnList($query);
    }

    public function findUserListByQuestionValues( $questionValues, $first, $count, $isAdmin = false, $aditionalParams = array() )
    {
        $userIdList = $this->findUserIdListByQuestionValues($questionValues, $first, $count, $isAdmin, $aditionalParams);

        if ( count($userIdList) === 0 )
        {
            return array();
        }

        $ex = new OW_Example();
        $ex->andFieldInArray('id', $userIdList);

        return $this->findListByExample($ex);
    }

    public function countUsersByQuestionValues( $questionValues, $isAdmin = false, $aditionalParams = array() )
    {
        $questionNameList = array_keys($questionValues);

        $questions = BOL_QuestionService::getInstance()->findQuestionByNameList($questionNameList);

        $prefix = 'qd';
        $counter = 0;
        $innerJoin = '';
        $where = '';

        foreach ( $questions as $question )
        {
            if ( !empty($questionValues[$question->name]) && $question->name != 'password' )
            {
                if ( $question->base == 1 )
                {
                    $where .= ' AND `user`.`' . $this->dbo->escapeString($question->name) . '` LIKE \'' . $this->dbo->escapeString($questionValues[$question->name]) . '%\'';
                }
                else
                {
                    $params = array(
                        'question' => $question,
                        'value' => $questionValues[$question->name],
                        'prefix' => $prefix . $counter
                    );

                    $event = new BASE_CLASS_QueryBuilderEvent("base.question.search_sql", $params);

                    OW::getEventManager()->trigger($event);

                    $data = $event->getData();

                    if ( !empty($data['join']) || !empty($data['where']) )
                    {
                        $innerJoin .= $event->getJoin();
                        $where .= ' AND ' . $event->getWhere();
                    }
                    else
                    {
                        $questionString = $this->getQuestionWhereString($question, $questionValues[$question->name], $prefix . $counter);

                        if ( !empty($questionString) )
                        {
                            $innerJoin .= " INNER JOIN `" . BOL_QuestionDataDao::getInstance()->getTableName() . "` `" . $prefix . $counter . "`
                                ON ( `user`.`id` = `" . $prefix . $counter . "`.`userId` AND `" . $prefix . $counter . "`.`questionName` = '" . $this->dbo->escapeString($question->name) . "' AND " . $questionString . " ) ";
                        }
                    }
                }

                $counter++;
            }
        }

        if ( !empty($aditionalParams['join']) )
        {
            $innerJoin .= $aditionalParams['join'];
        }

        if ( !empty($aditionalParams['where']) )
        {
            $where .= $aditionalParams['where'];
        }

        if ( !empty($questionValues['accountType']) )
        {
            $where .= " AND `user`.`accountType` = '" . $this->dbo->escapeString($questionValues['accountType']) . "' ";
        }

        $queryParts = $this->getUserQueryFilter("user", "id", array(
            "method" => "BOL_UserDao::countUsersByQuestionValues"
        ));

        $usersTableName = "`{$this->getTableName()}`";
        
        if ( !empty($aditionalParams["limit_users_count"]) && OW_SQL_LIMIT_USERS_COUNT > 0 )
        {
            $orderFieldname = self::ACTIVITY_STAMP;
            $usersTableName = "( SELECT * FROM {$usersTableName} ORDER BY `{$orderFieldname}` DESC LIMIT " . OW_SQL_LIMIT_USERS_COUNT . " )";
        }
        
        $query = "SELECT DISTINCT COUNT(`user`.id) FROM {$usersTableName} `user`
            {$innerJoin}
            {$queryParts["join"]}

            WHERE {$queryParts["where"]} {$where}";

        if ( $isAdmin === true )
        {
            $query = "SELECT DISTINCT COUNT(`user`.`id` ) FROM {$usersTableName} `user`
                {$innerJoin}
                WHERE 1 {$where}";
        }

        return $this->dbo->queryForColumn($query);
    }

    /**
     * Query for provided question values.
     *
     * @param array $questionValues
     * @param int $first
     * @param int $count
     * @param boolean $isAdmin
     * @param boolean $type
     *
     * @return String
     */
    public function findUserIdListByQuestionValuesQuery( $questionValues, $isAdmin = false, $aditionalParams = array() )
    {
        $questionNameList = array_keys($questionValues);

        $questions = BOL_QuestionService::getInstance()->findQuestionByNameList($questionNameList);

        $prefix = 'qd';
        $counter = 0;
        $innerJoin = '';
        $where = '';

        foreach ( $questions as $question )
        {
            if ( !empty($questionValues[$question->name]) && $question->name != 'password' )
            {
                if ( $question->base == 1 )
                {
                    $where .= ' AND `user`.`' . $this->dbo->escapeString($question->name) . '` LIKE \'' . $this->dbo->escapeString($questionValues[$question->name]) . '%\'';
                }
                else
                {
                    $params = array(
                        'question' => $question,
                        'value' => $questionValues[$question->name],
                        'prefix' => $prefix . $counter
                    );

                    $event = new BASE_CLASS_QueryBuilderEvent("base.question.search_sql", $params);

                    OW::getEventManager()->trigger($event);

                    $data = $event->getData();

                    if ( !empty($data['join']) || !empty($data['where']) )
                    {
                        $innerJoin .= $event->getJoin();
                        $where .= ' AND ' . $event->getWhere();
                    }
                    else
                    {
                        $questionString = $this->getQuestionWhereString($question, $questionValues[$question->name], $prefix . $counter);
                        if ( !empty($questionString) )
                        {
                            $innerJoin .= " INNER JOIN `" . BOL_QuestionDataDao::getInstance()->getTableName() . "` `" . $prefix . $counter . "`
                                ON ( `user`.`id` = `" . $prefix . $counter . "`.`userId` AND `" . $prefix . $counter . "`.`questionName` = '" . $this->dbo->escapeString($question->name) . "' AND " . $questionString . " ) ";
                        }
                    }

                    $counter++;
                }
            }
        }

        if ( !empty($aditionalParams['join']) )
        {
            $innerJoin .= $aditionalParams['join'];
        }

        if ( !empty($aditionalParams['where']) )
        {
            $where = $aditionalParams['where'];
        }

        if ( !empty($questionValues['accountType']) )
        {
            $where .= " AND `user`.`accountType` = '" . $this->dbo->escapeString($questionValues['accountType']) . "' ";
        }

        $queryParts = $this->getUserQueryFilter("user", "id", array(
            "method" => "BOL_UserDao::findUserIdListByQuestionValues"
        ));

        $order = '`user`.`activityStamp` DESC';

        if ( !empty($aditionalParams['order']) )
        {
            $order = $aditionalParams['order'];
        }

        $usersTableName = "`{$this->getTableName()}`";

        if ( !empty($aditionalParams["limit_users_count"]) && OW_SQL_LIMIT_USERS_COUNT > 0 )
        {
            $orderFieldname = self::ACTIVITY_STAMP;
            $usersTableName = "( SELECT * FROM {$usersTableName} ORDER BY `{$orderFieldname}` DESC LIMIT " . OW_SQL_LIMIT_USERS_COUNT . " )";
        }

        $distinct = !isset($aditionalParams["distinct"]) || $aditionalParams["distinct"] ? 'DISTINCT' : '';

        $query = "SELECT $distinct `user`.id, `user`.`activityStamp` FROM {$usersTableName} `user`
                {$innerJoin}
                {$queryParts["join"]}

                WHERE {$queryParts["where"]} {$where}
                ORDER BY {$order}";

        if ( $isAdmin === true )
        {
            $query = "SELECT $distinct `user`.id FROM {$usersTableName} `user`
            {$innerJoin}
                WHERE 1 {$where}
                ORDER BY {$order}";
        }

        return $query;
    }

    /**
     * Returns user for provided username/email.
     *
     * @param array $questionValues
     * @param int $first
     * @param int $count
     * @param boolean $isAdmin
     * @param boolean $type
     *
     * @return BOL_User
     */
    public function findUserIdListByQuestionValues( $questionValues, $first, $count, $isAdmin = false, $aditionalParams = array() )
    {
        $query = $this->findUserIdListByQuestionValuesQuery($questionValues,$isAdmin, $aditionalParams);
        return $this->dbo->queryForColumnList($query . " LIMIT :first, :count ", array_merge(array('first' => $first, 'count' => $count)));
    }

    public function findSearchResultList( $listId, $first, $count )
    {
        $userIdList = BOL_SearchService::getInstance()->getUserIdList($listId, $first, $count);

        if ( empty($userIdList) )
        {
            return array();
        }

        $queryParts = $this->getUserQueryFilter("user", "id", array(
            "method" => "BOL_UserDao::findUserIdListByQuestionValues"
        ));

        $sql = "SELECT `user`.* FROM `" . $this->getTableName() . "` `user`
            {$queryParts["join"]}
            WHERE `user`.`id` IN (" . $this->dbo->mergeInClause($userIdList) . ")
            ORDER BY " . (!empty($queryParts["order"]) ? $queryParts["order"] . ", " : "" ) . " `user`.`activityStamp` DESC";

        return $this->dbo->queryForObjectList($sql, $this->getDtoClassName());
    }

    public function getQuestionWhereString( BOL_Question $question, $value, $prefix = '' )
    {
        $result = '';
        $prefix = $this->dbo->escapeString($prefix);

        /* $event = new OW_Event('base.questions_get_search_sql', array(
          'presentation' => $question->presentation,
          'fieldName' => $question->name,
          'value' => $value,
          'tablePrefix' => $prefix,
          'questionDto' => $question
          ));

          OW::getEventManager()->trigger($event);

          $result = $event->getData();

          if ( !empty($result) )
          {
          return $result;
          } */

        switch ( $question->presentation )
        {
            case BOL_QuestionService::QUESTION_PRESENTATION_URL :
            case BOL_QuestionService::QUESTION_PRESENTATION_TEXT :
            case BOL_QuestionService::QUESTION_PRESENTATION_TEXTAREA :
                
                if ( $value === null || $value === "" )
                {
                    return;
                }
                
                $result = " LCASE(`" . $prefix . "`.`textValue`) LIKE '" . $this->dbo->escapeString(strtolower($value)) . "%'";
                break;

            case BOL_QuestionService::QUESTION_PRESENTATION_CHECKBOX :
                $result = " `" . $prefix . "`.`intValue` = " . (boolean) $value;

                break;

            case BOL_QuestionService::QUESTION_PRESENTATION_RADIO :
            case BOL_QuestionService::QUESTION_PRESENTATION_SELECT :
            case BOL_QuestionService::QUESTION_PRESENTATION_FSELECT :

                if ( !empty($value) )
                {
                    if ( is_array($value) )
                    {
                        $result = ' `' . $this->dbo->escapeString($prefix) . '`.`intValue` IN ( ' . $this->dbo->mergeInClause($value) . ') ';
                    }                    
                    else if ( (int) $value > 0 )
                    {
                        $result = ' `' . $this->dbo->escapeString($prefix) . '`.`intValue` & \'' . ((int)$value) . '\' ';
                    }
                }

                break;
            case BOL_QuestionService::QUESTION_PRESENTATION_MULTICHECKBOX :
                
                if ( !empty ( $value ) )
                {
                    if ( is_array($value) )
                    {
                        $result = " `" . $prefix . "`.`intValue` & '" . $this->dbo->escapeString(array_sum($value)) . "'";
                    }                    
                    else if ( (int) $value > 0 )
                    {
                        $result = " `" . $prefix . "`.`intValue` & '" . ((int) $value) . "'";
                    }
                }
                
                
                break;

            case BOL_QuestionService::QUESTION_PRESENTATION_BIRTHDATE :
            case BOL_QuestionService::QUESTION_PRESENTATION_AGE :

                if ( isset($value['from']) && isset($value['to']) )
                {
                    $maxDate = ( date('Y') - (int) $value['from'] ) . '-12-31';
                    $minDate = ( date('Y') - (int) $value['to'] ) . '-01-01';

                    $result = " `" . $prefix . "`.`dateValue` BETWEEN  '" . $this->dbo->escapeString($minDate) . "' AND '" . $this->dbo->escapeString($maxDate) . "'";
                }

                break;

            case BOL_QuestionService::QUESTION_PRESENTATION_DATE :

                $dateFrom = UTIL_DateTime::parseDate($value['from']);
                $dateTo = UTIL_DateTime::parseDate($value['to']);

                if ( isset($dateFrom) )
                {
                    if ( UTIL_Validator::isDateValid($dateFrom[UTIL_DateTime::PARSE_DATE_MONTH], $dateFrom[UTIL_DateTime::PARSE_DATE_DAY], $dateFrom[UTIL_DateTime::PARSE_DATE_YEAR]) )
                    {
                        $valueFrom = $dateFrom[UTIL_DateTime::PARSE_DATE_YEAR] . '-' . $dateFrom[UTIL_DateTime::PARSE_DATE_MONTH] . '-' . $dateFrom[UTIL_DateTime::PARSE_DATE_DAY];
                    }
                }

                if ( isset($dateTo) )
                {
                    if ( UTIL_Validator::isDateValid($dateTo[UTIL_DateTime::PARSE_DATE_MONTH], $dateTo[UTIL_DateTime::PARSE_DATE_DAY], $dateTo[UTIL_DateTime::PARSE_DATE_YEAR]) )
                    {
                        $valueTo = $dateTo[UTIL_DateTime::PARSE_DATE_YEAR] . '-' . $dateTo[UTIL_DateTime::PARSE_DATE_MONTH] . '-' . $dateTo[UTIL_DateTime::PARSE_DATE_DAY];
                    }
                }

                if ( isset($valueFrom) && isset($valueTo) )
                {
                    $result = " `" . $prefix . "`.`dateValue` BETWEEN  '" . $valueFrom . "' AND '" . $valueTo . "'";
                }

                break;
        }

        return $result;
    }

    public function findUserIdListByPreferenceValues( $preferenceValues, $start = 0, $count = 1000 )
    {
        if ( empty($preferenceValues) || !is_array($preferenceValues) )
        {
            return array();
        }

        $sqlList = array();

        foreach ( $preferenceValues as $key => $value )
        {
            $sqlList[$key] = " SELECT d.userId FROM " . (BOL_PreferenceDao::getInstance()->getTableName()) . " p
                LEFT JOIN " . (BOL_PreferenceDataDao::getInstance()->getTableName()) . " d ON ( d.`key` = p.`key` )
                WHERE p.`key` = '" . $this->dbo->escapeString($key) . "' AND ( d.value = '" . $this->dbo->escapeString($value) . "' OR d.value IS NULL AND p.defaultValue = '" . $this->dbo->escapeString($value) . "' ) "
                    . " LIMIT :start, :count ";

            if ( !empty($value) && is_array($value) )
            {
                $sqlList[$key] = " SELECT d.userId FROM " . (BOL_PreferenceDao::getInstance()->getTableName()) . " p
                    LEFT JOIN " . (BOL_PreferenceDataDao::getInstance()->getTableName()) . " d ON ( d.`key` = p.`key` )
                    WHERE p.`key` = '" . $this->dbo->escapeString($key) . "' AND ( d.value IN " . $this->dbo->mergeInClause($value) . " OR d.value IS NULL AND p.defaultValue IN " . $this->dbo->mergeInClause($value) . " ) "
                        . " LIMIT :start, :count ";
            }
        }

        $sqlString = '';

        $queryNumber = 0;

        foreach ( $sqlList as $sql )
        {
            if ( $queryNumber > 0 )
            {
                $sqlString .= ' UNION ';
            }

            $queryNumber++;
            $sqlString .= $sql;
        }

        return $this->dbo->queryForColumnList($sqlString, array('start' => $start, 'count' => $count));
    }
    protected $cachedItems = array();

    public function findById( $id, $cacheLifeTime = 0, $tags = array() )
    {
        $id = intval($id);
        
        if ( empty($this->cachedItems[$id]) )
        {
            $this->cachedItems[$id] = parent::findById($id, $cacheLifeTime, $tags);
        }

        return $this->cachedItems[$id];
    }
    
    public function findByIdWithoutCache( $id )
    {
        $id = intval($id);
        
        return parent::findById($id);
    }

    public function findByIdList( array $idList, $cacheLifeTime = 0, $tags = array() )
    {
        $idList = array_map('intval', $idList);

        $idsToRequire = array();
        $result = array();

        foreach ( $idList as $id )
        {
            if ( empty($this->cachedItems[$id]) )
            {
                $idsToRequire[] = $id;
            }
            else
            {
                $result[] = $this->cachedItems[$id];
            }
        }

        $items = array();

        if ( !empty($idsToRequire) )
        {
            $items = parent::findByIdList($idsToRequire, $cacheLifeTime, $tags);
        }

        foreach ( $items as $item )
        {
            $result[] = $item;
            $this->cachedItems[(int) $item->getId()] = $item;
        }

        return $result;
    }

    public function findIdListByIdList( array $idList, $cacheLifeTime = 0, $tags = array() )
    {
        $idList = array_map('intval', $idList);

        $idsToRequire = array();
        $result = array();

        foreach ( $idList as $id )
        {
            if ( empty($this->cachedIds[$id]) )
            {
                $idsToRequire[] = $id;
            }
            else
            {
                $result[] = $this->cachedIds[$id];
            }
        }

        $items = array();

        if ( !empty($idsToRequire) )
        {
            $example = new OW_Example();
            $example->andFieldInArray('id', $idsToRequire);
            $items = parent::findIdListByExample($example);
        }

        foreach ( $items as $item )
        {
            $result[] = $item;
            $this->cachedIds[(int) $item] = (int)$item;
        }

        return $result;
    }
}