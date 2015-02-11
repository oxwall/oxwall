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
 *
 * @author Sergey Kambalin <greyexpert@gmail.com>
 * @package ow_system_plugins.admin.components
 * @since 1.0
 */
class ADMIN_CMP_MobileNavigation extends OW_Component
{
    protected $panels = array();
    protected $prefix;
    protected $sharedData = array();
    protected $responderUrl;

    public function __construct() 
    {
        parent::__construct();
        
        OW_ViewRenderer::getInstance()->registerFunction('dnd_item', array($this, 'tplItem'));
    }
    
    public function setupPanel( $panel, $settings )
    {
        $this->panels[$panel] = empty($this->panels[$panel]) ? array(
            "key" => $panel,
            "items" => array()
        ) : $this->panels[$panel];
        
        $this->panels[$panel] = array_merge($this->panels[$panel], $settings);
    }
    
    public function setResponderUrl( $url )
    {
        $this->responderUrl = $url;
    }
    
    public function setPrefix( $prefix )
    {
        $this->prefix = $prefix;
    }
    
    public function setSharedData( $data )
    {
        $this->sharedData = $data;
    }
    
    public function initStatic()
    {
        $adminJsUrl = OW::getPluginManager()->getPlugin("admin")->getStaticJsUrl();
        $baseJsUrl = OW::getPluginManager()->getPlugin("base")->getStaticJsUrl();
        
        OW::getDocument()->addScript($baseJsUrl . "jquery-ui.min.js");
        OW::getDocument()->addScript($adminJsUrl . "mobile.js");
        
        $settings = array();
        $settings["rsp"] = $this->responderUrl;
        $settings["prefix"] = $this->prefix;
        $settings["shared"] = $this->sharedData;
        
        $js = UTIL_JsGenerator::newInstance();
        $js->callFunction(array("MOBILE", "Navigation", "init"), array($settings));
        
        OW::getDocument()->addOnloadScript($js);
        
        OW::getLanguage()->addKeyForJs("mobile", "admin_nav_adding_message");
        OW::getLanguage()->addKeyForJs("mobile", "admin_nav_settings_fb_title");
    }
    
    public function onBeforeRender() 
    {
        parent::onBeforeRender();
        
        $this->initStatic();
        
        $this->assign("panels", $this->panels);
    }
    
    
    public function tplItem( $params )
    {
        $data = isset($params["data"]) ? $params["data"] : $params;
        
        $item = new ADMIN_CMP_MobileNavigationItem($data);
        
        return $item->render();
    }
}