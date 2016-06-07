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
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_core
 * @method static OW_Authorization getInstance()
 * @since 1.0
 */
class OW_Authorization
{
    use OW_Singleton;
    
    /**
     * @var BOL_AuthorizationService
     */
    private $service;

    /**
     * Constructor.
     */
    private function __construct()
    {
        $this->service = BOL_AuthorizationService::getInstance();
    }

    /**
     * Adds new group.
     *
     * @param string $name
     * @param boolean $moderated
     */
    public function addGroup( $name, $moderated = true )
    {
        if ( $this->service->findGroupByName($name) !== null )
        {
            trigger_error('Cant add group `' . $name . '`! Duplicate entry!', E_NOTICE);
            return;
        }

        $group = new BOL_AuthorizationGroup();
        $group->name = $name;
        $group->moderated = $moderated;

        $this->service->saveGroup($group);
    }

    /**
     * Adds new action to group.
     *
     * @param string $groupName
     * @param string $actionName
     * @param boolean $availableForGuest
     */
    public function addAction( $groupName, $actionName, $availableForGuest = false )
    {
        $group = $this->service->findGroupByName($groupName);

        if ( $group === null )
        {
            trigger_error('Cant add action `' . $actionName . '`! Empty group `' . $groupName . '`!');
            return;
        }

        if ( $this->service->findAction($groupName, $actionName) !== null )
        {
            trigger_error('Cant add action `' . $actionName . '` to group `' . $groupName . '`! Duplicate entry!');
            return;
        }

        $action = new BOL_AuthorizationAction();
        $action->groupId = $group->id;
        $action->name = $actionName;
        $action->availableForGuest = $availableForGuest;

        $this->service->saveAction($action);

        $roles = $this->service->getRoleList();
        foreach ( $roles as $role )
        {
            $this->service->grantActionListToRole($role, array($action));
        }
    }

    /**
     * Deletes group and all included actions.
     *
     * @param string $groupName
     */
    public function deleteGroup( $groupName )
    {
        $this->service->deleteGroup($groupName);
    }

    /**
     * Deletes action by group and action names.
     *
     * @param string $groupName
     * @param string $actionName
     */
    public function deleteAction( $groupName, $actionName )
    {
        $action = $this->service->findAction($groupName, $actionName);

        if ( $action !== null )
        {
            $this->service->deleteAction($action->id);
        }
    }

    /**
     * Checks if user authorized for group/action.
     *
     * @param integer $userId
     * @param string $groupName
     * @param string $actionName
     * @param array $extra
     * @return boolean
     */
    public function isUserAuthorized( $userId, $groupName, $actionName = null, $extra = null )
    {
        if ( $extra !== null && !is_array($extra) )
        {
            trigger_error("`ownerId` parameter has been deprecated, pass `extra` parameter instead");
        }

        return $this->service->isActionAuthorizedForUser($userId, $groupName, $actionName, $extra);
    }
}