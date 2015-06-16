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
 * The service class helps to manage menus and documents. 
 * 
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_NavigationService
{
    const MENU_TYPE_MAIN = BOL_MenuItemDao::VALUE_TYPE_MAIN;
    const MENU_TYPE_BOTTOM = BOL_MenuItemDao::VALUE_TYPE_BOTTOM;
    const MENU_TYPE_HIDDEN = BOL_MenuItemDao::VALUE_TYPE_HIDDEN;
    const MENU_TYPE_ADMIN = BOL_MenuItemDao::VALUE_TYPE_ADMIN;
    const MENU_TYPE_SETTINGS = BOL_MenuItemDao::VALUE_TYPE_SETTINGS;
    const MENU_TYPE_PAGES = BOL_MenuItemDao::VALUE_TYPE_PAGES;
    const MENU_TYPE_APPEARANCE = BOL_MenuItemDao::VALUE_TYPE_APPEARANCE;
    const MENU_TYPE_USERS = BOL_MenuItemDao::VALUE_TYPE_USERS;
    const MENU_TYPE_PLUGINS = BOL_MenuItemDao::VALUE_TYPE_PLUGINS;
    const MENU_TYPE_MOBILE = BOL_MenuItemDao::VALUE_TYPE_MOBILE;
    const MENU_TYPE_MOBILE_TOP = BOL_MenuItemDao::VALUE_TYPE_MOBILE_TOP;
    const MENU_TYPE_MOBILE_BOTTOM = BOL_MenuItemDao::VALUE_TYPE_MOBILE_BOTTOM;
    const MENU_TYPE_MOBILE_HIDDEN = BOL_MenuItemDao::VALUE_TYPE_MOBILE_HIDDEN;

    const VISIBLE_FOR_GUEST = BOL_MenuItemDao::VALUE_VISIBLE_FOR_GUEST;
    const VISIBLE_FOR_MEMBER = BOL_MenuItemDao::VALUE_VISIBLE_FOR_MEMBER;
    const VISIBLE_FOR_ALL = BOL_MenuItemDao::VALUE_VISIBLE_FOR_ALL;

    /**
     * @var BOL_DocumentDao
     */
    private $documentDao;
    /**
     * @var BOL_MenuItemDao
     */
    private $menuItemDao;
    /**
     * @var BOL_NavigationService
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_NavigationService
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
     * Constructor.
     */
    private function __construct()
    {
        $this->documentDao = BOL_DocumentDao::getInstance();
        $this->menuItemDao = BOL_MenuItemDao::getInstance();
    }

    /**
     * Saves and updates document items.
     * 
     * @param BOL_Document $document
     */
    public function saveDocument( BOL_Document $document )
    {
        $this->documentDao->save($document);
    }

    /**
     * Saves and updates menu items.
     * 
     * @param BOL_MenuItem $menuItem
     */
    public function saveMenuItem( BOL_MenuItem $menuItem )
    {
        $this->menuItemDao->save($menuItem);
    }

    /**
     * Returns list of all static documents.
     * 
     * @return array<BOL_Document>
     */
    public function findAllStaticDocuments()
    {
        return $this->documentDao->findAllStaticDocuments();
    }

    /**
     * Returns list of all static documents.
     *
     * @return array<BOL_Document>
     */
    public function findAllMobileStaticDocuments()
    {
        return $this->documentDao->findAllMobileStaticDocuments();
    }

    /**
     * Checks if static document with provided uri (assigned) exists.
     *
     * @param string $uri
     * @return boolean
     */
    public function staticDocumentExists( $uri )
    {
        return!( $this->findStaticDocument($uri) === null );
    }

    /**
     * Returns static document object for provided uri.
     * 
     * @param string $uri
     * @return BOL_Document
     */
    public function findStaticDocument( $uri )
    {
        return $this->documentDao->findStaticDocument($uri);
    }

    /**
     * Returns list of menu items for provided menu type.
     * 
     * @param $menuType
     * @return array<BOL_MenuItem>
     */
    public function findMenuItems( $menuType )
    {
        return $this->menuItemDao->findMenuItems($menuType);
    }

    /**
     * Returns list of menu items for provided list of menu types.
     *
     * @param array $menuTypes
     * @return array
     */
    public function findMenuItemsForMenuList( $menuTypes )
    {
        $items = $this->menuItemDao->findMenuItemsForMenuTypes($menuTypes);

        $resultArray = array();

        foreach ( $menuTypes as $type )
        {
            $resultArray[$type] = array();
        }

        /* @var $item BOL_MenuItem */
        foreach ( $items as $item )
        {
            $resultArray[$item['type']][] = $item;
        }

        return $resultArray;
    }

    /**
     * Returns static document object (dto) for provided controller class and method.
     *
     * @param string $controller
     * @param string $action
     * @return BOL_Document
     */
    public function findDocumentByDispatchAttrs( $controller, $action )
    {
        return $this->documentDao->findDocumentByDispatchAttrs($controller, $action);
    }

    /**
     * Returns menu item dto for provided id.
     * 
     * @param int $id
     * @return BOL_MenuItem
     */
    public function findMenuItemById( $id )
    {
        return $this->menuItemDao->findById($id);
    }

    public function findDocumentById( $id )
    {
        return $this->documentDao->findById($id);
    }

    public function deleteDocument( $dto )
    {
        return $this->documentDao->delete($dto);
    }

    /**
     * Returns max sort order for menu type.
     * 
     * @param strign $menuType
     * @return integer
     */
    public function findMaxSortOrderForMenuType( $menuType )
    {
        return $this->menuItemDao->findMaxOrderForMenuType($menuType);
    }

    /**
     *
     * @return BOL_Document
     */
    public function findDocumentByKey( $key )
    {
        return $this->documentDao->findDocumentByKey($key);
    }

    public function deleteMenuItem( $dto )
    {
        $this->menuItemDao->delete($dto);
    }

    public function findMenuItem( $prefix, $key )
    {
        return $this->menuItemDao->findMenuItem($prefix, $key);
    }

    /**
     *
     * @param <type> $visibleFor
     * @return BOL_MenuItem
     */
    public function findFirstLocal( $visibleFor, $menuType )
    {
        return $this->menuItemDao->findFirstLocal($visibleFor, $menuType);
    }

    public function isDocumentUriUnique( $uri )
    {
        return $this->documentDao->isDocumentUriUnique($uri);
    }

    /**
     * Converts query result array into BASE_MenuItem items array.
     *
     * @param array $items
     */
    public function getMenuItems( array $menuItems )
    {
        $resultArray = array();

        foreach ( $menuItems as $value )
        {
            $visible = (int) $value['visibleFor'];
            $auth = OW::getUser()->isAuthenticated();

            if ( $visible === 0 || ( $visible === 1 && $auth ) || ( $visible === 2 && !$auth ) )
            {
                continue;
            }

            if ( !empty($value['externalUrl']) )
            {
                $url = $value['externalUrl'];
            }
            else if ( !empty($value['uri']) )
            {
                $url = OW::getRouter()->getBaseUrl() . $value['uri'];
            }
            else if ( !empty($value['routePath']) )
            {
                $url = OW::getRouter()->urlForRoute($value['routePath']);
            }
            else if ( !empty($value['class']) && !empty($value['action']) )
            {
                $url = OW::getRouter()->urlFor($value['class'], $value['action']);
            }
            else
            {
                $url = '_INVALID_URL_';
            }

            $menuItem = new BASE_MenuItem();
            $menuItem->setKey($value['menu_key']);
            $menuItem->setLabel(OW::getLanguage()->text($value['prefix'], $value['menu_key']));
            $menuItem->setOrder($value['order']);
            $menuItem->setUrl($url);
            $menuItem->setNewWindow($value['newWindow']);
            $menuItem->setPrefix($value['prefix']);

            $resultArray[] = $menuItem;
        }

        return $resultArray;
    }

    /**
     * System method. Don't call it.
     *
     * @param BOL_MenuItem $el1
     * @param BOL_MenuItem $el2
     */
    public function sortObjectListByAsc( BASE_MenuItem $el1, BASE_MenuItem $el2 )
    {
        if ( $el1->getOrder() === $el2->getOrder() )
        {
            return 0;
        }

        return $el1->getOrder() > $el2->getOrder() ? 1 : -1;
    }

    /**
     * Finds menu item by document key.
     *
     * @param string $docKey
     * @return BOL_MenuItem
     */
    public function findMenuItemByDocumentKey( $docKey )
    {
        return $this->menuItemDao->findByDocumentKey($docKey);
    }
}