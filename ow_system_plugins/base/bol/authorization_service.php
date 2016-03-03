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
 * Authorization Service
 *
 * @author Nurlan Dzhumakaliev <nurlanj@live.com>, Aybat Duyshokov <duyshokov@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_AuthorizationService
{
    const ADMIN_GROUP_NAME = 'admin';
    const ON_BEFORE_ROLE_DELETE = 'base.on_before_role_delete';
    const ON_AFTER_ROLE_DELETE = 'base.on_after_role_delete';

    const STATUS_AVAILABLE = 'available';
    const STATUS_PROMOTED = 'promoted';
    const STATUS_DISABLED = 'disabled';

    /**
     * @var BOL_AuthorizationRoleDao
     */
    private $roleDao;

    /**
     * @var BOL_AuthorizationUserRoleDao
     */
    private $userRoleDao;

    /**
     * @var BOL_AuthorizationActionDao
     */
    private $actionDao;

    /**
     * @var BOL_AuthorizationGroupDao
     */
    private $groupDao;

    /**
     * @var BOL_AuthorizationPermissionDao
     */
    private $permissionDao;

    /**
     * @var BOL_AuthorizationModeratorDao
     */
    private $moderatorDao;

    /**
     * @var BOL_AuthorizationModeratorPermissionDao
     */
    private $moderatorPermissionDao;

    /**
     * Singleton instance.
     *
     * @var BOL_AuthorizationService
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_AuthorizationService
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }
    private $groupCache = array();
    private $moderatorCache = array();
    private $moderatorPermissionCache = array();
    private $actionCache = array();
    private $permissionCache = array();
    private $guestRoleId;
    private $userRolesCache;
    private $superModeratorUserId;
    private $groupDaoCache;
    private $actionDaoCache;
    private $roleDaoCache;

    private function __construct()
    {
        $this->roleDao = BOL_AuthorizationRoleDao::getInstance();
        $this->userRoleDao = BOL_AuthorizationUserRoleDao::getInstance();
        $this->actionDao = BOL_AuthorizationActionDao::getInstance();
        $this->groupDao = BOL_AuthorizationGroupDao::getInstance();
        $this->permissionDao = BOL_AuthorizationPermissionDao::getInstance();
        $this->moderatorDao = BOL_AuthorizationModeratorDao::getInstance();
        $this->moderatorPermissionDao = BOL_AuthorizationModeratorPermissionDao::getInstance();

        $this->groupDaoCache = $this->groupDao->findAll();
        foreach ( $this->groupDaoCache as $group )
        {
            /* @var $group BOL_AuthorizationGroup */
            $this->groupCache[$group->name] = $group->id;
        }

        $moderatorDaoCache = $this->moderatorDao->findAll();
        $this->superModeratorUserId = 0;

        foreach ( $moderatorDaoCache as $moderator )
        {
            /* @var $moderator BOL_AuthorizationModerator */
            $this->moderatorCache[$moderator->userId] = $moderator->id;

            if ( $this->superModeratorUserId === 0
                || (int) $this->moderatorCache[$moderator->userId] < (int) $this->moderatorCache[$this->superModeratorUserId] )
            {
                $this->superModeratorUserId = (int) $moderator->userId;
            }
        }

        $moderatorPermissionDaoCache = $this->moderatorPermissionDao->findAll();
        foreach ( $moderatorPermissionDaoCache as $perm )
        {
            /* @var $perm BOL_AuthorizationModeratorPermission */
            $this->moderatorPermissionCache[$perm->moderatorId][$perm->groupId] = $perm->id;
        }

        $this->actionDaoCache = $this->actionDao->findAll();
        foreach ( $this->actionDaoCache as $action )
        {
            /* @var $action BOL_AuthorizationAction */
            $this->actionCache[$action->name][$action->groupId] = $action->id;
        }

        $this->userRolesCache = array();
        if ( OW::getUser()->isAuthenticated() )
        {
            $this->userRolesCache[OW::getUser()->getId()] = $this->userRoleDao->getRoleIdList(OW::getUser()->getId());
        }

        $permissionDaoCache = $this->permissionDao->findAll();
        foreach ( $permissionDaoCache as $permission )
        {
            /* @var $permission BOL_AuthorizationPermission */
            $this->permissionCache[$permission->actionId][$permission->roleId] = $permission->id;
        }

        $this->roleDaoCache = $this->roleDao->findAll();
        $this->guestRoleId = $this->getGuestRoleId();
    }
    /* ----------------------------------------- */

    public function getGuestRoleId()
    {
        /* @var $roleItem BOL_AuthorizationRole */
        foreach ( $this->roleDaoCache as $roleItem )
        {
            if ( $roleItem->getName() == BOL_AuthorizationRoleDao::GUEST )
            {
                return $roleItem->getId();
            }
        }

        return null;
    }

    public function findNonGuestRoleList()
    {
        $result = array();

        /* @var $roleItem BOL_AuthorizationRole */
        foreach ( $this->roleDaoCache as $roleItem )
        {
            if ( $roleItem->getName() != BOL_AuthorizationRoleDao::GUEST )
            {
                $result[] = $roleItem;
            }
        }

        return $result;
    }
    /* ---------------------------------------- */

    /**
     *
     * @param $groupName
     * @param $actionName
     * @param bool $readFromLocalCache
     * @return BOL_AuthorizationAction
     */
    public function findAction( $groupName, $actionName, $readFromLocalCache = false )
    {
        $groupDto = null;

        if ( $readFromLocalCache )
        {
            /* @var $groupItem BOL_AuthorizationGroup */
            foreach ( $this->groupDaoCache as $groupItem )
            {
                if ( $groupItem->getName() == $groupName )
                {
                    $groupDto = $groupItem;
                    break;
                }
            }
        }
        else
        {
            $groupDto = $this->groupDao->findByName($groupName);
        }

        if ( $groupDto === null )
        {
            return null;
        }

        if ( $readFromLocalCache )
        {

            /* @var $actionItem BOL_AuthorizationAction */
            foreach ( $this->actionDaoCache as $actionItem )
            {
                if ( $actionItem->getGroupId() == $groupDto->getId() && $actionItem->getName() == $actionName )
                {
                    return $actionItem;
                }
            }
        }
        else
        {
            return $this->actionDao->findAction($actionName, $groupDto->getId());
        }

        return null;
    }

    /**
     * @param string
     * @return BOL_AuthorizationGroup $groupName
     */
    public function findGroupByName( $groupName )
    {
        if ( !$groupName )
        {
            return null;
        }

        return $this->groupDao->findByName($groupName);
    }

    public function getGroupList( $excludeNonModerated = false )
    {
        $groups = $this->groupDao->findAll();

        foreach ( $groups as $key => $value )
        {
            /* @var $value BOL_AuthorizationGroup */
            if ( $excludeNonModerated && !$value->isModerated() )
            {
                unset($groups[$key]);
            }
        }

        return $groups;
    }

    public function getModeratorList()
    {
        return $this->moderatorDao->findAll();
    }

    public function getRoleList()
    {
        return $this->roleDao->findAll();
    }

    public function getActionList()
    {
        return $this->actionDao->findAll();
    }

    public function getPermissionList()
    {
        return $this->permissionDao->findAll();
    }

    public function getModeratorPermissionList()
    {
        return $this->moderatorPermissionDao->findAll();
    }

    public function getModeratorIdByUserId( $userId )
    {
        $userId = (int) $userId;
        if ( $userId > 0 )
        {
            return $this->moderatorDao->getIdByUserId($userId);
        }

        return null;
    }

    /**
     * @param $groupName
     * @param null $actionName
     * @param array $extra
     *
     * @return boolean
     */
    public function isActionAuthorized( $groupName, $actionName = null, $extra = null )
    {
        if ( $extra !== null && !is_array($extra) )
        {
            trigger_error("`ownerId` parameter has been deprecated, pass `extra` parameter instead\n"
                . OW_ErrorManager::getInstance()->debugBacktrace(), E_USER_WARNING);
        }

        if ( !empty($extra['userId']) )
        {
            $userId = (int) $extra['userId'];
        }
        else
        {
            $userId = OW::getUser()->isAuthenticated() ? OW::getUser()->getId() : 0;
        }

        $isAuthorized = $this->isActionAuthorizedForUser($userId, $groupName, $actionName);

        if ( $isAuthorized )
        {
            return true;
        }

        if ( !$userId && !$this->isActionAuthorizedForGuest($groupName, $actionName) )
        {
            return false;
        }

        // layer check
        $eventParams = array(
            'userId' => $userId,
            'groupName' => $groupName,
            'actionName' => $actionName,
            'extra' => $extra
        );
        
        try
        {
            $event = new BASE_CLASS_EventCollector('authorization.layer_check', $eventParams);
            OW::getEventManager()->trigger($event);
            $data = $event->getData();
        }
        catch ( Exception $ex )
        {
            OW::getLogger()->addEntry($ex->getMessage() . "\n" . print_r($ex->getTrace(), true) );
        }

        if ( !empty($data) )
        {
            usort($data, array($this, 'sortLayersByPriorityAsc'));

            foreach ( $data as $layer )
            {
                if ( $layer['permission'] === true )
                {
                    return true;
                }
            }
        }

        return $isAuthorized;
    }

    /**
     * @param $groupName
     * @param null $actionName
     * @param array $extra
     *
     * @return boolean
     */
    public function isActionAuthorizedBy( $groupName, $actionName = null, array $extra = null )
    {
        if ( !empty($extra['userId']) )
        {
            $userId = (int) $extra['userId'];
        }
        else
        {
            $userId = OW::getUser()->isAuthenticated() ? OW::getUser()->getId() : 0;
        }

        $isAuthorized = array(
            'status' => $this->isActionAuthorizedForUser($userId, $groupName, $actionName),
            'authorizedBy' => 'base'
        );

        if ( $isAuthorized['status'] )
        {
            return $isAuthorized;
        }
        
        try
        {
            // layer check
            $eventParams = array(
                'userId' => $userId,
                'groupName' => $groupName,
                'actionName' => $actionName,
                'extra' => $extra
            );
            $event = new BASE_CLASS_EventCollector('authorization.layer_check', $eventParams);
            OW::getEventManager()->trigger($event);
            $data = $event->getData();
        }
        catch ( Exception $ex )
        {
            OW::getLogger()->addEntry($ex->getMessage() . "\n" . print_r($ex->getTrace(), true) );
        }

        if ( !empty($data) )
        {
            usort($data, array($this, 'sortLayersByPriorityAsc'));

            foreach ( $data as $layer )
            {
                if ( $layer['permission'] === true )
                {
                    return array('status' => true, 'authorizedBy' => $layer['pluginKey']);
                }
            }
        }

        return $isAuthorized;
    }

    private function sortLayersByPriorityAsc( $el1, $el2 )
    {
        if ( $el1['priority'] === $el2['priority'] )
        {
            return 0;
        }

        return $el1['priority'] > $el2['priority'] ? 1 : -1;
    }

    /**
     * @param $groupName
     * @param null $actionName
     * @param array $extra
     * @return string
     */
    public function getActionStatus( $groupName, $actionName = null, array $extra = null )
    {
        if ( !empty($extra['userId']) )
        {
            $userId = (int) $extra['userId'];
        }
        else
        {
            $userId = OW::getUser()->isAuthenticated() ? OW::getUser()->getId() : 0;
        }

        $isAuthorized = $this->isActionAuthorizedBy($groupName, $actionName, $extra);

        if ( $isAuthorized['status'] )
        {
            return array('status' => self::STATUS_AVAILABLE, 'msg' => null, 'authorizedBy' => $isAuthorized['authorizedBy']);
        }

        $lang = OW::getLanguage();

        $error = array(
            'status' => self::STATUS_DISABLED,
            'msg' => $lang->text('base', 'authorization_failed_feedback')
        );

        if ( !$userId && !$this->isActionAuthorizedForGuest($groupName, $actionName) )
        {
            return $error;
        }

        try
        {
            // layer check
            $eventParams = array(
                'userId' => $userId,
                'groupName' => $groupName,
                'actionName' => $actionName,
                'extra' => $extra
            );
            $event = new BASE_CLASS_EventCollector('authorization.layer_check_collect_error', $eventParams);
            OW::getEventManager()->trigger($event);
            $data = $event->getData();
        }
        catch ( Exception $ex )
        {
            OW::getLogger()->addEntry($ex->getMessage() . "\n" . print_r($ex->getTrace(), true) );
        }

        if ( empty($data) )
        {
            return $error;
        }

        usort($data, array($this, 'sortLayersByPriorityAsc'));

        $links = array();
        foreach ( $data as $option )
        {
            if ( !empty($option['label']) )
            {
                $label = mb_strtolower($option['label']);
                $links[] = !empty($option['url']) ? '<a href="'.$option['url'].'">'.$label.'</a>' : $label;
            }
        }

        if ( count($links) )
        {
            $actionLabel = $this->getActionLabel($groupName, $actionName);

            $error = array(
                'status' => self::STATUS_PROMOTED,
                'msg' => $lang->text(
                    'base',
                    'authorization_action_promotion',
                    array('alternatives' => implode(' '.$lang->text('base', 'or').' ', $links), 'action' => mb_strtolower($actionLabel))
                )
            );
        }

        return $error;
    }

    public function trackAction( $groupName, $actionName = null, array $extra = null )
    {
        return $this->trackActionForUser( 0, $groupName, $actionName, $extra );
    }

    public function trackActionForUser( $userId, $groupName, $actionName = null, array $extra = null )
    {
        $userId = !empty($userId) ? $userId : (OW::getUser()->isAuthenticated() ? OW::getUser()->getId() : 0);

        $isAuthorized = $this->isActionAuthorizedBy($groupName, $actionName, $extra);
        $defaults = array('status' => false, 'msg' => null, 'trackedBy' => null);

        if ( $isAuthorized['status'] && $isAuthorized['authorizedBy'] == 'base' )
        {
            return $defaults;
        }

        // layer check
        $eventParams = array(
            'userId' => $userId,
            'groupName' => $groupName,
            'actionName' => $actionName,
            'extra' => $extra
        );

        $event = new BASE_CLASS_EventCollector('authorization.layer_check_track_action', $eventParams);
        OW::getEventManager()->trigger($event);
        $data = $event->getData();

        if ( !$data )
        {
            return $defaults;
        }

        usort($data, array($this, 'sortLayersByPriorityAsc'));

        foreach ( $data as $layer )
        {
            if ( !empty($layer['msg']) )
            {
                return array('status' => true, 'msg' => $layer['msg'], 'trackedBy' => $layer['pluginKey']);
            }
        }

        return $defaults;
    }

    public function isActionAuthorizedForUser( $userId, $groupName, $actionName = null )
    {
        $userId = (int) $userId;

        if ( isset($this->groupCache[$groupName]) )
        {
            $groupId = $this->groupCache[$groupName];
        }
        else
        {
            return false;
        }

        // contains user's role ids
        $roles = array();

        if ( $userId > 0 || OW::getUser()->isAuthenticated() )
        {
            $userId = ( $userId > 0 ) ? $userId : OW::getUser()->getId();

            if ( $actionName === null )
            {
                if ( isset($this->moderatorCache[$userId]) )
                {
                    $moderatorId = $this->moderatorCache[$userId];
//                    $adminGroupId = $this->groupCache[self::ADMIN_GROUP_NAME];

                    return isset($this->moderatorPermissionCache[$moderatorId][$groupId])
//                        || isset($this->moderatorPermissionCache[$moderatorId][$adminGroupId])
                    || $this->isSuperModerator($userId);
                }
                else
                {
                    return false;
                }
            }

            if ( !array_key_exists($userId, $this->userRolesCache) )
            {
                $this->userRolesCache[$userId] = $this->userRoleDao->getRoleIdList($userId);
            }
            $roles = $this->userRolesCache[$userId];
        }
        else
        {
            $roles[] = $this->guestRoleId;
        }

        if ( isset($this->actionCache[$actionName][$groupId]) )
        {
            $actionId = $this->actionCache[$actionName][$groupId];
        }
        else
        {
            return false;
        }

        $permissionId = null;

        foreach ( $roles as $role )
        {
            if ( isset($this->permissionCache[$actionId][$role]) )
            {
                $permissionId = $this->permissionCache[$actionId][$role];
                break;
            }
        }

        return ( $permissionId !== null /* && (int)$permissionId > 0 */ );
    }

    public function isActionAuthorizedForGuest( $groupName, $actionName = null )
    {
        if ( isset($this->groupCache[$groupName]) )
        {
            $groupId = $this->groupCache[$groupName];
        }
        else
        {
            return false;
        }

        // contains user's role ids
        $roles = array( $this->guestRoleId );

        if ( isset($this->actionCache[$actionName][$groupId]) )
        {
            $actionId = $this->actionCache[$actionName][$groupId];
        }
        else
        {
            return false;
        }

        $permissionId = null;

        foreach ( $roles as $role )
        {
            if ( isset($this->permissionCache[$actionId][$role]) )
            {
                $permissionId = $this->permissionCache[$actionId][$role];
                break;
            }
        }

        return ( $permissionId !== null /* && (int)$permissionId > 0 */ );
    }

    public function countUserByRoleId( $id )
    {
        return $this->userRoleDao->countByRoleId($id);
    }

    public function isModerator( $userId = null )//TODO rewrite this method
    {
        if ( $userId == null && ( $userId = OW::getUser()->getId() ) === null )
        {
            return false;
        }

        return (bool) $this->getModeratorIdByUserId($userId);
    }

    public function findUserRoleList( $userId )
    {
        return $this->roleDao->findUserRoleList($userId);
    }

    public function findGroupIdByName( $name )
    {
        $id = $this->groupDao->getIdByName($name);

        return ( $id === null ) ? 0 : (int) $id;
    }

    public function findActionListByGroupId( $groupId )
    {
        if ( $groupId === null || $groupId < 1 )
        {
            return array();
        }

        return $this->actionDao->findActionListByGroupId($groupId);
    }

    /**
     * @param int $id
     * @throws InvalidArgumentException
     * @return BOL_AuthorizationRole
     */
    public function getRoleById( $id )
    {
        $id = (int) $id;
        if ( $id < 1 )
        {
            throw new InvalidArgumentException('invalid role id');
        }

        return $this->roleDao->findById($id);
    }

    public function saveModeratorPermissionList( array $perms, $userId )
    {
        $moderatorId = $this->getModeratorIdByUserId($userId);

        if ( $moderatorId === null )
        {
            return;
        }

        $isSuperAdmin = $this->isSuperModerator($userId);

        $superModeratorId = (int) $this->getModeratorIdByUserId($this->getSuperModeratorUserId());

        $adminGroupId = $this->getAdminGroupId();

        // delete old
        $oldPerms = $this->getModeratorPermissionList();
        foreach ( $oldPerms as $perm )
        {
            /* @var $perm BOL_AuthorizationModeratorPermission */

            if ( (int) $perm->getGroupId() === $adminGroupId && !$isSuperAdmin )
            {
                continue;
            }

            if ( (int) $perm->getModeratorId() === $superModeratorId )
            {
                continue;
            }

            $match = false;
            foreach ( $perms as $value )
            {
                /* @var $value BOL_AuthorizationModeratorPermission */
                if ( (int) $value->groupId === (int) $perm->groupId && (int) $value->moderatorId === (int) $perm->moderatorId )
                {
                    $match = true;
                    break;
                }
            }

            if ( !$match )
            {
                $this->moderatorPermissionDao->delete($perm);
            }
        }

        // add new
        foreach ( $perms as $perm )
        {
            /* @var $perm BOL_AuthorizationModeratorPermission */

            if ( (int) $perm->getGroupId() === $adminGroupId && !$isSuperAdmin )
            {
                continue;
            }

            if ( (int) $perm->getModeratorId() === $superModeratorId )
            {
                continue;
            }

            $oldPermId = $this->moderatorPermissionDao->getId($perm->getModeratorId(), $perm->getGroupId());
            if ( $oldPermId !== null )
            {
                $perm->setId($oldPermId);
            }

            $this->moderatorPermissionDao->save($perm);
        }
    }

    public function addModerator( $userId )
    {
        $userId = (int) $userId;

        if ( $this->moderatorDao->getIdByUserId($userId) !== null )
        {
            return false;
        }

        $moder = new BOL_AuthorizationModerator();
        $moder->userId = $userId;
        $this->moderatorDao->save($moder);

        $this->giveAllPermissions($moder->getId());

        return true;
    }

    public function giveAllPermissions( $moderatorId )
    {
        $this->moderatorPermissionDao->deleteByModeratorId($moderatorId);

        $groups = $this->getGroupList(true);

        $adminGroupId = $this->getAdminGroupId();

        foreach ( $groups as $group )
        {
            /** @var $group BOL_AuthorizationGroup */
            if ( (int) $group->getId() === (int) $adminGroupId )
            {
                continue;
            }

            $permisson = new BOL_AuthorizationModeratorPermission();
            $permisson->setGroupId($group->getId())->setModeratorId($moderatorId);

            $this->saveModeratorPermission($permisson);
        }
    }

    public function addAdministrator( $userId )
    {
        $this->addModerator($userId);

        $moderatorId = $this->moderatorDao->getIdByUserId($userId);

        $groupId = $this->getAdminGroupId();

        $permisson = new BOL_AuthorizationModeratorPermission();
        $permisson->setGroupId($groupId)
            ->setModeratorId($moderatorId);

        $this->saveModeratorPermission($permisson);
    }

    public function deleteModerator( $moderatorId )
    {
        $moderatorId = (int) $moderatorId;

        $adminGroupId = $this->getAdminGroupId();
        if ( $this->moderatorPermissionDao->getId($moderatorId, $adminGroupId) !== null )
        {
            return false;
        }

        $removed = ( $this->moderatorDao->deleteById($moderatorId) > 0 ); // ? true : false;

        if ( $removed )
        {
            $this->moderatorPermissionDao->deleteByModeratorId($moderatorId);
        }

        return $removed;
    }

    public function findAdminIdList()
    {
        $adminGroupId = $this->getAdminGroupId();
        $moderPerms = $this->moderatorPermissionDao->findListByGroupId($adminGroupId);
        $adminIds = array();
        foreach ( $moderPerms as $perm )
        {
            /* @var $perm BOL_AuthorizationModeratorPermission */
            $adminIds[] = $perm->moderatorId;
        }

        return $adminIds;
    }

    public function getDefaultRole()
    {
        return $this->roleDao->findDefault();
    }

    public function getRoleListOfUsers( array $userIds, $displayLabel = true )
    {
        $userIds = array_unique($userIds);
        $roles = $this->userRoleDao->getRoleListOfUsers($userIds, $displayLabel);
        $keyRoles = array();
        foreach ( $roles as $key => &$role )
        {
            $keyRoles[$role['userId']] = &$role;
            $role['label'] = $this->getRoleLabel($role['name']);
        }

        return $keyRoles;
    }

    public function getRoleLabel( $roleName )
    {
        return OW::getLanguage()->text('base', "authorization_role_{$roleName}");
    }

    public function getActionLabel( $groupName, $actionName )
    {
        // collecting labels
        $event = new BASE_CLASS_EventCollector('admin.add_auth_labels');
        OW::getEventManager()->trigger($event);
        $data = $event->getData();

        $dataLabels = empty($data) ? array() : call_user_func_array('array_merge', $data);

        return !empty($dataLabels[$groupName]['actions'][$actionName]) ? $dataLabels[$groupName]['actions'][$actionName] : null;
    }

    public function isSuperModerator( $userId )
    {
        return (int) $userId === (int) $this->superModeratorUserId;
    }

    public function getSuperModeratorUserId()
    {
        return $this->superModeratorUserId;
    }

    public function getAdminGroupId()
    {
        return (int) $this->findGroupIdByName(self::ADMIN_GROUP_NAME);
    }

    public function savePermissionList( array $perms )//TODO check action available for guest
    {
        $this->permissionDao->deleteAll();
        foreach ( $perms as $perm )
        {
            $this->permissionDao->save($perm);
        }
    }

    public function saveUserRole( $userId, $roleId )
    {
        $this->deleteUserRole($userId, $roleId);

        $userRole = new BOL_AuthorizationUserRole();
        $userRole->userId = (int) $userId;
        $userRole->roleId = (int) $roleId;

        $this->userRoleDao->deleteUserRole($userRole->userId, $userRole->roleId);
        $this->userRoleDao->save($userRole);
    }

    public function assignDefaultRoleToUser( $userId )
    {
        $role = $this->getDefaultRole();
        $this->saveUserRole((int) $userId, $role->id);
    }

    public function reorderRoles( $list )
    {
        $idList = array_keys($list);

        $roles = $this->roleDao->findByIdList($idList);

        foreach ( $roles as $role )
        {
            /* @var $role BOL_AuthorizationRole */
            $this->roleDao->save($role->setSortOrder($list[$role->getId()]));
        }
    }

    public function deleteRoleById( $id )
    {
        /** @var BOL_AuthorizationRole $role */
        $role = $this->roleDao->findById($id);

        $eventBefore = new OW_Event(self::ON_BEFORE_ROLE_DELETE, array('roleId' => $role->getId()));
        OW::getEventManager()->trigger($eventBefore);

        $languageService = BOL_LanguageService::getInstance();

        $key = $languageService->findKey('base', "authorization_role_{$role->getName()}");

        if ( !empty($key) )
        {
            $languageService->deleteKey($key->getId());
        }

        $this->userRoleDao->onDeleteRole($role->getId(), $this->getDefaultRole()->getId());

        $this->roleDao->deleteById($role->getId());

        //TODO delete from Permission

        $eventAfter = new OW_Event(self::ON_AFTER_ROLE_DELETE, array('roleId' => $role->getId()));
        OW::getEventManager()->trigger($eventAfter);
    }

    public function addRole( $label )
    {
        $languageService = BOL_LanguageService::getInstance();

        $i = 0;
        $name = $languageService->generateCustomKey($label);

        $unique = "authorization_role_{$name}";

        while ( !$languageService->isKeyUnique('base', $unique) )
        {
            $i++;

            $unique = "authorization_role_{$name}" . $i;
        }
        if ( $i > 0 )
        {
            $name .= $i;
        }

        $key = $unique;

        $role = new BOL_AuthorizationRole();
        $role->setName($name);
        $role->setSortOrder($this->roleDao->findMaxOrder() + 1);

        $this->roleDao->save($role);

        $languageService->addValue($languageService->getCurrent()->getId(), 'base', $key, $label, true);
    }

    public function saveRole( BOL_AuthorizationRole $role )
    {
        $this->roleDao->save($role);
    }

    public function saveModeratorPermission( $dto )
    {
        $this->moderatorPermissionDao->save($dto);
    }

    public function deleteUserRolesByUserId( $userId )
    {
        $userId = (int) $userId;
        if ( $userId > 0 )
        {
            $this->userRoleDao->deleteByUserId($userId);
        }
    }

    public function deleteUserRole( $userId, $roleId )
    {
        $userId = (int) $userId;
        $roleId = (int) $roleId;

        if ( $userId > 0 && $roleId > 0 )
        {
            $this->userRoleDao->deleteUserRole($userId, $roleId);
        }
    }

    public function grantActionListToRole( BOL_AuthorizationRole $role, array $actions )
    {
        if ( $role === null || empty($actions) )
        {
            return;
        }

        /* @var $action BOL_AuthorizationAction */
        foreach ( $actions as $action )
        {
            if ( !$action->isAvailableForGuest() && ( (int) $role->id === (int) $this->guestRoleId ) )
            {
                continue;
            }

            $perm = $this->permissionDao->findByRoleIdAndActionId($role->id, $action->id);
            if ( $perm === null )
            {
                $perm = new BOL_AuthorizationPermission();
                $perm->actionId = $action->id;
                $perm->roleId = $role->id;
                $this->permissionDao->save($perm);
            }
        }
    }

    /**
     *
     * @param BOL_AuthorizationGroup $group
     * @param array $labels ex.: array('en' => 'Colour', 'en-US' => 'Color')
     * @throws InvalidArgumentException
     */
    public function addGroup( BOL_AuthorizationGroup $group, array $labels )
    {
        if ( $group === null )
        {
            throw new InvalidArgumentException('$group cannot be null');
        }

        $this->groupDao->save($group);

        foreach ( $labels as $tag => $label )
        {
            $lang = BOL_LanguageService::getInstance()->findByTag($tag);
            if ( $lang !== null )
            {
                BOL_LanguageService::getInstance()
                    ->addValue($lang->id, 'base', 'authorization_group_' . mb_strtolower($group->name), $label);
            }
        }

        if ( $group->isModerated() )
        {
            $adminIds = $this->findAdminIdList();
            if ( count($adminIds) > 0 )
            {
                foreach ( $adminIds as $adminId )
                {
                    $perm = new BOL_AuthorizationModeratorPermission();
                    $perm->groupId = $group->id;
                    $perm->moderatorId = $adminId;
                    $this->moderatorPermissionDao->save($perm);
                }
            }
        }
    }

    public function deleteGroup( $groupName )
    {
        $group = $this->groupDao->findByName($groupName);

        if ( $group !== null )
        {
            if ( $group->isModerated() )
            {
                $this->moderatorPermissionDao->deleteByGroupId($group->id);
            }
            $actions = $this->actionDao->findActionListByGroupId($group->id);

            if ( !empty($actions) )
            {
                foreach ( $actions as $action )
                {
                    $this->deleteAction($action->id);
                }
            }

            $this->groupDao->deleteById($group->id);
        }
    }

    /**
     *
     * @param BOL_AuthorizationAction $action
     * @param array $labels ex.: array('en' => 'Colour', 'en-US' => 'Color')
     * @throws InvalidArgumentException
     */
    public function addAction( BOL_AuthorizationAction $action, array $labels )
    {
        if ( $action === null )
        {
            throw new InvalidArgumentException('action cannot be null');
        }
        $this->actionDao->save($action);

        /** @var BOL_AuthorizationGroup $group */
        $group = $this->groupDao->findById($action->groupId);
        foreach ( $labels as $tag => $label )
        {
            $lang = BOL_LanguageService::getInstance()->findByTag($tag);
            if ( $lang !== null )
            {
                $key = 'authorization_action_' . mb_strtolower($group->name) . '_' . mb_strtolower($action->name);
                try
                {
                    BOL_LanguageService::getInstance()->addValue($lang->id, 'base', $key, $label);
                }
                catch ( Exception $e )
                {

                }
            }
        }

        $roles = $this->getRoleList();
        foreach ( $roles as $role )
        {
            $this->grantActionListToRole($role, array($action));
        }
    }

    public function deleteAction( $actionId )
    {
        $actionId = (int) $actionId;
        if ( $actionId > 0 )
        {
            $this->permissionDao->deleteByActionId($actionId);
            $this->actionDao->deleteById($actionId);
        }
    }

    public function saveGroup( BOL_AuthorizationGroup $group )
    {
        $this->groupDao->save($group);
    }

    public function saveAction( BOL_AuthorizationAction $action )
    {
        $this->actionDao->save($action);
    }

    public function deleteGroupByName( $groupName )
    {
        $group = $this->groupDao->findByName($groupName);

        if ( $group !== null )
        {
            if ( $group->isModerated() )
            {
                $this->moderatorPermissionDao->deleteByGroupId($group->id);
            }
            $actions = $this->actionDao->findActionListByGroupId($group->id);

            if ( !empty($actions) )
            {
                foreach ( $actions as $action )
                {
                    $this->deleteActionById($action->id);
                }
            }

            $this->groupDao->deleteById($group->id);
        }
    }

    public function deleteActionById( $actionId )
    {
        $actionId = (int) $actionId;

        if ( $actionId > 0 )
        {
            $this->permissionDao->deleteByActionId($actionId);
            $this->actionDao->deleteById($actionId);
        }
    }
}
