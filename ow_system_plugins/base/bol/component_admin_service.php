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
 * Widget Admin Service
 *
 * @author Sergey Kambalin <greyexpert@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_ComponentAdminService extends BOL_ComponentService
{
    /**
     * @var BOL_PlaceSchemeDao
     */
    protected $placeSchemeDao;
    /**
     *
     * @var BOL_ComponentPlaceDao
     */
    protected $componentPlaceDao;
    /**
     *
     * @var BOL_ComponentSettingDao
     */
    protected $componentSettingDao;
    /**
     *
     * @var BOL_ComponentPositionDao
     */
    protected $componentPositionDao;


    protected function __construct()
    {
        parent::__construct();

        $this->placeSchemeDao = BOL_PlaceSchemeDao::getInstance();
        $this->componentPlaceDao = BOL_ComponentPlaceDao::getInstance();
        $this->componentPositionDao = BOL_ComponentPositionDao::getInstance();
        $this->componentSettingDao = BOL_ComponentSettingDao::getInstance();
    }
    /**
     * Class instance
     *
     * @var BOL_ComponentAdminService
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return BOL_ComponentAdminService
     */
    public static function getInstance()
    {
        if ( !isset(self::$classInstance) )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }
    #region ComponentPlace

    public function findPlaceComponent( $componentPlaceUniqName )
    {
        return $this->componentPlaceDao->findByUniqName($componentPlaceUniqName);
    }

    public function findPlaceComponentList( $place )
    {
        $placeId = $this->findPlaceId($place);

        $list = $this->componentPlaceDao->findComponentList($placeId);

        return $this->fetchArrayList($list, 'uniqName');
    }

    public function findSectionComponentList( $place, $section )
    {
        $placeId = $this->findPlaceId($place);
        $list = $this->componentPlaceDao->findListBySection($placeId, $section);

        return $this->fetchArrayList($list, 'uniqName');
    }

    public function cloneComponentPlace( $componenPlacetUniqName )
    {
        $newComponent = $this->componentPlaceDao->cloneComponent($componenPlacetUniqName);
        $this->componentSettingDao->cloneSettingList($componenPlacetUniqName, $newComponent->uniqName);

        return $newComponent;
    }

    public function deletePlaceComponent( $componentPlaceUniqName )
    {
        $placeDto = $this->findPlaceComponent($componentPlaceUniqName);
        
        if ( $placeDto === null )
        {
            return;
        }

        $component = $this->findComponent($placeDto->componentId);

        $event = new OW_Event('widgets.before_place_delete', array(
            'class' => $component->className,
            'uniqName' => $placeDto->uniqName
        ));

        OW::getEventManager()->trigger($event);

        $this->componentPlaceDao->deleteByUniqName($componentPlaceUniqName);
        $this->componentSettingDao->deleteList($componentPlaceUniqName);
    }
    #endregion
    #region Settings

    public function findAllSettingList()
    {
        $dtoList = $this->componentSettingDao->findAll();

        return $this->fetchSettingList($dtoList);
    }

    public function findSettingList( $componentPlaceUniqName, $settingList = array() )
    {
        $dtoList = $this->componentSettingDao->findSettingList($componentPlaceUniqName, $settingList);

        return $this->fetchSettingList($dtoList, $componentPlaceUniqName);
    }

    public function findSettingListByComponentPlaceList( array $componentPlaceList )
    {
        $componentPlaceNameList = array();
        foreach ( $componentPlaceList as $item )
        {
            $componentPlaceNameList[] = $item['uniqName'];
        }

        $dtoList = $this->componentSettingDao->findListByComponentUniqNameList($componentPlaceNameList);

        return $this->fetchSettingList($dtoList);
    }

    public function saveComponentSettingList( $componentPlaceUniqName, array $settingList )
    {
        foreach ( $settingList as $name => $value )
        {
            $this->componentSettingDao->saveSetting($componentPlaceUniqName, $name, $value);
        }
    }
    #endregion
    #region Positions

    public function findAllPositionList( $place )
    {
        $placeId = $this->findPlaceId($place);
        $dtoList = $this->componentPositionDao->findAllPositionList($placeId);

        return $this->fetchArrayList($dtoList, 'componentPlaceUniqName');
    }

    public function findSectionPositionList( $place, $section )
    {
        $placeId = $this->findPlaceId($place);
        $dtoList = $this->componentPositionDao->findSectionPositionList($placeId, $section);

        return $this->fetchArrayList($dtoList, 'componentPlaceUniqName');
    }

    public function clearSection( $place, $section )
    {
        $placeId = $this->findPlaceId($place);
        $componentPositionIds = $this->componentPositionDao->findSectionPositionIdList($placeId, $section);

        return $this->componentPositionDao->deleteByIdList($componentPositionIds);
    }

    public function saveSectionPositionStack( $section, array $componentPlaceStack )
    {
        for ( $i = 0; $i < count($componentPlaceStack); $i++ )
        {
            $dtoComponentPositionDto = new BOL_ComponentPosition();
            $dtoComponentPositionDto->componentPlaceUniqName = $componentPlaceStack[$i];
            $dtoComponentPositionDto->order = $i;
            $dtoComponentPositionDto->section = $section;

            $this->componentPositionDao->save($dtoComponentPositionDto);
        }
    }
    #endregion
    #region Sheme

    public function savePlaceScheme( $place, $schemeId )
    {
        $placeId = $this->findPlaceId($place);
        $placeSchemeDto = $this->placeSchemeDao->findPlaceScheme($placeId);

        if ( !$placeSchemeDto )
        {
            $placeSchemeDto = new BOL_PlaceScheme();
            $placeSchemeDto->placeId = $placeId;
        }

        $placeSchemeDto->schemeId = $schemeId;

        $this->placeSchemeDao->save($placeSchemeDto);
    }

    /**
     *
     * @param string $place
     * @return BOL_Scheme
     */
    public function findSchemeByPlace( $place )
    {
        $placeId = $this->findPlaceId($place);

        return $this->findSchemeByPlaceId($placeId);
    }

    /**
     *
     * @param int $placeId
     * @return BOL_Scheme
     */
    public function findSchemeByPlaceId( $placeId )
    {
        $placeSchemeDto = $this->placeSchemeDao->findPlaceScheme($placeId);
        if ( !$placeSchemeDto )
        {
            return null;
        }

        return $this->schemeDao->findById($placeSchemeDto->schemeId);
    }
    #endregion

    public function saveAllowCustomize( $place, $allowed )
    {
        $placeDto = $this->findPlace($place);
        $placeDto->editableByUser = (bool) $allowed;
        $this->placeDao->save($placeDto);
    }
}