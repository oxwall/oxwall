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
 * Widgets admin panel
 *
 * @author Sergey Kambalin <greyexpert@gmail.com>
 * @package ow_system_plugins.base.controller
 * @since 1.0
 */
class ADMIN_CTRL_MobileNavigation extends ADMIN_CTRL_Abstract
{
    public function index()
    {
        $language = OW::getLanguage();
        $this->setPageTitle($language->text('admin', 'page_title_mobile_menus'));
        $this->setPageHeading($language->text('admin', 'page_title_mobile_menus'));

        $dnd = new ADMIN_CMP_MobileNavigation();
        $this->setup($dnd);
        
        $this->addComponent("dnd", $dnd);
    }
    
    public function rsp()
    {
        if ( !OW::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }

        $command = trim($_POST['command']);
        $data = json_decode($_POST['data'], true);
        $shared = json_decode($_POST['shared'], true);
        
        $response = call_user_func(array($this, $command), $data, $shared);

        echo json_encode($response);
        exit;
    }
    
    protected function setup( ADMIN_CMP_MobileNavigation $dnd )
    {
        $navigationService = BOL_NavigationService::getInstance();
        
        $responderUrl = OW::getRouter()->urlFor("ADMIN_CTRL_MobileNavigation", "rsp");
        $dnd->setResponderUrl($responderUrl);
        
        $template = OW::getPluginManager()->getPlugin("admin")->getCtrlViewDir() . "mobile_drag_and_drop.html";
        $this->setTemplate($template);
        
        $panels = array(
            "top" => BOL_MobileNavigationService::MENU_TYPE_TOP,
            "bottom" => BOL_MobileNavigationService::MENU_TYPE_BOTTOM,
            "hidden" => BOL_MobileNavigationService::MENU_TYPE_HIDDEN,
        );
        
        foreach ( $panels as $panel => $menuType )
        {
            $menuItems = $navigationService->findMenuItems($menuType);
            $items = array();
            
            foreach ( $menuItems as $item )
            {
                /* @var $item BOL_MenuItem */
                
                $settings = BOL_MobileNavigationService::getInstance()->getItemSettingsByPrefixAndKey($item["prefix"], $item["key"]);
                
                $items[] = array(
                    "key" => $item["prefix"] . ':' . $item["key"],
                    "title" => $settings[BOL_MobileNavigationService::SETTING_LABEL],
                    "custom" => $item["prefix"] == BOL_MobileNavigationService::MENU_PREFIX
                );
            }
            
            $dnd->setupPanel($panel, array(
                "key" => $menuType,
                "items" => $items
            ));
        }
        
        $dnd->setupPanel("new", array(
            "items" => array(
                array("key" => "new-item", "title" => OW::getLanguage()->text("mobile", "admin_nav_new_item_label"))
            )
        ));
        
        $dnd->setPrefix(BOL_MobileNavigationService::MENU_PREFIX);
        $dnd->setSharedData(array(
            "menuPrefix" => BOL_MobileNavigationService::MENU_PREFIX
        ));
        
        $template = OW::getPluginManager()->getPlugin("admin")->getCmpViewDir() . "mobile_navigation.html";
        $dnd->setTemplate($template);
    }

    public function saveOrder( $data, $shared ) 
    {
        $mobileNavigationService = BOL_MobileNavigationService::getInstance();
        $navigationService = BOL_NavigationService::getInstance();
        
        $response = array();
        
        $response["items"] = array();
        
        foreach ( $data["panels"] as $menu => $items )
        {
            $order = 0;
            
            foreach ( $items as $item )
            {
                list($prefix, $key) = explode(':', $item);
                $menuItem = $navigationService->findMenuItem($prefix, $key);
                
                if ( $menuItem === null )
                {
                    $menuItem = $mobileNavigationService->createEmptyItem($menu, $order);
                }
                else 
                {
                    $menuItem->setOrder($order);
                    $menuItem->setType($menu);
                    
                    $navigationService->saveMenuItem($menuItem);
                }
                
                $order++;
                
                $settings = BOL_MobileNavigationService::getInstance()->getItemSettingsByPrefixAndKey($menuItem->prefix, $menuItem->key);
                
                $response["items"][$item] = array(
                    "key" => $menuItem->getPrefix() . ':' . $menuItem->getKey(),
                    "title" => $settings[BOL_MobileNavigationService::SETTING_LABEL],
                    "custom" => $menuItem->getPrefix() == BOL_MobileNavigationService::MENU_PREFIX
                );
            }
        }
        
        return $response;
    }
    
    public function deleteItem( $data, $shared ) 
    {
        $mobileNavigationService = BOL_MobileNavigationService::getInstance();
        $navigationService = BOL_NavigationService::getInstance();
        list($prefix, $key) = explode(':', $data["key"]);
        
        $menuItem = $navigationService->findMenuItem($prefix, $key);
        
        if ( $menuItem === null  )
        {
            return;
        }
        
        $mobileNavigationService->deleteItem($menuItem);
    }
    
    public function saveItemSettings()
    {
        list($prefix, $key) = explode(':', $_POST["key"]);
        $menuItem = BOL_NavigationService::getInstance()->findMenuItem($prefix, $key);
        
        $form = new ADMIN_CLASS_MobileNavigationItemSettingsForm($menuItem, $menuItem->getPrefix() == BOL_MobileNavigationService::MENU_PREFIX, false);
                
        $out = array();
        
        if ( $form->isValid($_POST) )
        {
            $out = $form->process();
        }
        
        echo json_encode($out);
        exit;
    }
}