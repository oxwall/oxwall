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
 * Config service.
 * 
 * @author Sergey Kambalin <greyexpert@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_ContentService
{
    const EVENT_COLLECT_TYPES = "content.collect_types";
    const EVENT_GET_INFO = "content.get_info";
    const EVENT_UPDATE_INFO = "content.update_info";
    const EVENT_DELETE = "content.delete";
    
    const EVENT_AFTER_ADD = "content.after_add";
    const EVENT_AFTER_CHANGE = "content.before_change";
    const EVENT_BEFORE_DELETE = "content.before_delete";
    
    const STATUS_ACTIVE = "active";
    const STATUS_APPROVAL = "approval";
    const STATUS_SUSPENDED = "suspended";
    
    const MODERATION_TOOL_FLAG = "flag";
    const MODERATION_TOOL_APPROVE = "approve";
    
    /**
     * @var BOL_ContentService
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_ContentService
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private $contentTypes = array();
    
    private $contentDataDefaults = array(
        "id" => null,
        "userId" => null,
        "status" => null,
        "title" => null,
        "description" => null,
        "timeStamp" => null,
        "url" => null,
        "html" => null,
        "text" => null,
        "label" => null,
        "image" => array(
            "thumbnail" => null,
            "preview" => null,
            "view" => null,
            "fullsize" => null
        )
    );
    
    private $updateDataDefaults = array(
        "status" => null
    );
    
    private $contentTypeDefaults = array(
        "pluginKey" => null,
        "authorizationGroup" => null,
        "group" => null,
        "groupLabel" => null,
        "entityType" => null,
        "entityLabel" => null,
        "displayFormat" => null,
        "moderation" => array(self::MODERATION_TOOL_FLAG, self::MODERATION_TOOL_APPROVE)
    );
    
    /**
     * Constructor.
     */
    private function __construct()
    {
        $this->contentTypes = $this->collectContentTypes();
    }
    
    private function checkArray( $array, $requiredProps = array() )
    {
        return !array_diff($requiredProps, array_keys(array_filter($array)));
    }
    
    private function collectContentTypes()
    {
        $requiredProperties = array(
            "pluginKey",
            "group",
            "groupLabel",
            "entityType",
            "entityLabel"
        );
        
        $event = new BASE_CLASS_EventCollector(self::EVENT_COLLECT_TYPES);
        OW::getEventManager()->trigger($event);
        
        $types = array();
        
        foreach ( $event->getData() as $typeInfo )
        {
            if ( !$this->checkArray($typeInfo, $requiredProperties) )
            {
                continue;
            }
            
            $typeInfo["authorizationGroup"] = empty($typeInfo["authorizationGroup"])
                    ? $typeInfo["group"]
                    : $typeInfo["authorizationGroup"];
            
            $types[$typeInfo["entityType"]] = array_merge($this->contentTypeDefaults, $typeInfo);
        }
        
        return $types;
    }
    
    /* Public API */
    
    public function _contentDataDefaults()
    {
        return $this->contentDataDefaults;
    }
    
    public function getContentTypes()
    {
        return $this->contentTypes;
    }
    
    public function getContentGroups( array $entityTypes = null )
    {
        $types = $this->getContentTypes();
        $groups = array();
        
        foreach ( $types as $type )
        {
            if ( $entityTypes !== null && !in_array($type["entityType"], $entityTypes) )
            {
                continue;
            }
            
            if ( empty($groups[$type["group"]]) )
            {
                $groups[$type["group"]] = array(
                    "name" => $type["group"],
                    "label" => $type["groupLabel"],
                    "entityTypes" => array()
                );
            }
            
            $groups[$type["group"]]["entityTypes"][] = $type["entityType"];
        }
        
        return $groups;
    }
    
    public function getContentTypeByEntityType( $entityType )
    {
        return empty($this->contentTypes[$entityType]) ? null : $this->contentTypes[$entityType];
    }
    
    public function getContentList( $entityType, array $entityIds )
    {
        $typeInfo = $this->getContentTypeByEntityType($entityType);
        
        $event = new OW_Event(self::EVENT_GET_INFO, array(
            "entityType" => $entityType,
            "entityIds" => $entityIds
        ));
        OW::getEventManager()->trigger($event);
        
        $data = $event->getData();
        $data = empty($data) ? array() : $data;
        
        $out = array();
        foreach ( $entityIds as $entityId )
        {
            if ( empty($data[$entityId]) )
            {
                $out[$entityId] = null;
                
                continue;
            }
            
            $info = $data[$entityId];
            $info["label"] = empty($info["label"]) ? $typeInfo["entityLabel"] : $info["label"];
            
            $out[$entityId] = array_merge($this->contentDataDefaults, $info, array("typeInfo" => $typeInfo));
        }
                
        return $out;
    }
    
    public function getContent( $entityType, $entityId )
    {
        $out = $this->getContentList($entityType, array($entityId));
        
        return $out[$entityId];
    }
    
    public function updateContentList( $entityType, array $entityIds, array $data )
    {
        $dataList = array();
        foreach ( $entityIds as $entityId )
        {
            $dataList[$entityId] = array_merge($this->updateDataDefaults, $data);
        }
        
        $event = new OW_Event(self::EVENT_UPDATE_INFO, array(
            "entityType" => $entityType,
            "entityIds" => array_keys($dataList)
        ), $dataList);
        
        OW::getEventManager()->trigger($event);
    }
    
    public function updateContent( $entityType, $entityId, $data )
    {
        $this->updateContentList($entityType, array($entityId), $data);
    }
    
    public function deleteContentList( $entityType, array $entityIds )
    {
        $event = new OW_Event(self::EVENT_DELETE, array(
            "entityType" => $entityType,
            "entityIds" => $entityIds
        ));
        
        OW::getEventManager()->trigger($event);
    }
    
    public function deleteContent( $entityType, $entityId )
    {
        $this->deleteContentList($entityType, array($entityId));
    }
}