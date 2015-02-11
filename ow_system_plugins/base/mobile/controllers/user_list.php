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
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow_system_plugins.base.controllers
 * @since 1.0
 */
class BASE_MCTRL_UserList extends OW_MobileActionController
{
    private $usersPerPage;

    public function __construct()
    {
        parent::__construct();
        OW::getNavigation()->activateMenuItem(OW_Navigation::MAIN, 'base', 'users_main_menu_item');

        $this->setPageHeading(OW::getLanguage()->text('base', 'users_browse_page_heading'));
        $this->setPageTitle(OW::getLanguage()->text('base', 'users_browse_page_heading'));
        $this->setPageHeadingIconClass('ow_ic_user');
        $this->usersPerPage = (int)OW::getConfig()->getValue('base', 'users_count_on_page');
    }

    public function index( $params )
    {
        $listType = empty($params['list']) ? 'latest' : strtolower(trim($params['list']));
        $language = OW::getLanguage();
        $this->addComponent('menu', self::getMenu($listType));
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl().'mobile_user_list.js');

        $data = $this->getData( $listType, array(), true, $this->usersPerPage );
        $cmp = new BASE_MCMP_BaseUserList($listType, $data, true);
        $this->addComponent('list', $cmp);
        $this->assign('listType', $listType);

        OW::getDocument()->addOnloadScript(" 
            window.mobileUserList = new OW_UserList(".  json_encode(array(
                    'component' => 'BASE_MCMP_BaseUserList',
                    'listType' => $listType,
                    'excludeList' => $data,
                    'node' => '.owm_user_list',
                    'showOnline' => true,
                    'count' => $this->usersPerPage,
                    'responderUrl' => OW::getRouter()->urlForRoute('base_user_lists_responder')
                )).");
        ", 50);
    }

    public function responder( $params )
    {
        if ( !OW::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }
        
        $listKey = empty($_POST['list']) ? 'latest' : strtolower(trim($_POST['list']));
        $excludeList = empty($_POST['excludeList']) ? array() : $_POST['excludeList'];
        $showOnline = empty($_POST['showOnline']) ? false : $_POST['showOnline'];
        $count = empty($_POST['count']) ? $this->usersPerPage : (int)$_POST['count'];

        $data = $this->getData( $listKey, $excludeList, $showOnline, $count );

        echo json_encode($data);
        exit;
    }

    protected function getData( $listKey, $excludeList = array(), $showOnline, $count )
    {
        $list = array();

        $start = count($excludeList);

        while ( $count > count($list) )
        {
            $service = BOL_UserService::getInstance();
            $tmpList =  $service->getDataForUsersList($listKey, $start, $count);
            $itemList = $tmpList[0];
            $itemCount = $tmpList[1];

            if ( empty($itemList)  )
            {
                break;
            }
            
            foreach ( $itemList as $key => $item )
            {
                if ( count($list) == $count )
                {
                    break;
                }

                if ( !in_array($item->id, $excludeList) )
                {
                    $list[] = $item->id;
                }
            }
            
            $start += $count;

            if ( $start >= $itemCount )
            {
                break;
            }
        }

        return $list;
    }

    public static function getMenu( $activeListType )
    {
        $language = OW::getLanguage();

        $menuArray = array(
            array(
                'label' => $language->text('base', 'user_list_menu_item_latest'),
                'url' => OW::getRouter()->urlForRoute('base_user_lists', array('list' => 'latest')),
                'iconClass' => 'ow_ic_clock',
                'key' => 'latest',
                'order' => 1
            ),
            array(
                'label' => $language->text('base', 'user_list_menu_item_online'),
                'url' => OW::getRouter()->urlForRoute('base_user_lists', array('list' => 'online')),
                'iconClass' => 'ow_ic_push_pin',
                'key' => 'online',
                'order' => 3
            ),
            /* array(
                'label' => $language->text('base', 'user_search_menu_item_label'),
                'url' => OW::getRouter()->urlForRoute('users-search'),
                'iconClass' => 'ow_ic_lens',
                'key' => 'search',
                'order' => 4
            ) */
        );

        if ( BOL_UserService::getInstance()->countFeatured() > 0 )
        {
            $menuArray[] =  array(
                'label' => $language->text('base', 'user_list_menu_item_featured'),
                'url' => OW::getRouter()->urlForRoute('base_user_lists', array('list' => 'featured')),
                'iconClass' => 'ow_ic_push_pin',
                'key' => 'featured',
                'order' => 2
            );
        }

        $event = new BASE_CLASS_EventCollector('base.add_user_list');
        OW::getEventManager()->trigger($event);
        $data = $event->getData();

        if ( !empty($data) )
        {
            $menuArray = array_merge($menuArray, $data);
        }

        $menu = new BASE_MCMP_ContentMenu();

        foreach ( $menuArray as $item )
        {
            $menuItem = new BASE_MenuItem();
            $menuItem->setLabel($item['label']);
            $menuItem->setIconClass($item['iconClass']);
            $menuItem->setUrl($item['url']);
            $menuItem->setKey($item['key']);
            $menuItem->setOrder(empty($item['order']) ? 999 : $item['order']);
            $menu->addElement($menuItem);

            if ( $activeListType == $item['key'] )
            {
                $menuItem->setActive(true);
            }
        }

        return $menu;
    }
}

