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
 * Unsubscribe mass mailing users
 *
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow_system_plugins.base.controllers
 * @since 1.0
 */

class BASE_CTRL_Unsubscribe extends OW_ActionController
{
    private $unsubscribeServise;
    private $userServise;

    public function __construct()
    {
        $this->unsubscribeServise = BOL_MassMailingIgnoreUserService::getInstance();
        $this->userServise = BOL_UserService::getInstance();
    }

    public function index( $params )
    {
        if( OW::getRequest()->isAjax() )
        {
            exit;
        }
        
        $language = OW::getLanguage();

        $this->setPageHeading( $language->text( 'base', 'massmailing_unsubscribe' ) );

        $code = null;
        $userId = null;

        $result = false;

        if( isset($params['code']) && isset($params['id']) )
        {
            $result = 'confirm';
            
            if ( !empty($_POST['cancel']) )
            {
                $this->redirect(OW_URL_HOME);
            }


            $code = trim($params['code']);
            $userId = $params['id'];
            $user = $this->userServise->findUserById($userId);
            if ( $user !== null )
            {
                if( md5( $user->username . $user->password ) ===  $code )
                {
                    $result = 'confirm';
                    if (!empty( $_POST['confirm'] ) )
                    {   
                        BOL_PreferenceService::getInstance()->savePreferenceValue('mass_mailing_subscribe', false, $user->id);
                        $result = true;
                        OW::getFeedback()->info($language->text('base', 'massmailing_unsubscribe_successful'));
                        $this->redirect(OW_URL_HOME);
                    }
                }
            }
        }

        $this->assign('result', $result);
    }
    
    public function apiUnsubscribe($params)
    {
        if ( empty($params['emails']) || !is_array($params['emails']) )
        {
            throw new InvalidArgumentException('Invalid email list');
        }
        
        foreach ( $params['emails'] as $email )
        {
            $user = BOL_UserService::getInstance()->findByEmail($email);
            
            if ( $user === null )
            {
                throw new LogicException('User with email ' . $email . ' not found');
            }
            
            BOL_PreferenceService::getInstance()->savePreferenceValue('mass_mailing_subscribe', false, $user->id);
        }
    }
}

?>
