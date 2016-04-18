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

namespace Oxwall\Core;

/**
 * Web user class
 *
 * @author Nurlan Dzhumakaliev <nurlanj@live.com>
 * @since 1.8.3
 */
class User
{
    private static $classInstance;

    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct()
    {
        $this->auth = Auth::getInstance();

        if ( $this->isAuthenticated() )
        {
            $this->user = \BOL_UserService::getInstance()->findUserById($this->auth->getUserId());
        }
        else
        {
            $this->user = null;
        }
    }
    /**
     *
     * @var Auth
     */
    private $auth;
    /**
     * Current user object;
     *
     * @var \BOL_User
     */
    private $user;

    /**
     *
     * @param string $groupName
     * @param string $actionName
     * @param array $extra
     * @return boolean
     */
    public function isAuthorized( $groupName, $actionName = null, $extra = null )
    {
        if ( $extra !== null && !is_array($extra) )
        {
            trigger_error("`ownerId` parameter has been deprecated, pass `extra` parameter instead\n"
                . ErrorManager::getInstance()->debugBacktrace(), E_USER_WARNING);
        }

        return \BOL_AuthorizationService::getInstance()->isActionAuthorized($groupName, $actionName, $extra);
    }

    /**
     *
     * @param AuthAdapter $adapter
     * @return AuthResult
     */
    public function authenticate( $adapter )
    {
        $result = $this->auth->authenticate($adapter);

        if ( $this->isAuthenticated() )
        {
            $this->user = \BOL_UserService::getInstance()->findUserById($this->auth->getUserId());
        }

        return $result;
    }

    /**
     *
     * @return boolean
     */
    public function isAuthenticated()
    {
        return $this->auth->isAuthenticated();
    }

    /**
     * Get user id
     *
     * @return int
     */
    public function getId()
    {
        return ( $this->user === null ) ? 0 : $this->user->getId();
    }

    /**
     *
     * @return string
     */
    public function getEmail()
    {
        return ( $this->user === null ) ? '' : $this->user->email;
    }

    /**
     *
     * @return \BOL_User
     */
    public function getUserObject()
    {
        return $this->user;
    }

    public function isAdmin()
    {
        return $this->isAuthorized(\BOL_AuthorizationService::ADMIN_GROUP_NAME);
    }

    public function login( $userId )
    {
        $this->auth->login($userId);

        if ( $this->isAuthenticated() )
        {
            $this->user = \BOL_UserService::getInstance()->findUserById($this->auth->getUserId());
        }
    }

    public function logout()
    {
        if ( $this->isAuthenticated() )
        {
            $this->auth->logout();
            $this->user = null;
        }
    }
}

