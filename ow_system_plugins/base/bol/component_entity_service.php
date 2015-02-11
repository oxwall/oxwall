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
 * Widget Entity Service
 *
 * @author Sergey Kambalin <greyexpert@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_ComponentEntityService extends BOL_ComponentService
{
    /**
     * @var BOL_ComponentEntityPositionDao
     */
    private $componentPositionDao;
    /**
     * @var BOL_ComponentEntitySettingDao
     */
    private $componentSettingDao;
    /**
     * @var BOL_PlaceSchemeDao
     */
    private $placeSchemeDao;
    /**
     *
     * @var BOL_ComponentEntityPlaceDao
     */
    private $componentPlaceDao;

    protected function __construct()
    {
        parent::__construct();

        $this->componentPositionDao = BOL_ComponentEntityPositionDao::getInstance();
        $this->componentSettingDao = BOL_ComponentEntitySettingDao::getInstance();
        $this->placeSchemeDao = BOL_PlaceEntitySchemeDao::getInstance();
        $this->componentPlaceDao = BOL_ComponentEntityPlaceDao::getInstance();
    }
    /**
     * Class instance
     *
     * @var BOL_ComponentEntityService
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return BOL_ComponentEntityService
     */
    public static function getInstance()
    {
        if ( !isset(self::$classInstance) )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function findComponentPlace( $componentPlaceUniqName, $entityId )
    {
        $componentPlace = $this->componentPlaceDao->findByUniqName($componentPlaceUniqName, $entityId);
        if ( $componentPlace === null )
        {
            $componentPlace = BOL_ComponentPlaceDao::getInstance()->findByUniqName($componentPlaceUniqName);
        }

        return $componentPlace;
    }

    public function findPlaceComponentList( $place, $entityId )
    {
        $placeId = $this->findPlaceId($place);
        $list = $this->componentPlaceDao->findComponentList($placeId, $entityId);

        return $this->fetchArrayList($list, 'uniqName');
    }

    public function cloneComponentPlace( $componentPlaceUniqName, $entityId )
    {
        $defaultComponentPlaceDao = BOL_ComponentPlaceDao::getInstance();
        $defaultComponentSettingDao = BOL_ComponentSettingDao::getInstance();

        /* @var $componentPlaceDto BOL_ComponentPlace */
        $componentPlaceDto = $defaultComponentPlaceDao->findByUniqName($componentPlaceUniqName);
        $componentEntityPlaceDto = new BOL_ComponentEntityPlace();
        $componentEntityPlaceDto->entityId = $entityId;
        $componentEntityPlaceDto->clone = 1;
        $componentEntityPlaceDto->componentId = $componentPlaceDto->componentId;
        $componentEntityPlaceDto->uniqName = uniqid('entity-');
        $componentEntityPlaceDto->placeId = $componentPlaceDto->placeId;

        $this->componentPlaceDao->save($componentEntityPlaceDto);

        $defaultComponentSettings = $defaultComponentSettingDao->findSettingList($componentPlaceUniqName);

        foreach ( $defaultComponentSettings as $setting )
        {
            $newSettingDto = new BOL_ComponentEntitySetting();
            $newSettingDto->name = $setting->name;
            $newSettingDto->componentPlaceUniqName = $componentEntityPlaceDto->uniqName;
            $newSettingDto->entityId = $entityId;
            $newSettingDto->value = $setting->value;

            $this->componentSettingDao->save($newSettingDto);
        }

        return $componentEntityPlaceDto;
    }

    public function findAllSettingList( $entityId )
    {
        $dtoList = $this->componentSettingDao->findAllEntitySettingList($entityId);

        return $this->fetchSettingList($dtoList);
    }

    public function findSettingList( $componentPlaceUniqName, $entityId, $settingList = array() )
    {
        $dtoList = $this->componentSettingDao->findSettingList($componentPlaceUniqName, $entityId, $settingList);

        return $this->fetchSettingList($dtoList, $componentPlaceUniqName);
    }

    public function saveComponentSettingList( $componentPlaceUniqName, $entityId, array $settingList )
    {
        foreach ( $settingList as $name => $value )
        {
            $this->componentSettingDao->saveSetting($componentPlaceUniqName, $entityId, $name, $value);
        }
    }

    public function findAllPositionList( $place, $entityId )
    {
        $placeId = $this->findPlaceId($place);
        $dtoList = $this->componentPositionDao->findAllPositionList($placeId, $entityId);

        return $this->fetchArrayList($dtoList, 'componentPlaceUniqName');
    }

    public function clearSection( $place, $entityId, $section )
    {
        $placeId = $this->findPlaceId($place);
        $componentPositionIds = $this->componentPositionDao->findSectionPositionIdList($placeId, $entityId, $section);

        return $this->componentPositionDao->deleteByIdList($componentPositionIds);
    }

    public function saveSectionPositionStack( $entityId, $section, array $componentPlaceStack )
    {

        for ( $i = 0; $i < count($componentPlaceStack); $i++ )
        {
            $dtoPosition = new BOL_ComponentEntityPosition();
            $dtoPosition->componentPlaceUniqName = $componentPlaceStack[$i];
            $dtoPosition->order = $i;
            $dtoPosition->section = $section;
            $dtoPosition->entityId = $entityId;

            $this->componentPositionDao->save($dtoPosition);
        }
    }

    public function moveComponentPlaceFromDefault( $componentPlaceUniqName, $entityId )
    {
        $existingComponent = $this->componentPlaceDao->findByUniqName($componentPlaceUniqName, $entityId);
        if ( $existingComponent !== null )
        {
            return $existingComponent;
        }

        $defaultComponentPlaceDao = BOL_ComponentPlaceDao::getInstance();

        /* @var $componentPlaceDto BOL_ComponentPlace */
        $componentPlaceDto = $defaultComponentPlaceDao->findByUniqName($componentPlaceUniqName);
        $componentEntityPlaceDto = new BOL_ComponentEntityPlace();
        $componentEntityPlaceDto->entityId = $entityId;
        $componentEntityPlaceDto->clone = $componentPlaceDto->clone;
        $componentEntityPlaceDto->componentId = $componentPlaceDto->componentId;
        $componentEntityPlaceDto->uniqName = $componentPlaceDto->uniqName;
        $componentEntityPlaceDto->placeId = $componentPlaceDto->placeId;

        $newComponent = $this->componentPlaceDao->save($componentEntityPlaceDto);

        return $newComponent;
    }

    public function deletePlaceComponent( $componentPlaceUniqName, $entityId )
    {
        $placeDto = $this->findComponentPlace($componentPlaceUniqName, $entityId);
        if ( $placeDto === null )
        {
            return;
        }

        $component = $this->findComponent($placeDto->componentId);

        $event = new OW_Event('widgets.before_place_delete', array(
            'class' => $component->className,
            'uniqName' => $placeDto->uniqName,
            'entityId' => $entityId
        ));

        OW::getEventManager()->trigger($event);

        $this->componentPlaceDao->deleteByUniqName($componentPlaceUniqName, $entityId);
        $this->componentSettingDao->deleteList($componentPlaceUniqName, $entityId);
    }

    public function savePlaceScheme( $place, $entityId, $schemeId )
    {
        $placeId = $this->findPlaceId($place);
        $placeSchemeDto = $this->placeSchemeDao->findPlaceScheme($placeId, $entityId);

        if ( !$placeSchemeDto )
        {
            $placeSchemeDto = new BOL_PlaceEntityScheme();
            $placeSchemeDto->placeId = $placeId;
            $placeSchemeDto->entityId = $entityId;
        }

        $placeSchemeDto->schemeId = $schemeId;

        $this->placeSchemeDao->save($placeSchemeDto);
    }

    /**
     *
     * @param string $place
     * @return BOL_Scheme
     */
    public function findSchemeByPlace( $place, $entityId )
    {
        $placeId = $this->findPlaceId($place);
        return $this->findSchemeByPlaceId($placeId, $entityId);
    }

    /**
     *
     * @param int $placeId
     * @return BOL_Scheme
     */
    public function findSchemeByPlaceId( $placeId, $entityId )
    {
        $placeSchemeDto = $this->placeSchemeDao->findPlaceScheme($placeId, $entityId);
        if ( !$placeSchemeDto )
        {
            return null;
        }
        return $this->schemeDao->findById($placeSchemeDto->schemeId);
    }

    public function resetCustomization( $place, $entityId )
    {
        $placeId = $this->findPlaceId($place);

        $componentIdList = $this->componentPlaceDao->findAdminComponentIdList($placeId, $entityId);
        $this->componentPlaceDao->deleteByIdList($componentIdList);

        $positionIdList = $this->componentPositionDao->findAllPositionIdList($placeId, $entityId);
        $this->componentPositionDao->deleteByIdList($positionIdList);
    }

    public function onEntityDelete( $place, $entityId )
    {
        $placeId = $this->findPlaceId($place);

        $adminCmps = BOL_ComponentAdminService::getInstance()->findPlaceComponentList($place);
        $entityCmps = $this->findPlaceComponentList($place, $entityId);
        $placeComponents = array_merge($adminCmps, $entityCmps);

        $uniqNames = array();
        foreach ( $placeComponents as $uniqName => $item )
        {
            $uniqNames[] = $uniqName;
        }

        $this->componentPositionDao->deleteByUniqNameList($entityId, $uniqNames);
        $this->componentSettingDao->deleteByUniqNameList($entityId, $uniqNames);
        $this->componentPlaceDao->deleteList($placeId, $entityId);

        $this->componentPlaceCacheDao->deleteCache($placeId, $entityId);
    }
}