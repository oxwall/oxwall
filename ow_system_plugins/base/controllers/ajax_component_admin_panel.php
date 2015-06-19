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
 * Ajax widget admin panel
 *
 * @author Sergey Kambalin <greyexpert@gmail.com>
 * @package ow_system_plugins.base.controllers
 * @since 1.0
 */
class BASE_CTRL_AjaxComponentAdminPanel extends BASE_CTRL_AjaxComponentPanel
{
    /**
     *
     * @var BOL_ComponentAdminService
     */
    private $componentService;

    /**
     * @see OW_ActionController::init()
     *
     */
    public function init()
    {
        parent::init();

        $this->registerAction('allowCustomize', array($this, 'allowCustomize'));

        if ( !OW::getUser()->isAdmin() )
        {
            throw new Redirect404Exception();
        }

        $this->componentService = BOL_ComponentAdminService::getInstance();
    }

    private function clearCache( $place )
    {
        $this->componentService->clearCache($place);
    }

    protected function saveComponentPlacePositions( $data )
    {
        $this->componentService->clearSection($data['place'], $data['section']);
        $this->componentService->saveSectionPositionStack($data['section'], $data['stack']);
        $this->clearCache($data['place']);

        return true;
    }

    protected function deleteComponent( $data )
    {
        $this->clearCache($data['place']);
        return $this->componentService->deletePlaceComponent($data['componentId']);
    }

    protected function cloneComponent( $data )
    {
        $this->componentService->clearSection($data['place'], $data['section']);
        $newComponentUniqName = $this->componentService->cloneComponentPlace($data['componentId'])->uniqName;

        foreach ( $data['stack'] as & $item )
        {
            $item = ( $item == $data['componentId'] ) ? $newComponentUniqName : $item;
        }

        $this->componentService->saveSectionPositionStack($data['section'], $data['stack']);
        $this->clearCache($data['place']);

        return $newComponentUniqName;
    }

    protected function saveSettings( $data )
    {
        $componentPlaceUniqName = $data['componentId'];
        $settings = $data['settings'];

        $componentId = $this->componentService->findPlaceComponent($componentPlaceUniqName)->componentId;
        $componentClass = $this->componentService->findComponent($componentId)->className;

        try
        {
            $this->validateComponentSettingList($componentClass, $settings, $data['place'], $data);
        }
        catch ( WidgetSettingValidateException $e )
        {
            return array('error' => array(
                    'message' => $e->getMessage(),
                    'field' => $e->getFieldName()
                ));
        }

        $settings = $this->processSettingList($componentClass, $settings, $data['place'], true, $data);

        $this->componentService->saveComponentSettingList($componentPlaceUniqName, $settings);
        $componentSettings = $this->componentService->findSettingList($componentPlaceUniqName);
        $this->clearCache($data['place']);

        return array('settingList' => $componentSettings);
    }

    protected function getSettingsMarkup( $data )
    {
        if ( empty($data['componentId']) )
        {
            return array();
        }

        $componentPlaceUniqName = $data['componentId'];

        $componentId = $this->componentService->findPlaceComponent($componentPlaceUniqName)->componentId;

        $componentClass = $this->componentService->findComponent($componentId)->className;
        
        $componentSettingList = $this->getComponentSettingList($componentClass, $data);
        $componentStandardSettingValueList = $this->getComponentStandardSettingValueList($componentClass, $data);
        $componentAccess = $this->getComponentAccess($componentClass, $data);

        $entitySettingList = $this->componentService->findSettingList($componentPlaceUniqName);

        $cmpClass = empty($data["settingsCmpClass"]) ? "BASE_CMP_ComponentSettings" : $data["settingsCmpClass"];
        $cmp = OW::getClassInstance($cmpClass, $componentPlaceUniqName, $componentSettingList, $entitySettingList, $componentAccess);
        
        if ( $data['place'] == BOL_ComponentService::PLACE_INDEX )
        {
            $cmp->markAsHidden('freeze');
        }

        $cmp->setStandardSettingValueList($componentStandardSettingValueList);

        return $this->getSettingFormMarkup($cmp);
    }

    protected function savePlaceScheme( $data )
    {
        $placeName = $data['place'];
        $scheme = (int) $data['scheme'];
        $this->componentService->savePlaceScheme($placeName, $scheme);

        $this->clearCache($data['place']);

        return true;
    }

    protected function moveComponentToPanel( $data )
    {
        $placeComponentId = $data['componentId'];
        $this->componentService->saveComponentSettingList($placeComponentId, array('freeze' => 0));

        $this->clearCache($data['place']);

        return array(
            'freeze' => false
        );
    }

    protected function reloadComponent( $data )
    {
        $componentUniqName = $data['componentId'];
        $renderView = !empty($data['render']);

        $componentPlace = $this->componentService->findPlaceComponent($componentUniqName);
        $component = $this->componentService->findComponent($componentPlace->componentId);
        $componentSettingList = $this->componentService->findSettingList($componentUniqName);

        $viewInstance = new BASE_CMP_DragAndDropItem($componentUniqName, $componentPlace->clone, 'drag_and_drop_item_customize');
        $viewInstance->setSettingList($componentSettingList);
        $viewInstance->componentParamObject->additionalParamList = $data['additionalSettings'];
        $viewInstance->componentParamObject->customizeMode = true;

        $viewInstance->setContentComponentClass($component->className);

        return $this->getComponentMarkup($viewInstance, $renderView);
    }

    protected function allowCustomize( $data )
    {
        $placeName = $data['place'];
        $allowed = $data['state'];

        $this->componentService->saveAllowCustomize($placeName, $allowed);
    }
}
