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
 * Frontend widgets panel
 *
 * @author Sergey Kambalin <greyexpert@gmail.com>
 * @package ow_system_plugins.base.components
 * @since 1.0
 */

class BASE_CMP_DragAndDropFrontendPanel extends BASE_CMP_DragAndDropPanel
{
    protected $customizeMode = false;
    protected $allowCustomize = false;
    protected $responderController = "BASE_CTRL_AjaxComponentAdminPanel";

    public function __construct( $placeName, array $componentList, $customizeMode, $componentTemplate, $responderController = null )
    {
        parent::__construct($placeName, $componentList, $componentTemplate);

        if ( !empty($responderController) )
        {
            $this->responderController = $responderController;
        }
        
        $this->customizeMode = (bool) $customizeMode;
        
        $this->assign('customizeMode', $this->customizeMode);
        $this->assign('allowCustomize', $this->allowCustomize);
        
        $this->setSettingsClassName("BASE_CMP_ComponentFrontendSettings");
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        if ( $this->customizeMode )
        {
            $this->initializeJs($this->responderController, 'OW_Components_DragAndDrop', $this->sharedData);

            $jsDragAndDropUrl = OW::getPluginManager()->getPlugin('BASE')->getStaticJsUrl() . 'drag_and_drop.js';
            OW::getDocument()->addScript($jsDragAndDropUrl);
        }
    }

    public function allowCustomize( $allowed = true )
    {
        $this->allowCustomize = $allowed;
        $this->assign('allowCustomize', $allowed);
    }

    public function customizeControlCunfigure( $customizeUrl, $normalUrl )
    {
        if ( $this->allowCustomize )
        {
            $js = new UTIL_JsGenerator();
            $js->newVariable('dndCustomizeUrl', $customizeUrl);
            $js->newVariable('dndNormalUrl', $normalUrl);
            $js->jQueryEvent('#goto_customize_btn', 'click', 'if(dndCustomizeUrl) window.location.href=dndCustomizeUrl;');
            $js->jQueryEvent('#goto_normal_btn', 'click', 'if(dndNormalUrl) window.location.href=dndNormalUrl;');
            OW::getDocument()->addOnloadScript($js);
        }
    }

    public function tplComponent( $params )
    {
        $uniqName = $params['uniqName'];
        $render = !empty($params['render']);

        $componentPlace = $this->componentList[$uniqName];
        $template = $this->customizeMode ? 'drag_and_drop_item_customize' : null;

        $viewInstance = new $this->itemClassName($uniqName, $this->isComponentClone($uniqName), $template, $this->sharedData);
        $viewInstance->setSettingList(empty($this->settingList[$uniqName]) ? array() : $this->settingList[$uniqName]);
        $viewInstance->componentParamObject->additionalParamList = $this->additionalSettingList;
        $viewInstance->componentParamObject->customizeMode = $this->customizeMode;

        if ( !empty($this->standartSettings[$componentPlace['className']]) )
        {
            $viewInstance->setStandartSettings($this->standartSettings[$componentPlace['className']]);
        }

        $viewInstance->setContentComponentClass($componentPlace['className']);

        if ( $render )
        {
            return $viewInstance->renderView();
        }

        return $viewInstance->renderScheme();
    }
}