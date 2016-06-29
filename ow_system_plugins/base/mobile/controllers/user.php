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
 * @package ow.ow_system_plugins.base.controllers
 * @since 1.0
 */
class BASE_MCTRL_User extends OW_MobileActionController
{
    /**
     * @var BOL_UserService
     */
    private $userService;

    public function __construct()
    {
        parent::__construct();
        $this->userService = BOL_UserService::getInstance();
    }

    public function signIn()
    {
        $form = $this->userService->getSignInForm();

        if ( !$form->isValid($_POST) )
        {
            exit(json_encode(array('result' => false, 'message' => 'Error!')));
        }

        $data = $form->getValues();
        $result = $this->userService->processSignIn($data['identity'], $data['password'], true);

        $message = '';

        foreach ( $result->getMessages() as $value )
        {
            $message .= $value;
        }

        if ( $result->isValid() )
        {
            exit(json_encode(array('result' => true, 'message' => $message)));
        }
        else
        {
            exit(json_encode(array('result' => false, 'message' => $message)));
        }
    }

    public function standardSignIn()
    {
        if ( OW::getRequest()->isAjax() )
        {
            exit(json_encode(array()));
        }

        if ( OW::getUser()->isAuthenticated() )
        {
            throw new RedirectException(OW_URL_HOME);
        }

        if ( OW::getRequest()->isPost() )
        {
            $form = $this->userService->getSignInForm();

            if ( !$form->isValid($_POST) )
            {
                OW::getFeedback()->error("Error");
                $this->redirect();
            }

            $data = $form->getValues();
            $result = $this->userService->processSignIn($data['identity'], $data['password'], isset($data['remember']));

            $message = '';

            foreach ( $result->getMessages() as $value )
            {
                $message .= $value;
            }

            if ( $result->isValid() )
            {
                OW::getFeedback()->info($message);

                if ( empty($_GET['back-uri']) )
                {
                    $this->redirect();
                }

                $this->redirect(OW::getRouter()->getBaseUrl() . urldecode($_GET['back-uri']));
            }
            else
            {
                OW::getFeedback()->error($message);
                $this->redirect();
            }
        }

        OW::getDocument()->getMasterPage()->setRButtonData(array('extraString' => ' style="display:none;"'));
        $this->addComponent('signIn', new BASE_MCMP_SignIn(false));

        // set meta info
        $params = array(
            "sectionKey" => "base.base_pages",
            "entityKey" => "sign_in",
            "title" => "base+meta_title_sign_in",
            "description" => "base+meta_desc_sign_in",
            "keywords" => "base+meta_keywords_sign_in"
        );

        OW::getEventManager()->trigger(new OW_Event("base.provide_page_meta_info", $params));
    }

    /**
     * 
     * @param array $params
     * @return BOL_User
     * @throws Redirect404Exception
     * @throws RedirectException
     */
    protected function checkProfilePermissions( $params )
    {
        $userService = BOL_UserService::getInstance();
        /* @var $userDto BOL_User */
        $userDto = $userService->findByUsername($params['username']);     

        if ( $userDto === null )
        {
            throw new Redirect404Exception();
        }
        

        if ( (OW::getUser()->isAuthenticated() && OW::getUser()->getId() != $userDto->id || !OW::getUser()->isAuthenticated()) && !OW::getUser()->isAuthorized('base', 'view_profile')  )
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('base', 'view_profile');
            $this->assign('permissionMessage', $status['msg']);
            return null;
        }
        
        $isSuspended = $userService->isSuspended($userDto->id);
        
        if ( $isSuspended )
        {   
            $this->assign('permissionMessage', OW::getLanguage()->text('base', 'user_page_suspended'));
            return null;
        }
        
        $eventParams = array(
            'action' => 'base_view_profile',
            'ownerId' => $userDto->id,
            'viewerId' => OW::getUser()->getId()
        );

        $displayName = BOL_UserService::getInstance()->getDisplayName($userDto->id);

        try
        {
            OW::getEventManager()->getInstance()->call('privacy_check_permission', $eventParams);
        }
        catch ( RedirectException $ex )
        {
            throw new RedirectException(OW::getRouter()->urlForRoute('base_user_privacy_no_permission', array('username' => $displayName)));
        }

        return $userDto;
    }

    public function profile( $params )
    {
        $userDto = $this->checkProfilePermissions($params);

        if ( $userDto === null )
        {
            return;
        }

        $displayName = BOL_UserService::getInstance()->getDisplayName($userDto->id);

        $this->setPageTitle(OW::getLanguage()->text('base', 'profile_view_title', array('username' => $displayName)));
        $this->setPageHeading(OW::getLanguage()->text('base', 'profile_view_heading', array('username' => $displayName)));
        $this->setPageHeadingIconClass('ow_ic_user');

        $this->addComponent("header", OW::getClassInstance("BASE_MCMP_ProfileHeader", $userDto));

        //Profile Info
        $this->addComponent("info", OW::getClassInstance("BASE_MCMP_ProfileInfo", $userDto, true));
        $this->addComponent('contentMenu', OW::getClassInstance("BASE_MCMP_ProfileContentMenu", $userDto));
        $this->addComponent('about', OW::getClassInstance("BASE_MCMP_ProfileAbout", $userDto, 80));

        $this->assign("userId", $userDto->id);

        $vars = BOL_SeoService::getInstance()->getUserMetaInfo($userDto);

        // set meta info
        $params = array(
            "sectionKey" => "base.users",
            "entityKey" => "userPage",
            "title" => "base+meta_title_user_page",
            "description" => "base+meta_desc_user_page",
            "keywords" => "base+meta_keywords_user_page",
            "vars" => $vars,
            "image" => BOL_AvatarService::getInstance()->getAvatarUrl($userDto->getId(), 2)
        );

        OW::getEventManager()->trigger(new OW_Event("base.provide_page_meta_info", $params));
    }

    public function about( $params )
    {
        $userDto = $this->checkProfilePermissions($params);

        if ( $userDto === null )
        {
            return;
        }

        $displayName = BOL_UserService::getInstance()->getDisplayName($userDto->id);

        $this->setPageTitle(OW::getLanguage()->text('base', 'profile_view_title', array('username' => $displayName)));
        $this->setPageHeading(OW::getLanguage()->text('base', 'profile_view_heading', array('username' => $displayName)));
        $this->setPageHeadingIconClass('ow_ic_user');

        $this->addComponent("header", OW::getClassInstance("BASE_MCMP_ProfileHeader", $userDto));

        //Profile Info
        $this->addComponent("info", OW::getClassInstance("BASE_MCMP_ProfileInfo", $userDto));
        $this->addComponent('about', OW::getClassInstance("BASE_MCMP_ProfileAbout", $userDto));

        $this->assign("userId", $userDto->id);
    }

    public function userDeleted()
    {
        
    }

    public function forgotPassword()
    {
        if ( OW::getUser()->isAuthenticated() )
        {
            $this->redirect(OW::getRouter()->getBaseUrl());
        }

        $this->setPageHeading(OW::getLanguage()->text('base', 'forgot_password_heading'));

        $language = OW::getLanguage();

        $form = $this->userService->getResetForm();

        $this->addForm($form);

        if ( OW::getRequest()->isPost() )
        {
            if ( $form->isValid($_POST) )
            {
                $data = $form->getValues();

                try
                {
                    $this->userService->processResetForm($data);
                }
                catch ( LogicException $e )
                {
                    OW::getFeedback()->error($e->getMessage());
                    $this->redirect();
                }

                OW::getFeedback()->info($language->text('base', 'forgot_password_success_message'));
                $this->redirect();
            }
            else
            {
                OW::getFeedback()->error($language->text('base', 'forgot_password_general_error_message'));
                $this->redirect();
            }
        }

        // set meta info
        $params = array(
            "sectionKey" => "base.base_pages",
            "entityKey" => "forgot_pass",
            "title" => "base+meta_title_forgot_pass",
            "description" => "base+meta_desc_forgot_pass",
            "keywords" => "base+meta_keywords_forgot_pass"
        );

        OW::getEventManager()->trigger(new OW_Event("base.provide_page_meta_info", $params));
    }

    public function resetPasswordRequest()
    {
        if ( OW::getUser()->isAuthenticated() )
        {
            $this->redirect(OW::getRouter()->getBaseUrl());
        }

        $form = $this->userService->getResetPasswordRequestFrom();
        $this->addForm($form);
        $this->setPageHeading(OW::getLanguage()->text('base', 'reset_password_request_heading'));

        if ( OW::getRequest()->isPost() )
        {
            if ( $form->isValid($_POST) )
            {
                $data = $form->getValues();

                $resetPassword = $this->userService->findResetPasswordByCode($data['code']);

                if ( $resetPassword === null )
                {
                    OW::getFeedback()->error(OW::getLanguage()->text('base', 'reset_password_request_invalid_code_error_message'));
                    $this->redirect();
                }

                $this->redirect(OW::getRouter()->urlForRoute('base.reset_user_password', array('code' => $resetPassword->getCode())));
            }
            else
            {
                OW::getFeedback()->error(OW::getLanguage()->text('base', 'reset_password_request_invalid_code_error_message'));
                $this->redirect();
            }
        }
    }

    public function resetPassword( $params )
    {
        $language = OW::getLanguage();

        if ( OW::getUser()->isAuthenticated() )
        {
            $this->redirect(OW::getRouter()->getBaseUrl());
        }

        $this->setPageHeading($language->text('base', 'reset_password_heading'));

        if ( empty($params['code']) )
        {
            throw new Redirect404Exception();
        }

        $resetCode = $this->userService->findResetPasswordByCode($params['code']);

        if ( $resetCode == null )
        {
            throw new RedirectException(OW::getRouter()->urlForRoute('base.reset_user_password_expired_code'));
        }

        $user = $this->userService->findUserById($resetCode->getUserId());

        if ( $user === null )
        {
            throw new Redirect404Exception();
        }

        $form = $this->userService->getResetPasswordForm();
        $this->addForm($form);

        $this->assign('formText', $language->text('base', 'reset_password_form_text', array('username' => $user->getUsername())));

        if ( OW::getRequest()->isPost() )
        {
            if ( $form->isValid($_POST) )
            {
                $data = $form->getValues();

                try
                {
                    $this->userService->processResetPasswordForm($data, $user, $resetCode);
                }
                catch ( LogicException $e )
                {
                    OW::getFeedback()->error($e->getMessage());
                    $this->redirect();
                }

                OW::getFeedback()->info(OW::getLanguage()->text('base', 'reset_password_success_message'));
                $this->redirect(OW::getRouter()->urlForRoute('static_sign_in'));
            }
            else
            {
                OW::getFeedback()->error('Invalid Data');
                $this->redirect();
            }
        }
    }

    public function resetPasswordCodeExpired()
    {
        $this->setPageHeading(OW::getLanguage()->text('base', 'reset_password_code_expired_cap_label'));        
        $this->assign('text', OW::getLanguage()->text('base', 'reset_password_code_expired_text', array('url' => OW::getRouter()->urlForRoute('base_forgot_password'))));        
    }
}

