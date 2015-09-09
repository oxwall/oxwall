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
 * Ajax widget entity panel
 *
 * @author Sergey Kambalin <greyexpert@gmail.com>
 * @package ow_system_plugins.base.controllers
 * @since 1.0
 */
class BASE_CTRL_AjaxComponentEntityPanel extends BASE_CTRL_AjaxComponentPanel
{
    /**
     *
     * @var BOL_ComponentEntityService
     */
    private $componentService;

    /**
     * @see OW_ActionController::init()
     *
     */
    public function init()
    {
        parent::init();

        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new Redirect404Exception();
        }

        $this->registerAction('reset', array($this, 'resetCustomization'));

        $this->componentService = BOL_ComponentEntityService::getInstance();
    }

    private function clearCache( $place, $entity )
    {
        $this->componentService->clearEntityCache($place, $entity);
    }

    protected function saveComponentPlacePositions( $data )
    {
        $entity = $data['entity'];

        $this->componentService->clearSection($data['place'], $entity, $data['section']);
        $this->componentService->saveSectionPositionStack($entity, $data['section'], $data['stack']);
        $this->clearCache($data['place'], $entity);

        return true;
    }

    protected function cloneComponent( $data )
    {
        $entity = $data['entity'];

        $this->componentService->clearSection($data['place'], $entity, $data['section']);
        $newComponentId = $this->componentService->cloneComponentPlace($data['componentId'], $entity)->uniqName;

        foreach ( $data['stack'] as & $item )
        {
            $item = ( $item == $data['componentId'] ) ? $newComponentId : $item;
        }

        $this->componentService->saveSectionPositionStack($entity, $data['section'], $data['stack']);
        $this->clearCache($data['place'], $entity);

        return $newComponentId;
    }

    protected function saveSettings( $data )
    {
        $componentPlaceUniqName = $data['componentId'];
        $entity = $data['entity'];

        $settings = $data['settings'];

        $componentId = $this->componentService->findComponentPlace($componentPlaceUniqName, $entity)->componentId;
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

        $settings = $this->processSettingList($componentClass, $settings, $data['place'], false, $data);

        $this->componentService->saveComponentSettingList($componentPlaceUniqName, $entity, $settings);
        $componentSettings = $this->componentService->findSettingList($componentPlaceUniqName, $entity);
        $this->clearCache($data['place'], $entity);

        return array('settingList' => $componentSettings);
    }

    protected function getSettingsMarkup( $data )
    {
        $componentPlaceUniqName = $data['componentId'];
        $entity = $data['entity'];

        $componentId = $this->componentService->findComponentPlace($componentPlaceUniqName, $entity)->componentId;
        $componentClass = $this->componentService->findComponent($componentId)->className;
        $componentSettingList = $this->getComponentSettingList($componentClass, $data);
        $componentStandardSettingValueList = $this->getComponentStandardSettingValueList($componentClass, $data);
        $componentAccess = $this->getComponentAccess($componentClass, $data);

        $defaultSettingList = BOL_ComponentAdminService::getInstance()->findSettingList($componentPlaceUniqName);
        $entitySettingList = $this->componentService->findSettingList($componentPlaceUniqName, $entity);

        $cmpClass = empty($data["settingsCmpClass"]) ? "BASE_CMP_ComponentSettings" : $data["settingsCmpClass"];
        $cmp = OW::getClassInstance($cmpClass, $componentPlaceUniqName, $componentSettingList, array_merge($defaultSettingList, $entitySettingList), $componentAccess);
        $cmp->setStandardSettingValueList($componentStandardSettingValueList);

        return $this->getSettingFormMarkup($cmp);
    }

    protected function savePlaceScheme( $data )
    {
        $placeName = $data['place'];
        $scheme = (int) $data['scheme'];
        $entity = $data['entity'];

        $this->componentService->savePlaceScheme($placeName, $entity, $scheme);

        return true;
    }

    protected function moveComponentToPanel( $data )
    {
        $placeComponentId = $data['componentId'];
        $entity = $data['entity'];
        $this->componentService->moveComponentPlaceFromDefault($placeComponentId, $entity);
        $this->clearCache($data['place'], $entity);

        return true;
    }

    protected function reloadComponent( $data )
    {
        $componentUniqName = $data['componentId'];
        $renderView = !empty($data['render']);
        $entity = $data['entity'];

        $componentPlace = $this->componentService->findComponentPlace($componentUniqName, $entity);
        $component = $this->componentService->findComponent($componentPlace->componentId);
        $defaultSettingList = BOL_ComponentAdminService::getInstance()->findSettingList($componentUniqName);
        $entitySettingList = $this->componentService->findSettingList($componentUniqName, $entity);

        $viewInstance = new BASE_CMP_DragAndDropItem($componentUniqName, (bool) $componentPlace->clone, 'drag_and_drop_item_customize');
        $viewInstance->setSettingList($defaultSettingList, $entitySettingList);
        $viewInstance->componentParamObject->additionalParamList = $data['additionalSettings'];
        $viewInstance->componentParamObject->customizeMode = true;
        $viewInstance->setContentComponentClass($component->className);

        return $this->getComponentMarkup($viewInstance, $renderView);
    }

    protected function deleteComponent( $data )
    {
        $componentUniqName = $data['componentId'];
        $entity = $data['entity'];
        $this->clearCache($data['place'], $entity);

        return $this->componentService->deletePlaceComponent($componentUniqName, $entity);
    }

    protected function resetCustomization( $data )
    {
        $placeName = $data['place'];
        $entity = $data['entity'];

        $this->componentService->resetCustomization($placeName, $entity);
        $this->clearCache($placeName, $entity);
    }
}
