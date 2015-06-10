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
 * Widget panel Item
 *
 * @author Sergey Kambalin <greyexpert@gmail.com>
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
class BASE_CMP_DragAndDropItem extends OW_Component
{
    private $boxSettingList = array(
        'type' => 'empty',
        'title' => 'No Title',
        'icon' => 'ow_ic_file',
        'show_title' => true,
        'freeze' => false,
        'wrap_in_box' => false,
        'toolbar' => array(),
        'capContent' => null
    );
    private $settingList = array();
    private $runTimeSettingList = array();
    private $componentContentClass;
    private $standartSettings = array();

    protected $sharedData = array();

    /**
     *
     * @var BASE_CLASS_WidgetParameter
     */
    public $componentParamObject;

    public function __construct( $componentUniqName, $isClone = false, $template = null, $sharedData = array() )
    {
        parent::__construct();
        if ( $template !== null )
        {
            $this->setTemplate(OW::getPluginManager()->getPlugin('base')->getCmpViewDir() . $template . '.html');
        }

        $this->sharedData = $sharedData;

        $this->componentParamObject = new BASE_CLASS_WidgetParameter();
        $this->syncFromParamsObject($this->boxSettingList);
        
        $this->boxSettingList['uniqName'] = $componentUniqName;
        $this->boxSettingList['clone'] = $isClone;

        $this->componentParamObject->widgetDetails->uniqName = $componentUniqName;
    }

    protected function syncFromParamsObject( &$settingList )
    {
        $settingList['show_title'] = $this->componentParamObject->standartParamList->showTitle;
        $settingList['freeze'] = $this->componentParamObject->standartParamList->freezed;
        $settingList['wrap_in_box'] = $this->componentParamObject->standartParamList->wrapInBox;
        $settingList['toolbar'] = $this->componentParamObject->standartParamList->toolbar;
        $settingList['capContent'] = $this->componentParamObject->standartParamList->capContent;
    }
    
    public function setSettingList( array $settingList, array $entitySettingList = array() )
    {
        $this->settingList = array_merge($settingList, $entitySettingList);
    }

    public function setContentComponentClass( $className )
    {
        $this->checkComponent($className);
        $this->componentContentClass = $className;
        
        $this->prepareComponentParamObject();
    }

    protected function getBoxSettingList( array $settingList, array $runTimeSettingList )
    {
        $paramsSettingList = array();
        
        $standartSettingList = $this->getComponentStandartSettingValueList();
        $this->syncFromParamsObject($paramsSettingList);
        $settingList = array_merge($standartSettingList, $settingList, $paramsSettingList, $runTimeSettingList);
        
        $resultSettingList = array();

        foreach ( $settingList as $name => $value )
        {
            switch ( $name )
            {
                case 'wrap_in_box':
                    $resultSettingList['type'] = $value ? null : 'empty';
                case 'show_title':
                case 'freeze':
                case 'clone':
                    $resultSettingList[$name] = (bool) $value;
                    break;
                case 'title':
                    $resultSettingList[$name] = htmlspecialchars($value);
                    break;
                case 'icon':
                case 'uniqName':
                case 'capContent':
                    $resultSettingList[$name] = $value;
                    break;
                case 'toolbar':
                    $resultSettingList[$name] = empty($value) ? array() : $value;
                    break;
                case 'avaliable_sections':
                    $resultSettingList[$name] = is_array($value) ? implode(',', $value) : array();
                    break;
                case BASE_CLASS_Widget::SETTING_ACCESS_RESTRICTIONS:
                    $resultSettingList[$name] = empty($value) ? array() : $value;
                    break;
            }
        }

        return $resultSettingList;
    }

    /**
     *
     * @return BASE_CLASS_WidgetParameter
     */
    private function prepareComponentParamObject()
    {
        $paramObject = $this->componentParamObject;

        $componentSettingList = array();
        foreach ( call_user_func(array($this->componentContentClass, 'getSettingList'), $this->componentParamObject->widgetDetails->uniqName) as $key => $item )
        {
            $componentSettingList[$key] = empty($item['value']) ? null : $item['value'];
        }

        foreach ( $this->settingList as $prop => $value )
        {
            switch ( $prop )
            {
                case 'wrap_in_box':
                    $paramObject->standartParamList->wrapInBox = (bool) $value;
                    break;

                case 'show_title':
                    $paramObject->standartParamList->showTitle = (bool) $value;
                    break;

                case 'freeze':
                    $paramObject->standartParamList->freezed = (bool) $value;
                    break;

                case BASE_CLASS_Widget::SETTING_RESTRICT_VIEW:
                    $paramObject->standartParamList->restrictView = (bool) $value;
                    break;

                case BASE_CLASS_Widget::SETTING_ACCESS_RESTRICTIONS:
                    $paramObject->standartParamList->accessRestriction = empty($value) ? array() : $value;
                    break;

                case BASE_CLASS_Widget::SETTING_TOOLBAR:
                    $paramObject->standartParamList->toolbar = $value;
                    break;

                case BASE_CLASS_Widget::SETTING_CAP_CONTENT:
                    $paramObject->standartParamList->capContent = $value;
                    break;
                
                default:
                    if ( array_key_exists($prop, $componentSettingList) )
                    {
                        $componentSettingList[$prop] = $value;
                    }
            }
        }

        $paramObject->customParamList = $componentSettingList;

        return $paramObject;
    }

    private function checkComponent( $className )
    {
        if ( empty($className) )
        {
            throw new InvalidArgumentException('Invalid Argument `$className`');
        }

        $reflectionClass = new ReflectionClass($className);

        if ( !$reflectionClass->isSubclassOf('BASE_CLASS_Widget') )
        {
            throw new LogicException($className . ' is not configurable');
        }
    }

    public function setStandartSettings( $settings )
    {
        $this->standartSettings = $settings;
    }

    private function getComponentStandartSettingValueList()
    {
        if ( !empty($this->standartSettings) )
        {
            $standardSettingValueList = $this->standartSettings;
        }
        else
        {
            $standardSettingValueList = call_user_func(array($this->componentContentClass, 'getStandardSettingValueList'), $this->componentParamObject->widgetDetails->uniqName);
        }

        return array_merge($this->boxSettingList, $standardSettingValueList);
    }

    private function getComponentAccess()
    {
        return call_user_func(array($this->componentContentClass, 'getAccess'), $this->componentParamObject->widgetDetails->uniqName);
    }

    private function isComponentAvaliable( BASE_CLASS_WidgetParameter $paramsObject )
    {
        $isUserAuthenticated = OW::getUser()->isAuthenticated();

        $access = $this->getComponentAccess();

        if ( $access == BASE_CLASS_Widget::ACCESS_GUEST )
        {
            return !$isUserAuthenticated;
        }

        if ( $access == BASE_CLASS_Widget::ACCESS_MEMBER && !$isUserAuthenticated )
        {
            return false;
        }

        if ( !$paramsObject->standartParamList->restrictView )
        {
            return true;
        }

        if ( in_array($access, array(BASE_CLASS_Widget::ACCESS_ALL, BASE_CLASS_Widget::ACCESS_MEMBER)) )
        {
            if ( $paramsObject->standartParamList->accessRestriction === null )
            {
                return true;
            }
        }

        if ( $access == BASE_CLASS_Widget::ACCESS_ALL && !$isUserAuthenticated )
        {
            $guestRoleId = BOL_AuthorizationService::getInstance()->getGuestRoleId();

            return in_array($guestRoleId, $paramsObject->standartParamList->accessRestriction);
        }

        $userRoles = BOL_AuthorizationService::getInstance()->findUserRoleList(OW::getUser()->getId());

        foreach ( $userRoles as $role )
        {
            if ( in_array($role->id, $paramsObject->standartParamList->accessRestriction) )
            {
                return true;
            }
        }

        return false;
    }

    public function renderView()
    {
        $this->assign('render', true);
        $this->assign('access', $this->getComponentAccess());

        $paramsObject = $this->componentParamObject;
        $isCustomizeMode = $paramsObject->customizeMode;

        if ( !$this->isComponentAvaliable($paramsObject) && !$isCustomizeMode )
        {
            $this->setVisible(false);
            return parent::render();
        }

        $className = $this->componentContentClass;

        /* @var $contentComponent BASE_CLASS_Widget */
        $contentComponent = OW::getClassInstance($className, $paramsObject);

        $this->runTimeSettingList = $contentComponent->getRunTimeSettingList();
        
        if ( !$isCustomizeMode )
        {
            $this->setVisible($contentComponent->isVisible());
        }

        $this->addComponent('content', $contentComponent);

        return $this->render();
    }

    public function renderScheme()
    {
        $this->assign('render', false);
        return $this->render();
    }

    public function render()
    {
        $boxSettings = $this->getBoxSettingList($this->settingList, $this->runTimeSettingList);
        $boxSettings['access'] = $this->getComponentAccess();
        $this->assign('box', $boxSettings);
        
        return parent::render();
    }
}

