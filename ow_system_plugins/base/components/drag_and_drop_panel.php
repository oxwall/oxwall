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
 * Widgets panel
 *
 * @author Sergey Kambalin <greyexpert@gmail.com>
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
abstract class BASE_CMP_DragAndDropPanel extends OW_Component
{
    protected $settingsCmpClass = "BASE_CMP_ComponentSettings";
    protected $itemClassName = "BASE_CMP_DragAndDropItem";
    
    protected $componentList = array();
    protected $settingList = array();
    protected $positionList = array();
    protected $standartSettings = array();

    protected $placeName;
    /**
     *
     * @var BOL_Scheme
     */
    protected $scheme = 1;
    protected $schemeList = array();
    protected $additionalSettingList = array();
    protected $sharedData = array();

    public function __construct( $placeName, array $componentList, $template = null )
    {
        parent::__construct();

        if ( $template !== null )
        {
            $plugin = OW::getPluginManager()->getPlugin(OW::getAutoloader()->getPluginKey(get_class($this)));
            $this->setTemplate($plugin->getCmpViewDir() . $template . '.html');
        }

        $this->placeName = $placeName;
        $this->componentList = $componentList;

        foreach ( $this->componentList as $widget )
        {
            $this->standartSettings[$widget['className']] = call_user_func(array($widget['className'], 'getStandardSettingValueList'), $widget["uniqName"]);
        }

        OW_ViewRenderer::getInstance()->registerFunction('dd_component', array($this, 'tplComponent'));

        $this->assign('disableJs', !empty($_GET['disable-js']));

        $this->assign('placeName', $placeName);


        $this->sharedData = array(
            'additionalSettings' => &$this->additionalSettingList,
            'place' => $this->placeName
        );
    }

    public function setItemClassName( $class ) 
    {
        $this->itemClassName = $class;
    }
    
    public function setSettingsClassName( $class ) 
    {
        $this->settingsCmpClass = $class;
    }

    protected function initializeJs( $responderController, $dragAndDropJsConstructor, $sharedData = array() )
    {
        $baseStaticJsUrl = OW::getPluginManager()->getPlugin('BASE')->getStaticJsUrl();

        OW::getDocument()->addScript($baseStaticJsUrl . 'jquery-ui.min.js');
        OW::getDocument()->addScript($baseStaticJsUrl . 'drag_and_drop_slider.js');
        OW::getDocument()->addScript($baseStaticJsUrl . 'ajax_utils.js');
        OW::getDocument()->addScript($baseStaticJsUrl . 'drag_and_drop_handler.js');
        OW::getDocument()->addScript($baseStaticJsUrl . 'component_drag_and_drop.js');

        OW::getLanguage()->addKeyForJs('base', 'widgets_delete_component_confirm');
        OW::getLanguage()->addKeyForJs('base', 'widgets_reset_position_confirm');
        $urlAjaxResponder = OW::getRouter()->urlFor($responderController, 'processQueue');

        $sharedData = array_merge(array(
            "settingsCmpClass" => $this->settingsCmpClass
        ), $sharedData);
        
        $js = new UTIL_JsGenerator();
        $js->newObject('handler', 'OW_Components_DragAndDropAjaxHandler', array($urlAjaxResponder, $sharedData));
        $js->newObject('dragAndDrop', $dragAndDropJsConstructor);
        $js->addScript("dragAndDrop.setHandler(handler)");

        OW::getDocument()->addOnloadScript($js);
    }

    public function setSettingList( array $settingList )
    {
        $this->settingList = $settingList;
    }

    public function setPositionList( array $positionList )
    {
        $this->positionList = $positionList;
    }

    public function setScheme( $scheme )
    {
        $this->scheme = (array) $scheme;
    }

    public function setSchemeList( array $schemeList )
    {
        $this->schemeList = $schemeList;
    }

    protected function getCurrentScheme( $scheme )
    {
        return $scheme;
    }

    private function makeTplComponentList()
    {
        $resultList = array();
        $tplPanelComponents = & $resultList['place'];
        $tplSectionComponents = & $resultList['section'];
        $tplClonableComponents = & $resultList['clonable'];

        $tplPanelComponents = array();
        $tplSectionComponents = array();
        $tplClonableComponents = array();

        foreach ( $this->componentList as $uniqName => $component )
        {
            if ( isset($this->positionList[$uniqName]) )
            {
                $position = $this->positionList[$uniqName];
                $tplSectionComponents[$position['section']][] = $component;
            }
            else
            {
                if ( $component['clonable'] && !$component['clone'] )
                {
                    $tplClonableComponents[] = $component;
                }
                else
                {
                    $tplPanelComponents[] = $component;
                }
            }
        }

        krsort($tplClonableComponents); //TODO clonable component order

        foreach ( $tplSectionComponents as &$section )
        {
            usort($section, array($this, 'sectionSortDelegate'));
        }

        return $resultList;
    }

    protected function sectionSortDelegate( $a, $b )
    {
        $x = ( isset($this->settingList[$a['uniqName']]['freeze']) && $this->settingList[$a['uniqName']]['freeze'] ) ? 0 : 1;
        $y = ( isset($this->settingList[$b['uniqName']]['freeze']) && $this->settingList[$b['uniqName']]['freeze'] ) ? 0 : 1;

        $r = $x - $y;

        if ( $r === 0 )
        {
            $positionA = (int) $this->positionList[$a['uniqName']]['order'];
            $positionB = (int) $this->positionList[$b['uniqName']]['order'];

            return $positionA - $positionB;
        }

        return $r;
    }

    /*protected function sectionSortDelegate( $a, $b )
    {
        //TODO refactoring: bad place to call static method
        $widgetA = $this->componentList[$a['uniqName']];
        $widgetB = $this->componentList[$b['uniqName']];

        $standardSettingsA = call_user_func(array($widgetA['className'], 'getStandardSettingValueList'));
        $standardSettingsB = call_user_func(array($widgetB['className'], 'getStandardSettingValueList'));

        $freezedA = empty($standardSettingsA['freeze']) ? 0 : 1;
        $freezedB = empty($standardSettingsB['freeze']) ? 0 : 1;

        $x = empty($this->settingList[$a['uniqName']]['freeze']) ? $freezedA : 1;
        $y = empty($this->settingList[$b['uniqName']]['freeze']) ? $freezedB : 1;

        $r = $y - $x;

        if ( $r === 0 )
        {
            $positionA = (int) $this->positionList[$a['uniqName']]['order'];
            $positionB = (int) $this->positionList[$b['uniqName']]['order'];

            return $positionA - $positionB;
        }

        return $r;
    }*/

    protected function makePositionList( $positionList )
    {
        return $positionList;
    }

    protected function makeComponentList( $componentList )
    {
        return $componentList;
    }

    protected function makeSettingList( $settingList )
    {
        foreach ( $this->componentList as $widget )
        {
            $standartSettings = empty($this->standartSettings[$widget['className']])
                ? array()
                : $this->standartSettings[$widget['className']];

            $settingList[$widget['uniqName']] = empty($settingList[$widget['uniqName']])
                ? $standartSettings
                : array_merge($standartSettings, $settingList[$widget['uniqName']]);
        }

        return $settingList;
    }

    public function onBeforeRender()
    {
        BASE_CLASS_Widget::setPlaceData($this->sharedData);

        $this->settingList = $this->makeSettingList($this->settingList);
        $this->positionList = $this->makePositionList($this->positionList);
        $this->componentList = $this->makeComponentList($this->componentList);

        $componentList = $this->makeTplComponentList();

        $currentShceme = $this->getCurrentScheme($this->scheme);
        if ( !empty($currentShceme) )
        {
            $this->assign('activeScheme', $currentShceme);
        }

        $this->assign('componentList', $componentList);
        $this->assign('schemeList', $this->schemeList);
    }

    public function setAdditionalSettingList( array $settingList = array() )
    {
        $this->additionalSettingList = $settingList;
    }

    protected function isComponentClone( $uniqName )
    {
        return $this->componentList[$uniqName]['clone'];
    }
    
    public function tplComponent( $params )
    {
        $uniqName = $params['uniqName'];

        $viewInstance = new $this->itemClassName($uniqName, $this->isComponentClone($uniqName), 'drag_and_drop_item_customize', $this->sharedData);
        $viewInstance->setSettingList(empty($this->settingList[$uniqName]) ? array() : $this->settingList[$uniqName]);
        $viewInstance->componentParamObject->additionalParamList = $this->additionalSettingList;
        $viewInstance->componentParamObject->customizeMode = null;

        if ( !empty($this->standartSettings[$this->componentList[$uniqName]['className']]) )
        {
            $viewInstance->setStandartSettings($this->standartSettings[$this->componentList[$uniqName]['className']]);
        }

        $viewInstance->setContentComponentClass($this->componentList[$uniqName]['className']);

        return $viewInstance->renderScheme();
    }
}