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
    const EVENT_GET_FIELDS = "base.user_list.get_fields";

    protected $listKey;

    public function __construct($listKey, $list, $actions = false)
    {
        $this->listKey = $listKey;

        if ( $this->listKey == 'birthdays' )
        {
            $showOnline = false;
        }

        parent::__construct($list, $showOnline);
    }

    public function getFields( $userIdList )
    {
        $fields = array();
        BOL_UserService::getInstance()->getDisplayNamesForList($userIdList);

        foreach ( $userIdList as $id )
        {
            $fields[$id] = array();
        }

        $params = array(
            'list' => $this->listKey,
            'userIdList' => $userIdList  );

        $event = new OW_Event( self::EVENT_GET_FIELDS, $params, $fields);
        OW::getEventManager()->trigger($event);
        $data = $event->getData();

        return $data;
    }

    private function process( $list )
    {
        $service = BOL_UserService::getInstance();

        $idList = array();
        $userList = array();

        foreach ( $list as $dto )
        {
            $userList[$dto->getId()] = $dto;
            $idList[$dto->getId()] = $dto->getId();
        }

        $displayNameList = array();

        if ( !empty($idList) )
        {
            $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars($idList, false, true, true, false);
            $avtarsSrc = BOL_AvatarService::getInstance()->getAvatarsUrlList($idList, 2);

            foreach ( $avatars as $userId => $avatarData )
            {
                $avatars[$userId]['src'] = $avtarsSrc[$userId];
            }

            $usernameList = $service->getUserNamesForList($idList);
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

            if ( $this->actions )
            {
                $actions = USEARCH_CLASS_EventHandler::getInstance()->collectUserListActions($idList);
                $this->assign('actions', $actions);
            }

            $this->assign('fields', $this->getFields($idList));
            $this->assign('usernameList', $usernameList);
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