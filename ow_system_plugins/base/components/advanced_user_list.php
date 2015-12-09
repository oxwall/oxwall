<?php

/**
 * Copyright (c) 2014, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com) and is licensed under SkaDate Exclusive License by Skalfa LLC.
 *
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */

/**
 * Search result component
 *
 * @author Egor Bulgakov <egor.bulgakov@gmail.com>
 * @package ow.ow_plugins.usearch.components
 * @since 1.5.3
 */
class BASE_CMP_AdvancedUserList extends OW_Component
{
    const EVENT_GET_VISIBLE_FIELDS = "base.user_list.get_visible_fields";
    const EVENT_GET_HIDDEN_FIELDS = "base.user_list.get_hidden_fields";

    protected $items;
    protected $listKey;
    protected $enableActions;
    protected $params;

    public function __construct($listKey, $list, $actions = false, $aditionalParams = array())
    {
        $this->listKey = $listKey;
        $this->items = $list;
        $this->enableActions = $actions;
        $this->params = $aditionalParams;
        parent::__construct();
    }

    protected function getOnlineInfo($userIdList)
    {
        if ( empty($userIdList) )
        {
            return array();
        }

        $service = BOL_UserService::getInstance();
        $onlineInfo = $service->findOnlineStatusForUserList($userIdList);
        $ownerIdList = array();

        foreach ( $onlineInfo as $userId => $isOnline )
        {
            $ownerIdList[$userId] = $userId;
        }

        $eventParams = array(
            'action' => 'base_view_my_presence_on_site',
            'ownerIdList' => $ownerIdList,
            'viewerId' => OW::getUser()->getId()
        );

        $permissions = OW::getEventManager()->getInstance()->call('privacy_check_permission_for_user_list', $eventParams);

        foreach ( $onlineInfo as $userId => $isOnline )
        {
            $onlineInfo[$userId] = true;

            // Check privacy permissions
            if ( isset($permissions[$userId]['blocked']) && $permissions[$userId]['blocked'] == true )
            {
                $onlineInfo[$userId] = false;
            }
        }

        return $onlineInfo;
    }

    public function getFields( $userIdList )
    {
        $fields = array();
        $visible = array();
        $hidden = array();

        $service = BOL_UserService::getInstance();

        $displayNameList = $service->getDisplayNamesForList($userIdList);
        $onlineInfo = $this->getOnlineInfo($userIdList);

        foreach ( $userIdList as $id )
        {
            $visible[$id] = array();
            $hidden[$id] = array();

            if ( !empty($onlineInfo[$id]) ) {
                $visible[$id]['onlineInfo'] = "<div style=\"display: inline-block;\" class=\"ow_miniic_live\">
                             <span class=\"ow_live_on\"></span>
                          </div>";
            }

            if ( isset($displayNameList[$id]) ) {

                $visible[$id]['displayName'] = "<b class=\"ow_usearch_display_name\">{$displayNameList[$id]}</b>";
            }
        }

        $params = array(
            'list' => $this->listKey,
            'userIdList' => $userIdList,
            'aditionalParams' => $this->params );

        // get visible fields
        $event = new OW_Event(self::EVENT_GET_VISIBLE_FIELDS, $params, $visible);
        OW::getEventManager()->trigger($event);
        $visible = $event->getData();

        //get hidden fields
        $event = new OW_Event( self::EVENT_GET_HIDDEN_FIELDS, $params, $hidden);
        OW::getEventManager()->trigger($event);
        $hidden = $event->getData();

        foreach ( $userIdList as $id )
        {
            $fields[$id] = array('visible' =>  !empty($visible[$id]) ? $visible[$id] : array(), 'hidden' => !empty($hidden[$id]) ? $hidden[$id] : array() );
        }

        return $fields;
    }

    private function process( $list )
    {
        $service = BOL_UserService::getInstance();

        $idList = array();

        foreach ( $list as $id )
        {
            $idList[$id] = $id;
        }

        $displayNameList = array();
        $userNameList = array();
        $userList = array();

        if ( !empty($idList) )
        {
            $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars($idList, false, true, true, false);
            $avtarsSrc = BOL_AvatarService::getInstance()->getAvatarsUrlList($idList, 2);

            foreach ( $avatars as $userId => $avatarData )
            {
                $avatars[$userId]['src'] = $avtarsSrc[$userId];
            }

            $userNameList = $service->getUserNamesForList($idList);

            $userList = $service->findUserListByIdList($idList);


//            $onlineInfo = $service->findOnlineStatusForUserList($idList);
//            $ownerIdList = array();
//
//            foreach ( $onlineInfo as $userId => $isOnline )
//            {
//                $ownerIdList[$userId] = $userId;
//            }
//
//            $eventParams = array(
//                'action' => 'base_view_my_presence_on_site',
//                'ownerIdList' => $ownerIdList,
//                'viewerId' => OW::getUser()->getId()
//            );
//
//            $permissions = OW::getEventManager()->getInstance()->call('privacy_check_permission_for_user_list', $eventParams);
//
//            foreach ( $onlineInfo as $userId => $isOnline )
//            {
//                // Check privacy permissions
//                if ( isset($permissions[$userId]['blocked']) && $permissions[$userId]['blocked'] == true )
//                {
//                    $showPresenceList[$userId] = false;
//                    continue;
//                }
//
//                $showPresenceList[$userId] = true;
//            }

            if ( $this->enableActions )
            {
                //$actions = USEARCH_CLASS_EventHandler::getInstance()->collectUserListActions($idList);
                //$this->assign('actions', $actions);
            }

            $this->assign('fields', $this->getFields($idList));
            $this->assign('usernameList', $userNameList);
            $this->assign('displayNameList', $displayNameList);
            $this->assign('avatars', $avatars);
        }

        $this->assign('list', $userList);
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $this->process($this->items);
    }
}