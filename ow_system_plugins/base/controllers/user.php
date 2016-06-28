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
class BASE_CTRL_User extends OW_ActionController
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

    public function forgotPassword()
    {
        if ( OW::getUser()->isAuthenticated() )
        {
            $this->redirect(OW_URL_HOME);
        }

        $this->setPageHeading(OW::getLanguage()->text('base', 'forgot_password_heading'));

        $language = OW::getLanguage();

        $form = $this->userService->getResetForm();

        $this->addForm($form);

        OW::getDocument()->getMasterPage()->setTemplate(OW::getThemeManager()->getMasterPageTemplate(OW_MasterPage::TEMPLATE_BLANK));

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
            "entityKey" => "forgotPass",
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
            $this->redirect(OW::getRouter()->urlForRoute('base_member_dashboard'));
        }

        $form = $this->userService->getResetPasswordRequestFrom();
        $this->addForm($form);

        $this->setPageHeading(OW::getLanguage()->text('base', 'reset_password_request_heading'));

        OW::getDocument()->getMasterPage()->setTemplate(OW::getThemeManager()->getMasterPageTemplate(OW_MasterPage::TEMPLATE_BLANK));

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
            $this->redirect(OW::getRouter()->urlForRoute('base_member_dashboard'));
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

        OW::getDocument()->getMasterPage()->setTemplate(OW::getThemeManager()->getMasterPageTemplate(OW_MasterPage::TEMPLATE_BLANK));

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
        $this->setPageHeadingIconClass('ow_ic_info');
        $this->assign('text', OW::getLanguage()->text('base', 'reset_password_code_expired_text', array('url' => OW::getRouter()->urlForRoute('base_forgot_password'))));
        OW::getDocument()->getMasterPage()->setTemplate(OW::getThemeManager()->getMasterPageTemplate(OW_MasterPage::TEMPLATE_BLANK));
    }

    public function standardSignIn()
    {
        if ( OW::getRequest()->isAjax() )
        {
            exit(json_encode(array()));
        }

        if ( OW::getUser()->isAuthenticated() )
        {
            throw new RedirectException(OW::getRouter()->getBaseUrl());
        }

        $this->assign('joinUrl', OW::getRouter()->urlForRoute('base_join'));

        OW::getDocument()->getMasterPage()->setTemplate(OW::getThemeManager()->getMasterPageTemplate(OW_MasterPage::TEMPLATE_BLANK));

        $this->addComponent('sign_in_form', new BASE_CMP_SignIn());

        if ( OW::getRequest()->isPost() )
        {
            try
            {
                $result = $this->processSignIn();
            }
            catch ( LogicException $e )
            {
                OW::getFeedback()->error('Invalid data submitted!');
                $this->redirect();
            }

            $message = implode('', $result->getMessages());

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

        $this->setDocumentKey('base_sign_in');

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

    public function ajaxSignIn()
    {
        if ( !OW::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }

        if ( OW::getRequest()->isPost() )
        {
            try
            {
                $result = $this->processSignIn();
            }
            catch ( LogicException $e )
            {
                exit(json_encode(array('result' => false, 'message' => 'Error!')));
            }

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

            exit(json_encode(array()));
        }

        exit(json_encode(array()));
    }

    public function signOut()
    {

        OW::getUser()->logout();

        if ( isset($_COOKIE['ow_login']) )
        {
            setcookie('ow_login', '', time() - 3600, '/');
        }
        OW::getSession()->set('no_autologin', true);
        $this->redirect(OW::getRouter()->getBaseUrl());
    }
//    public static function getSignInForm( $submitDecorator = 'button' )
//    {
//        $form = new Form('sign-in');
//
//        $form->setAjaxResetOnSuccess(false);
//
//        $username = new TextField('identity');
//        $username->setRequired(true);
//        $username->setHasInvitation(true);
//        $username->setInvitation(OW::getLanguage()->text('base', 'component_sign_in_login_invitation'));
//        $form->addElement($username);
//
//        $password = new PasswordField('password');
//        $password->setHasInvitation(true);
//        $password->setInvitation('password');
//        $password->setRequired(true);
//
//        $form->addElement($password);
//
//        $remeberMe = new CheckboxField('remember');
//        $remeberMe->setLabel(OW::getLanguage()->text('base', 'sign_in_remember_me_label'));
//        $form->addElement($remeberMe);
//
//        $submit = new Submit('submit', $submitDecorator);
//        $submit->setValue(OW::getLanguage()->text('base', 'sign_in_submit_label'));
//        $form->addElement($submit);
//
//        return $form;
//    }

    /**
     * @return OW_AuthResult
     */
    private function processSignIn()
    {
        $form = $this->userService->getSignInForm();

        if ( !$form->isValid($_POST) )
        {
            throw new LogicException();
        }

        $data = $form->getValues();
        return $this->userService->processSignIn($data['identity'], $data['password'], isset($data['remember']));
    }

    public function controlFeatured( $params )
    {
        $service = BOL_UserService::getInstance();

        if ( (!OW::getUser()->isAuthenticated() || !OW::getUser()->isAuthorized('base') ) || ($userId = intval($params['id'])) <= 0 )
        {
            exit;
        }

        switch ( $params['command'] )
        {
            case 'mark':

                $service->markAsFeatured($userId);
                OW::getFeedback()->info(OW::getLanguage()->text('base', 'user_feedback_marked_as_featured'));

                break;

            case 'unmark':

                $service->cancelFeatured($userId);
                OW::getFeedback()->info(OW::getLanguage()->text('base', 'user_feedback_unmarked_as_featured'));

                break;
        }

        $this->redirect($_GET['backUrl']);
    }

    public function updateActivity( $params )
    {
        // activity already updated
        exit;
    }

    public function deleteUser( $params )
    {
        if ( !OW::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }

        $userId = (int) $params['user-id'];

        $user = BOL_UserService::getInstance()->findUserById($userId);

        if ( $user === null || !OW::getUser()->isAuthorized('base') )
        {
            exit(json_encode(array(
                'result' => 'error'
            )));
        }

        if ( BOL_AuthorizationService::getInstance()->isActionAuthorizedForUser($userId, BOL_AuthorizationService::ADMIN_GROUP_NAME) )
        {
            exit(json_encode(array(
                'message' => OW::getLanguage()->text('base', 'cannot_delete_admin_user_message'),
                'result' => 'error'
            )));
        }

//        $event = new OW_Event(OW_EventManager::ON_USER_UNREGISTER, array('userId' => $userId, 'deleteContent' => true));
//        OW::getEventManager()->trigger($event);

        BOL_UserService::getInstance()->deleteUser($userId);

        $successMessage = OW::getLanguage()->text('base', 'user_deleted_page_message');

        if ( !empty($_GET['showMessage']) )
        {
            OW::getFeedback()->info($successMessage);
        }

        exit(json_encode(array(
            'message' => $successMessage,
            'result' => 'success'
        )));
    }

    public function userDeleted()
    {//TODO do smth
        //OW::getDocument()->getMasterPage()->setTemplate(OW::getThemeManager()->getMasterPageTemplate(OW_MasterPage::TEMPLATE_BLANK));
    }

    public function approve( $params )
    {
        if ( !OW::getUser()->isAuthorized('base') )
        {
            throw new Redirect404Exception();
        }

        $userId = $params['userId'];

        $userService = BOL_UserService::getInstance();

        if ( $user = $userService->findUserById($userId) )
        {
            if ( !$userService->isApproved($userId) )
            {
                $userService->approve($userId);
                $userService->sendApprovalNotification($userId);

                OW::getFeedback()->info(OW::getLanguage()->text('base', 'user_approved'));
            }
        }

        if ( empty($_SERVER['HTTP_REFERER']) )
        {
            $username = $userService->getUserName($userId);
            $this->redirect(OW::getRouter()->urlForRoute('base_user_profile', array('username' => $username)));
        }
        else
        {
            $this->redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function updateUserRoles()
    {
        if ( !OW::getUser()->isAuthorized('base') )
        {
            exit(json_encode(array(
                'result' => 'error',
                'message' => 'Not Authorized'
            )));
        }

        $user = BOL_UserService::getInstance()->findUserById((int) $_POST['userId']);

        if ( $user === null )
        {
            exit(json_encode(array('result' => 'error', 'mesaage' => 'Empty user')));
        }

        $roles = array();
        foreach ( $_POST['roles'] as $roleId => $onoff )
        {
            if ( !empty($onoff) )
            {
                $roles[] = $roleId;
            }
        }

        $aService = BOL_AuthorizationService::getInstance();
        $aService->deleteUserRolesByUserId($user->getId());

        foreach ( $roles as $roleId )
        {
            $aService->saveUserRole($user->getId(), $roleId);
        }

        exit(json_encode(array(
            'result' => 'success',
            'message' => OW::getLanguage()->text('base', 'authorization_feedback_roles_updated')
        )));
    }

    public function block( $params )
    {
        if ( empty($params['id']) )
        {
            exit;
        }
        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }
        $userId = (int) $params['id'];

        $userService = BOL_UserService::getInstance();
        $userService->block($userId);

        OW::getFeedback()->info(OW::getLanguage()->text('base', 'user_feedback_profile_blocked'));

        $this->redirect($_GET['backUrl']);
    }

    public function unblock( $params )
    {
        if ( empty($params['id']) )
        {
            exit;
        }
        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }
        $id = (int) $params['id'];

        $userService = BOL_UserService::getInstance();
        $userService->unblock($id);

        OW::getFeedback()->info(OW::getLanguage()->text('base', 'user_feedback_profile_unblocked'));

        $this->redirect($_GET['backUrl']);
    }
}
