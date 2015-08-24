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
 * Widgets entity panel
 *
 * @author Sergey Kambalin <greyexpert@gmail.com>
 * @package ow_system_plugins.base.components
 * @since 1.0
 */

class BASE_CMP_DragAndDropEntityPanel extends BASE_CMP_DragAndDropFrontendPanel
{
    private $entityScheme;
    private $entitySettingList = array();
    private $entityPositionList = array();
    private $entityComponentList = array();
    private $entityClonedNameList = array();
    private $entityId;

    public function __construct( $placeName, $entityId, array $componentList, $customizeMode, $componentTemplate, $responderController = 'BASE_CTRL_AjaxComponentEntityPanel' )
    {
        $responderController = empty($responderController) ? "BASE_CTRL_AjaxComponentEntityPanel" : $responderController;
        
        parent::__construct($placeName, $componentList, $customizeMode, $componentTemplate, $responderController);

        $this->entityId = (int) $entityId;
        $this->assign('entityId', $this->entityId);
        $this->sharedData['entity'] = $this->entityId;
        
        $this->setSettingsClassName("BASE_CMP_ComponentEntitySettings");
    }

    public function setEntityScheme( $scheme )
    {
        $this->entityScheme = $scheme;
    }

    public function setEntitySettingList( array $settingList )
    {
        $this->entitySettingList = $settingList;
    }

    public function setEntityPositionList( array $positionList )
    {
        $this->entityPositionList = $positionList;
    }

    public function setEntityComponentList( array $entityComponentList )
    {
        $this->entityComponentList = $entityComponentList;
    }

    protected function getCurrentScheme( $defaultScheme )
    {
        if ( empty($this->entityScheme) )
        {
            return $defaultScheme;
        }

        return $this->entityScheme;
    }

    protected function makePositionList( $defaultPositions )
    {
        $entityComponentList = $this->entityComponentList;

        $tmpList = array();

        foreach ( $defaultPositions as $item )
        {
            $componentFreezed = isset($this->settingList[$item['componentPlaceUniqName']]['freeze'])
                && $this->settingList[$item['componentPlaceUniqName']]['freeze'];

            if ( isset($entityComponentList[$item['componentPlaceUniqName']]) && !$componentFreezed )
            {
                continue;
            }

            $tmpList[$item['componentPlaceUniqName']] = $item;
        }

        foreach ( $this->entityPositionList as $item )
        {
            $tmpList[$item['componentPlaceUniqName']] = $item;
        }

        return parent::makePositionList($tmpList);
    }

    protected function makeComponentList( $defaultComponentList )
    {
        $entityList = array();
        foreach ( $this->entityComponentList as $item )
        {
            if ( !isset($defaultComponentList[$item['uniqName']]) )
            {
                $this->entityClonedNameList[] = $item['uniqName'];
            }
            $entityList[$item['uniqName']] = $item;
        }

        return parent::makeComponentList(array_merge($defaultComponentList, $entityList));
    }

    protected function makeSettingList( $defaultSettingtList )
    {
        foreach ( $this->entitySettingList as $key => $item )
        {
            $defaultSettingtList[$key] = empty($defaultSettingtList[$key]) ? $this->entitySettingList[$key] : array_merge($defaultSettingtList[$key], $this->entitySettingList[$key]);
        }

        return parent::makeSettingList($defaultSettingtList);
    }

    protected function isComponentClone($uniqName) 
    {
        return in_array($uniqName, $this->entityClonedNameList);
    }
}