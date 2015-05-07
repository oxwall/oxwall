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
 * User list
 *
 * @author Alex Ermashev <alexermashev@gmail.com>
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
class BASE_CMP_UserList extends OW_Component
{
    /**
     * Default users count
     */
    const DEFAULT_USERS_COUNT = 10;

    /**
     * Count users
     * 
     * @var integer
     */
    protected $countUsers;

    /**
     * User list
     * 
     * @param array $params
     *      integer count
     *      string boxType
     */
    function __construct( array $params = array() )
    {
        parent::__construct();

        $this->countUsers = !empty($params['count']) 
            ? (int) $params['count'] 
            : self::DEFAULT_USERS_COUNT;

        $boxType = !empty($params['boxType']) 
            ? $params['boxType']
            : "";

        // init users short list
        $randId = UTIL_HtmlTag::generateAutoId('base_users_cmp');
        $data = $this->getData($this->countUsers);

        $menuItems = array();
        $dataToAssign = array();

        foreach ( $data as $key => $item )
        {
            $contId = "{$randId}_users_cmp_{$key}";
            $toolbarId = (!empty($item['toolbar']) ? "{$randId}_toolbar_{$key}" : false );

            $menuItems[$key] = array(
                'label' => $item['menu-label'],
                'id' => "{$randId}_users_cmp_menu_{$key}",
                'contId' => $contId,
                'active' => !empty($item['menu_active']),
                'toolbarId' => $toolbarId,
                        'display' => 1
            );

            $usersCmp = $this->getUsersCmp($item['userIds']);

            $dataToAssign[$key] = array(
                'users' => $usersCmp->render(),
                'active' => !empty($item['menu_active']),
                'toolbar' => (!empty($item['toolbar']) ? $item['toolbar'] : array() ),
                'toolbarId' => $toolbarId,
                'contId' => $contId
            );
        }

        $menu = $this->getMenuCmp($menuItems);

        if ( !empty($menu) )
        {
            $this->addComponent('menu', $menu);
        }

        // assign view variables
        $this->assign('widgetId', $randId);
        $this->assign('data', $dataToAssign);
        $this->assign('boxType', $boxType);
    }

    /**
     * Get data
     * 
     * @return array
     */
    public function getData()
    {
        $language = OW::getLanguage();

        $toolbar = array(
            'latest' => array(
                'label' => OW::getLanguage()->text('base', 'view_all'),
                'href' => OW::getRouter()->urlForRoute('base_user_lists', array('list' => 'latest'))
            ),
            'online' => array(
                'label' => OW::getLanguage()->text('base', 'view_all'),
                'href' => OW::getRouter()->urlForRoute('base_user_lists', array('list' => 'online'))
            ),
            'featured' => array(
                'label' => OW::getLanguage()->text('base', 'view_all'),
                'href' => OW::getRouter()->urlForRoute('base_user_lists', array('list' => 'featured'))
            )
        );

        $userService = BOL_UserService::getInstance();
        $latestUsersCount = $userService->count();

        $latestUsersCount > $this->countUsers
            ? $this->assign('toolbar', array($toolbar['latest']))
            : $this->assign('toolbar', array());

        // fill array with result
        $resultList = array(
            'latest' => array(
                'menu-label' => $language->text('base', 'user_list_menu_item_latest'),
                'menu_active' => true,
                'userIds' => $this->getIdList($userService->findList(0, $this->countUsers)),
                'toolbar' => ( $latestUsersCount > $this->countUsers ? array($toolbar['latest']) : false ),
            ),
            'online' => array(
                'menu-label' => $language->text('base', 'user_list_menu_item_online'),
                'userIds' => $this->getIdList($userService->findOnlineList(0, $this->countUsers)),
                'toolbar' => ( $userService->countOnline() > $this->countUsers ? array($toolbar['online']) : false ),
            ));

        // get list of featured users
        $featuredIdLIst = $this->getIdList($userService->findFeaturedList(0, $this->countUsers));

        if ( !empty($featuredIdLIst) )
        {
            $resultList['featured'] = array(
                'menu-label' => $language->text('base', 'user_list_menu_item_featured'),
                'userIds' => $featuredIdLIst,
                'toolbar' => ( $userService->countFeatured() > $this->countUsers ? array($toolbar['featured']) : false ),
            );
        }

        $event = new OW_Event('base.userList.onToolbarReady', array(), $resultList);
        OW::getEventManager()->trigger($event);

        return  $event->getData();
    }

    /**
     * Get id list
     * 
     * @param array $users
     * @return array
     */
    protected function getIdList( array $users )
    {
        $resultArray = array();

        if ( $users )
        {
            foreach ( $users as $user )
            {
                $resultArray[] = $user->getId();
            }
        }

        return $resultArray;
    }
    
    /**
     * Get users component
     * 
     * @param array $list
     * @return \BASE_CMP_AvatarUserList
     */
    protected  function getUsersCmp( array $list )
    {
        return new BASE_CMP_AvatarUserList($list);
    }

    /**
     * Get menu component
     * 
     * @param array $menuItems
     * @return \BASE_CMP_WidgetMenu
     */
    protected function getMenuCmp( array $menuItems )
    {
        return new BASE_CMP_WidgetMenu($menuItems);
    }
}