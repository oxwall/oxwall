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
 *
 * @author Sergey Kambalin <greyexpert@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_FlagDao extends OW_BaseDao
{
    /**
     *
     * @var BOL_FlagDao
     */
    private static $classInstance;

    /**
     * Enter description here...
     *
     * @return BOL_FlagDao
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_flag';
    }

    public function getDtoClassName()
    {
        return 'BOL_Flag';
    }

    /**
     * 
     * @param string $entityType
     * @param int $entityId
     * @param int $userId
     * @return BOL_Flag
     */
    public function findFlag( $entityType, $entityId, $userId )
    {
        $example = new OW_Example();

        $example->andFieldEqual('entityType', $entityType)
            ->andFieldEqual('entityId', $entityId)
            ->andFieldEqual('userId', $userId);

        return $this->findObjectByExample($example);
    }
    
    /**
     * 
     * @param array $entityTypes
     * @return array
     */
    public function findByEntityTypeList( $entityTypes, array $limit = null )
    {
        $example = new OW_Example();
        $example->andFieldInArray("entityType", $entityTypes);
        
        if ( !empty($limit) )
        {
            $example->setLimitClause($limit[0], $limit[1]);
        }
        
        $example->setOrder("timeStamp DESC");
        
        return $this->findListByExample($example);
    }

    /**
     * 
     * @param string $entityType
     * @return int
     */
    public function countByEntityType( $entityType )
    {
        $example = new OW_Example();
        $example->andFieldEqual("entityType", $entityType);

        return $this->countByExample($example);
    }
    
    /**
     * 
     * @param array $entityTypes
     * @return array
     */
    public function findCountForEntityTypeList( $entityTypes )
    {
        if ( empty($entityTypes) )
        {
            return array();
        }
        
        $query = "SELECT count(DISTINCT `entityId`) `count`, `entityType` "
                    . "FROM `" . $this->getTableName() . "` "
                    . "WHERE `entityType` IN ('" . implode("', '", $entityTypes) . "') "
                    . "GROUP BY `entityType`";
        
        $out = array();
        foreach ( $this->dbo->queryForList($query) as $row )
        {
            $out[$row['entityType']] = $row['count'];
        }
        
        return $out;
    }
    
    public function deleteFlagList( $entityType, array $entityIdList = null )
    {
        $example = new OW_Example();
        $example->andFieldEqual('entityType', $entityType);
        
        if ( !empty($entityIdList) )
        {
            $example->andFieldInArray("entityId", $entityIdList);
        }

        $this->deleteByExample($example);
    }
    
    public function deleteEntityFlags( $entityType, $entityId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('entityType', $entityType);
        $example->andFieldEqual('entityId', $entityId);

        $this->deleteByExample($example);
    }
}