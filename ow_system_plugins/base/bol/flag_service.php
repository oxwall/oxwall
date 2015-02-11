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
class BOL_FlagService
{
    /*
     * @type BOL_FlagDao
     */
    private $flagDao;
    /**
     *
     * @var BOL_FlagService
     */
    private static $classInstance;

    /**
     * Enter description here...
     *
     * @return BOL_FlagService
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function __construct()
    {
        $this->flagDao = BOL_FlagDao::getInstance();
    }

    public function addFlag( $entityType, $entityId, $reason, $userId )
    {
        $flagDto = $this->flagDao->findFlag($entityType, $entityId, $userId);
        
        if ( $flagDto === null )
        {
            $flagDto = new BOL_Flag;
        }
        
        $flagDto->userId = $userId;
        $flagDto->entityType = $entityType;
        $flagDto->entityId = $entityId;
        $flagDto->reason = $reason;
        $flagDto->timeStamp = time();
        
        $this->flagDao->save($flagDto);
    }

    public function isFlagged( $entityType, $entityId, $userId )
    {
        return $this->findFlag($entityType, $entityId, $userId) !== null;
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
        return $this->flagDao->findFlag($entityType, $entityId, $userId);
    }
    
    /**
     * 
     * @param array $entityTypes
     * @return array
     */
    public function findFlagsByEntityTypeList( $entityTypes, array $limit = null )
    {
        return $this->flagDao->findByEntityTypeList($entityTypes, $limit);
    }
    
    /**
     * 
     * @param array $entityTypes
     * @return int
     */
    public function findCountForEntityTypeList( $entityTypes )
    {
        return $this->flagDao->findCountForEntityTypeList($entityTypes);
    }
    
    public function getContentGroupsWithCount()
    {
        $contentTypes = $this->getContentTypeListWithCount();
        $contentGroups = BOL_ContentService::getInstance()->getContentGroups(array_keys($contentTypes));
        
        foreach ( $contentGroups as &$group )
        {
            $group["url"] = OW::getRouter()->urlForRoute("base.moderation_flags", array(
                "group" => $group["name"]
            ));
            
            $group["count"] = 0;
            foreach ( $group["entityTypes"] as $entityType )
            {
                $group["count"] += $contentTypes[$entityType]["count"];
            }
        }
        
        return $contentGroups;
    }
    
    public function getContentTypeListWithCount()
    {
        $contentTypes = BOL_ContentService::getInstance()->getContentTypes();
        $entityTypes = array_keys($contentTypes);
        $counts = $this->findCountForEntityTypeList($entityTypes);
        
        $out = array();
        
        foreach ( $counts as $entityType => $count )
        {
            if ( !OW::getUser()->isAuthorized($contentTypes[$entityType]["authorizationGroup"]) )
            {
                continue;
            }
            
            $out[$entityType] = $contentTypes[$entityType];
            $out[$entityType]["count"] = $count;
        }
        
        return $out;
    }
    
    public function deleteFlagList($entityType, array $entityIdList = null)
    {
    	$this->flagDao->deleteFlagList($entityType, $entityIdList);
    }
    
    public function deleteEntityFlags( $entityType, $entityId )
    {
        $this->flagDao->deleteEntityFlags($entityType, $entityId);
    }
    
    public function deleteFlagListByIds( $idList )
    {
        $this->flagDao->deleteByIdList($idList);
    }

    
    /* Backward compatibility methods */
    
    /**
     * 
     * @param type $type
     * @param type $entityId
     */
    public function deleteByTypeAndEntityId( $type, $entityId )
    {
        $this->deleteEntityFlags($type, $entityId);
    }
    
    public function deleteByType( $entityType )
    {
        $this->deleteFlagList($entityType);
    }
}