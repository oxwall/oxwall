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
 * Data Access Object for `base_authorization_moderator_permission` table.
 *
 * @author Nurlan Dzhumakaliev <nurlanj@live.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_AuthorizationModeratorPermissionDao extends OW_BaseDao
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
     * @var BOL_AuthorizationModeratorPermissionDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_AuthorizationModeratorPermissionDao
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
        return 'BOL_AuthorizationModeratorPermission';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_authorization_moderator_permission';
    }

    public function getId( $moderatorId, $groupId )
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('moderatorId', $moderatorId);
        $ex->andFieldEqual('groupId', $groupId);
        $ex->setLimitClause(1, 1);

        return $this->findIdByExample($ex);
    }

    public function deleteAll()
    {
        $this->clearCache();
        $this->dbo->delete('TRUNCATE TABLE ' . $this->getTableName());
    }

    public function findListByGroupId( $groupId )
    {
        $groupId = (int) $groupId;
        $ex = new OW_Example();
        $ex->andFieldEqual('groupId', $groupId);

        return $this->findListByExample($ex);
    }

    public function findListByModeratorId( $moderatorId )
    {
        if ( empty($moderatorId) )
        {
            throw new InvalidArgumentException('$moderatorId is empty');
        }

        $moderatorId = (int) $moderatorId;

        $ex = new OW_Example();

        $ex->andFieldEqual('moderatorId', $moderatorId);

        return $this->findListByExample($ex);
    }

    public function deleteByModeratorId( $moderatorId )
    {
        $this->clearCache();
        $ex = new OW_Example();
        $ex->andFieldEqual('moderatorId', $moderatorId);

        return $this->deleteByExample($ex);
    }

    public function deleteByGroupId( $groupId )
    {
        $this->clearCache();
        $groupId = (int) $groupId;
        $ex = new OW_Example();
        $ex->andFieldEqual('groupId', $groupId);
        $this->deleteByExample($ex);
    }

    protected function clearCache()
    {
        OW::getCacheManager()->clean(array(BOL_AuthorizationActionDao::CACHE_TAG_AUTHORIZATION));
    }

    public function findAll( $cacheLifeTime = 0, $tags = array() )
    {
        return parent::findAll(3600 * 24, array(BOL_AuthorizationActionDao::CACHE_TAG_AUTHORIZATION, OW_CacheManager::TAG_OPTION_INSTANT_LOAD));
    }
}