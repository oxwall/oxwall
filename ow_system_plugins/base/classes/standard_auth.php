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
 * @author Madumarov Sardar <madumarov@gmail.com>
 * @package ow_system_plugins.base.classes
 * @since 1.0
 */
class BASE_CLASS_StandardAuth extends OW_AuthAdapter
{
    /**
     * @var string
     */
    private $identity;
    /**
     * @var string
     */
    private $password;
    /**
     * @var BOL_UserService
     */
    private $userService;

    /**
     * Constructor.
     *
     * @param string $identity
     * @param string $password
     */
    public function __construct( $identity, $password )
    {
        require_once(OW_DIR_LIB . 'password_compat' . DS . 'password.php');

        $this->identity = trim($identity);
        $this->password = trim($password);

        $this->userService = BOL_UserService::getInstance();
    }

    /**
     * @see OW_AuthAdapter::authenticate()
     *
     * @return OW_AuthResult
     */
    function authenticate()
    {
        $user = $this->userService->findUserForStandardAuth($this->identity);

        $language = OW::getLanguage();

        if ( $user === null )
        {
            return new OW_AuthResult(OW_AuthResult::FAILURE_IDENTITY_NOT_FOUND, null, array($language->text('base', 'auth_identity_not_found_error_message')));
        }
        
        if( $this->userService->checkPasswordChange($user->getId()) != null )
        {
            if( !password_verify($this->password . OW_PASSWORD_SALT, $user->getPassword()) )
            {
                return new OW_AuthResult(OW_AuthResult::FAILURE_PASSWORD_INVALID, null, array($language->text('base', 'auth_invlid_password_error_message')));
            }
            else
            {
                return new OW_AuthResult(OW_AuthResult::SUCCESS, $user->getId(), array($language->text('base', 'auth_success_message')));
            }
        }
        else
        {
            if ( $user->getPassword() !== BOL_UserService::getInstance()->hashPassword($this->password) )
            {
                return new OW_AuthResult(OW_AuthResult::FAILURE_PASSWORD_INVALID, null, array($language->text('base', 'auth_invlid_password_error_message')));
            }
            else
            {
                return new OW_AuthResult(OW_AuthResult::SUCCESS, $user->getId(), array($language->text('base', 'auth_success_message')));
                
                $this->userService->updatePassword($user->getId(), $this->password);
                $this->userService->updatePasswordChanged($user->getId());
            }
        }
    }
}
