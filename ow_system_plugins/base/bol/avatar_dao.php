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
 * Data Access Object for `base_avatar` table.
 *
 * @author Egor Bulgakov <egor.bulgakov@gmail.com>
 * @package ow.ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_AvatarDao extends OW_BaseDao
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
     * @var BOL_AvatarDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_AvatarDao
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
        return 'BOL_Avatar';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_avatar';
    }

    protected $cachedItems = array();

    public function clearCahche( $userId )
    {
        unset($this->cachedItems[$userId]);
    }
    
    /**
     * Finds user avatar by userId
     *
     * @param int $userId
     * @param bool $checkCache
     * @return BOL_Avatar
     */
    public function findByUserId( $userId, $checkCache = true )
    {
        $userId = intval($userId);

        if ( !$checkCache || empty($this->cachedItems[$userId]) )
        {
            $example = new OW_Example();
            $example->andFieldEqual('userId', $userId);
            $example->setLimitClause(0, 1);

            $this->cachedItems[$userId] = $this->findObjectByExample($example);
        }

        return $this->cachedItems[$userId];
    }

    /**
     * Get list of avatars
     *
     * @param $idList
     * @return array of BOL_Avatar
     */
    public function getAvatarsList( $idList )
    {
        if ( empty($idList) )
        {
            return array();
        }

        $idList = array_unique(array_map('intval', $idList));

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
            $example = new OW_Example();
            $example->andFieldInArray('userId', $idsToRequire);

            $items = $this->findListByExample($example);
        }

        foreach ( $items as $item )
        {
            $result[] = $item;
            $this->cachedItems[(int) $item->userId] = $item;
        }

        return $result;
    }

}