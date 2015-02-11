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
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_MobileNavigationService
{
    const MENU_TYPE_TOP = BOL_NavigationService::MENU_TYPE_MOBILE_TOP;
    const MENU_TYPE_BOTTOM = BOL_NavigationService::MENU_TYPE_MOBILE_BOTTOM;
    const MENU_TYPE_HIDDEN = BOL_NavigationService::MENU_TYPE_MOBILE_HIDDEN;
    
    const LANG_PREFIX = "ow_custom";
    
    const MENU_PREFIX = self::LANG_PREFIX;
    
    const SETTING_TYPE = "type";
    const SETTING_URL = "url";
    const SETTING_LABEL = "label";
    const SETTING_TITLE = "title";
    const SETTING_CONTENT = "content";
    const SETTING_VISIBLE_FOR = "visibleFor";
    
    /**
     * @var BOL_MobileNavigationService
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_MobileNavigationService
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     *
     * @var BOL_NavigationService
     */
    private $navigationService;
    
    /**
     * Constructor.
     */
    private function __construct()
    {
        $this->navigationService = BOL_NavigationService::getInstance();
    }
    
    
    private function deleteLanguageKeyIfExists( $prefix, $key )
    {
        $languageService = BOL_LanguageService::getInstance();
        
        $keyDto = $languageService->findKey($prefix, $key);
        
        if ( $keyDto !== null )
        {
            $languageService->deleteKey($keyDto->id);
        }
    }
    
    /**
     * 
     * @param string $menu
     * @param int $order
     * @return BOL_MenuItem
     */
    public function createEmptyItem( $menu, $order )
    {
        $menuItem = new BOL_MenuItem();
        $documentKey = UTIL_HtmlTag::generateAutoId('mobile_page');
        
        $menuItem->setDocumentKey($documentKey);
        $menuItem->setPrefix(self::MENU_PREFIX);
        $menuItem->setKey($documentKey);

        $menuItem->setType($menu);
        $menuItem->setOrder($order);
        
        $this->navigationService->saveMenuItem($menuItem);
        
        $document = new BOL_Document();
        $document->isStatic = true;
        $document->isMobile = true;
        $document->setKey($menuItem->getKey());
        $document->setUri($menuItem->getKey());

        $this->navigationService->saveDocument($document);
        
        $document->setUri("cp-" . $document->getId());
        $this->navigationService->saveDocument($document);
        
        $this->editItem($menuItem, array(
            self::SETTING_LABEL => OW::getLanguage()->text("mobile", "admin_nav_default_menu_name"),
            self::SETTING_TITLE => OW::getLanguage()->text("mobile", "admin_nav_default_page_title"),
            self::SETTING_CONTENT => OW::getLanguage()->text("mobile", "admin_nav_default_page_content"),
            self::SETTING_VISIBLE_FOR => 3,
            self::SETTING_TYPE => "local",
            self::SETTING_URL => null
        ));
        
        return $menuItem;
    }
    
    public function deleteItem( BOL_MenuItem $item )
    {
        $document = $this->navigationService->findDocumentByKey($item->getDocumentKey());
        $this->navigationService->deleteDocument($document);
        $this->navigationService->deleteMenuItem($item);

        $this->deleteLanguageKeyIfExists($item->getPrefix(), $item->getKey());
        $this->deleteLanguageKeyIfExists(self::LANG_PREFIX, $item->getKey() . "_title");
        $this->deleteLanguageKeyIfExists(self::LANG_PREFIX, $item->getKey() . "_content");
    }
    
    public function editItem( BOL_MenuItem $item, $settings )
    {
        $languageService = BOL_LanguageService::getInstance();
        $currentLanguageId = $languageService->getCurrent()->getId();
        
        // Menu Item Name
        if ( isset($settings[self::SETTING_LABEL]) )
        {
            $languageService->addOrUpdateValue($currentLanguageId, $item->prefix, $item->key, $settings[self::SETTING_LABEL], false);
        }

        // Page Title
        if ( isset($settings[self::SETTING_TITLE]) )
        {
            $languageService->addOrUpdateValue($currentLanguageId, $item->prefix, $item->key . "_title", $settings[self::SETTING_TITLE], false);
        }

        // Page Content
        if ( isset($settings[self::SETTING_CONTENT]) )
        {
            $content = $settings[self::SETTING_CONTENT];
            $languageService->addOrUpdateValue($currentLanguageId, $item->prefix, $item->key . "_content", $content, false);
        }

        if ( isset($settings[self::SETTING_VISIBLE_FOR]) )
        {
            $item->visibleFor = is_array($settings[self::SETTING_VISIBLE_FOR]) ? array_sum($settings[self::SETTING_VISIBLE_FOR]) : (int) $settings[self::SETTING_VISIBLE_FOR];
        }
        
        if ( isset($settings[self::SETTING_TYPE]) && $settings[self::SETTING_TYPE] == "local" )
        {
            $settings[self::SETTING_URL] = null;
            $item->externalUrl = null;
        }
        
        if ( isset($settings[self::SETTING_URL]) )
        {
            $item->externalUrl = $settings[self::SETTING_URL];
        }
        
        $this->navigationService->saveMenuItem($item);
        $languageService->generateCache($currentLanguageId);
    }
    
    public function getItemSettings( BOL_MenuItem $item )
    {
        $language = OW::getLanguage();
        
        return array(
            self::SETTING_LABEL => $language->text($item->prefix, $item->key),
            self::SETTING_TITLE => $language->text($item->prefix, $item->key . "_title"),
            self::SETTING_CONTENT => $language->text($item->prefix, $item->key . "_content"),
            self::SETTING_VISIBLE_FOR => (int) $item->visibleFor,
            self::SETTING_TYPE => empty($item->externalUrl) ? "local" : "external",
            self::SETTING_URL => $item->externalUrl
        );
    }
    
    public function getItemSettingsByPrefixAndKey( $prefix, $key )
    {
        $item = $this->navigationService->findMenuItem($prefix, $key);
        
        if ( $item === null ) 
        {
            return array();
        }
        
        return $this->getItemSettings($item);
    }
}