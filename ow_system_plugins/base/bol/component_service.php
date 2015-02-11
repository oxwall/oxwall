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
 * Widget Service
 *
 * @author Sergey Kambalin <greyexpert@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_ComponentService
{
    const PLACE_DASHBOARD = 'dashboard';
    const PLACE_INDEX = 'index';
    const PLACE_PROFILE = 'profile';

    const SECTION_TOP = 'top';
    const SECTION_BOTTOM = 'bottom';
    const SECTION_LEFT = 'left';
    const SECTION_RIGHT = 'right';
    const SECTION_SIDEBAR = 'sidebar';

    /**
     * @var BOL_PlaceDao
     */
    protected $placeDao;

    /**
     * @var BOL_ComponentDao
     */
    protected $componentDao;

    /**
     * @var BOL_SchemeDao
     */
    protected $schemeDao;

    /**
     * @var BOL_ComponentPlaceCacheDao
     */
    protected $componentPlaceCacheDao;

    private $placeDtoCache = array();

    protected function  __construct()
    {
        $this->placeDao = BOL_PlaceDao::getInstance();
        $this->componentDao = BOL_ComponentDao::getInstance();
        $this->schemeDao = BOL_SchemeDao::getInstance();
        $this->componentPlaceCacheDao = BOL_ComponentPlaceCacheDao::getInstance();
    }


    /**
     *
     * @param string $placeName
     * @return BOL_Place
     */
    public function findPlace($placeName)
    {
        if ( empty($this->placeDtoCache[$placeName]) )
        {
            $this->placeDtoCache[$placeName] = $this->placeDao->findByName($placeName);
        }

        return $this->placeDtoCache[$placeName];
    }
    
    /**
     * 
     * @param string $placeName
     * @param bool $editableByUser
     * @return BOL_Place
     */
    public function saveOrUpdatePlace( $placeName, $editableByUser = 0 )
    {
        $place = $this->findPlace($placeName);
        
        if ( $place === null )
        {
            $place = new BOL_Place;
        }
        
        $place->name = $placeName;
        $place->editableByUser = $editableByUser;
        
        $this->placeDao->save($place);
        
        return $place;
    }

    public function findPlaceId($placeName)
    {
        return $this->findPlace($placeName)->id;
    }
    
    protected function fetchArrayList( $list, $keyField = null )
    {
        if ( empty($list) )
        {
            return array();
        }

        $resultArray = array();
        foreach ( $list as $key => $item )
        {
            $key = empty($keyField)
                ? $key
                : ( is_array($item) ? $item[$keyField] : $item->$keyField );

            $resultArray[$key] = (array) $item;
        }

        return $resultArray;
    }

    protected function fetchSettingList( $dtoList, $componentPlaceUniqName = null )
    {
        if ( empty($dtoList) )
        {
            return array();
        }

        $resultList = array();
        foreach ( $dtoList as $dto )
        {
            $resultList[$dto->componentPlaceUniqName][$dto->name] = $dto->getValue();
        }

        return empty($componentPlaceUniqName) ? $resultList : $resultList[$componentPlaceUniqName];
    }

    /**
     * @return BOL_Component
     */
    public function findComponent( $componentId )
    {
        return $this->componentDao->findById($componentId);
    }

    public function findSchemeList()
    {
        return $this->schemeDao->findAll();
    }

    /**
     *
     * @param $place
     * @return BOL_ComponentPlaceCache
     */
    public function findCache( $place )
    {
        $placeId = $this->findPlaceId($place);

        $cacheDto = $this->componentPlaceCacheDao->findCache($placeId);

        return ($cacheDto !== null) ? json_decode($cacheDto->state, true) : null;
    }

    /**
     *
     * @param $place
     * @param $entityId
     * @return BOL_ComponentPlaceCache
     */
    public function findEntityCache( $place, $entityId )
    {
        $placeId = $this->findPlaceId($place);

        $cacheDto = $this->componentPlaceCacheDao->findCache($placeId, $entityId);

        return ($cacheDto !== null) ? json_decode($cacheDto->state, true) : null;
    }

    public function saveCache( $place, array $state )
    {
        $placeId = $this->findPlaceId($place);

        $cacheDto = $this->componentPlaceCacheDao->findCache($placeId);

        if ( $cacheDto === null )
        {
            $cacheDto = new BOL_ComponentPlaceCache();
            $cacheDto->placeId = $placeId;
            $cacheDto->entityId = 0;
        }

        $cacheDto->state = json_encode($state);
        $this->componentPlaceCacheDao->save($cacheDto);

        return $cacheDto;
    }

    public function saveEntityCache( $place, $entityId, array $state )
    {
        $placeId = $this->findPlaceId($place);

        $cacheDto = $this->componentPlaceCacheDao->findCache($placeId, $entityId);

        if ( $cacheDto === null )
        {
            $cacheDto = new BOL_ComponentPlaceCache();
            $cacheDto->placeId = $placeId;
        }

        $cacheDto->state = json_encode($state);
        $cacheDto->entityId = $entityId;
        $this->componentPlaceCacheDao->save($cacheDto);

        return $cacheDto;
    }

    public function isCacheExists( $place )
    {
        $placeId = $this->findPlaceId($place);

        return $this->componentPlaceCacheDao->findCache($placeId) !== null;
    }

    public function isEntityCacheExists( $place, $entityId )
    {
        $placeId = $this->findPlaceId($place);

        return $this->componentPlaceCacheDao->findCache($placeId, $entityId) !== null;
    }

    public function clearAllCache()
    {
        return $this->componentPlaceCacheDao->deleteAllCache();
    }

    public function clearCache( $place )
    {
        $placeId = $this->findPlaceId($place);

        return $this->componentPlaceCacheDao->deleteCache($placeId);
    }

    public function clearEntityCache( $place, $entityId )
    {
        $placeId = $this->findPlaceId($place);

        return $this->componentPlaceCacheDao->deleteCache($placeId, $entityId);
    }

    /**
     *
     * @param string $componentClass
     * @param bool $isClonable
     * @return BOL_Component
     */
    public function addWidget( $widgetClass, $isClonable = false )
    {
        $isClonable = (bool) $isClonable;
        $widgetClass = trim($widgetClass);

        $componentDto = $this->componentDao->findByClassName($widgetClass);
        if ( $componentDto === null )
        {
            $componentDto = new BOL_Component();
            $componentDto->className = $widgetClass;
        }

        $componentDto->clonable = $isClonable;

        $this->componentDao->save($componentDto);

        return $componentDto;
    }

    /**
     *
     * @param BOL_Component $widget
     * @param string $place
     * @param string $uniqName
     * @return BOL_ComponentPlace
     */
    public function addWidgetToPlace( BOL_Component $widget, $place, $uniqName = null )
    {
        $componentPlaceDao = BOL_ComponentPlaceDao::getInstance();
        $place = trim($place);

        $uniqName = empty($uniqName) ? $place . '-' . $widget->className : $uniqName;
        $componentPlaceDto = $componentPlaceDao->findByUniqName($uniqName);

        if ( $componentPlaceDto !== null )
        {
            return $componentPlaceDto;
        }

        $place = $this->saveOrUpdatePlace($place);
        
        $componentPlaceDto = new BOL_ComponentPlace();
        $componentPlaceDto->clone = false;
        $componentPlaceDto->componentId = $widget->id;
        $componentPlaceDto->uniqName = $uniqName;
        $componentPlaceDto->placeId = $place->id;

        $componentPlaceDao->save($componentPlaceDto);

        $this->componentPlaceCacheDao->deleteCache($place->id);

        return $componentPlaceDto;
    }

    public function addWidgetToPosition(BOL_ComponentPlace $placeWidget, $section, $order = -1)
    {
        $positionDao = BOL_ComponentPositionDao::getInstance();
        $settingsDao = BOL_ComponentSettingDao::getInstance();

        $freezed = false;

        $currentPosition = $positionDao->findByUniqName($placeWidget->uniqName);

        if ($currentPosition !== null)
        {
            throw new LogicException("`$currentPosition->componentPlaceUniqName` is already added to `$currentPosition->section` section");
        }

        $list = $positionDao->findSectionPositionList($placeWidget->placeId, $section);

        $orderList = array();
        $positionIdList = array();

        foreach ($list as $item)
        {
            /* @var $item BOL_ComponentPosition */
            $orderList[$item->componentPlaceUniqName] = $item->order;
            $positionIdList[] = $item->id;
        }

        $freezedList = array();
        $settingList = $settingsDao->findListByComponentUniqNameList(array_keys($orderList));

        foreach ( $settingList as $setting )
        {
            /* @var $setting BOL_ComponentSetting */
            if ( $setting->name == 'freeze' && $setting->value )
            {
                $freezedList[$orderList[$setting->componentPlaceUniqName]] = $setting->componentPlaceUniqName;
                unset($orderList[$setting->componentPlaceUniqName]);
            }
        }

        ksort($freezedList);
        asort($orderList);
        $orderedList = array_keys($orderList);

        $stack = array();
        foreach($orderedList as $key => $uniqName)
        {
            if ($order == $key)
            {
                $stack[] = $placeWidget->uniqName;
            }

            $stack[] = $uniqName;
        }

        if ( $freezed )
        {
            $freezedList[] = $placeWidget->uniqName;
        }
        else if ($order + 1 > count($orderedList) || $order < 0)
        {
            $stack[] = $placeWidget->uniqName;
        }

        $positionDao->deleteByIdList($positionIdList);

        foreach ( $freezedList as $f )
        {
            array_unshift($stack, $f);
        }

        foreach ($stack as $i => $uniqName)
        {
            $dto = new BOL_ComponentPosition();
            $dto->componentPlaceUniqName = $uniqName;
            $dto->order = $i;
            $dto->section = $section;

            $positionDao->save($dto);
        }

        $this->componentPlaceCacheDao->deleteCache($placeWidget->placeId);
    }

    public function deleteWidget( $widgetClass )
    {
        $componentPlaceDao = BOL_ComponentPlaceDao::getInstance();
        $componentDto = $this->componentDao->findByClassName($widgetClass);
        if ( $componentDto === null )
        {
            return;
        }

        $event = new OW_Event('widgets.before_delete', array(
            'class' => $widgetClass
        ));

        OW::getEventManager()->trigger($event);

        $componentPlaceList = $componentPlaceDao->findListByComponentId($componentDto->id);

        $placeList = array();
        $uniqNameList = array();
        foreach ($componentPlaceList as $item)
        {
            $event = new OW_Event('widgets.before_place_delete', array(
                'class' => $widgetClass,
                'uniqName' => $item->uniqName
            ));

            OW::getEventManager()->trigger($event);

            /*@var $item BOL_ComponentPlace */
            $componentPlaceDao->deleteByUniqName($item->uniqName);
            BOL_ComponentEntityPlaceDao::getInstance()->deleteAllByUniqName($item->uniqName);

            BOL_ComponentSettingDao::getInstance()->deleteList($item->uniqName);
            BOL_ComponentEntitySettingDao::getInstance()->deleteAllByUniqName($item->uniqName);

            BOL_ComponentPositionDao::getInstance()->deleteByUniqName($item->uniqName);
            BOL_ComponentEntityPositionDao::getInstance()->deleteAllByUniqName($item->uniqName);

            $this->componentPlaceCacheDao->deleteAllCache($item->placeId);

            $placeList[$item->placeId] = 1;
        }

        $this->componentDao->delete($componentDto);

        foreach ( $placeList as $placeId => $value )
        {
            $this->componentPlaceCacheDao->deleteAllCache($placeId);
        }
    }

    public function deleteWidgetPlace( $uniqName )
    {
        $componentPlaceDao = BOL_ComponentPlaceDao::getInstance();
        $dto = $componentPlaceDao->findByUniqName($uniqName);

        if ( $dto === null )
        {
            return;
        }

        $componentPlaceDao->deleteByUniqName($dto->uniqName);
        BOL_ComponentEntityPlaceDao::getInstance()->deleteAllByUniqName($dto->uniqName);

        BOL_ComponentSettingDao::getInstance()->deleteList($dto->uniqName);
        BOL_ComponentEntitySettingDao::getInstance()->deleteAllByUniqName($dto->uniqName);

        BOL_ComponentPositionDao::getInstance()->deleteByUniqName($dto->uniqName);
        BOL_ComponentEntityPositionDao::getInstance()->deleteAllByUniqName($dto->uniqName);

        $this->componentPlaceCacheDao->deleteAllCache($dto->placeId);

        $this->componentDao->delete($dto);
    }

    public function findByPluginKey( $key )
    {
        return $this->componentDao->findByPluginKey($key);
    }

}