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
 * Email Verify controller
 *
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow_system_plugins.base.controller
 * @since 1.0
 */
class BASE_CTRL_EmailVerify extends OW_ActionController
{
    protected $questionService;
    protected $emailVerifyService;

    public function __construct()
    {
        parent::__construct();

        $this->questionService = BOL_QuestionService::getInstance();
        $this->emailVerifyService = BOL_EmailVerifyService::getInstance();

        $this->userService = BOL_UserService::getInstance();
    }

    protected function setMasterPage()
    {
         OW::getDocument()->getMasterPage()->setTemplate(OW::getThemeManager()->getMasterPageTemplate(OW_MasterPage::TEMPLATE_BLANK));
    }

    public function index( $params )
    {
        if( OW::getRequest()->isAjax() )
        {
            echo "{message:'user is not verified'}";
            exit;
        }

        $this->setMasterPage();

        $userId = OW::getUser()->getId();

        if ( !OW::getUser()->isAuthenticated() || $userId === null )
        {
            throw new AuthenticateException();
        }

        $user = BOL_UserService::getInstance()->findUserById($userId);

        if ( (int) $user->emailVerify === 1 )
        {
            $this->redirect(OW::getRouter()->uriForRoute('base_member_dashboard'));
        }

        $language = OW::getLanguage();

        $this->setPageHeading($language->text('base', 'email_verify_index'));

        $emailVerifyForm = new Form('emailVerifyForm');

        $email = new TextField('email');
        $email->setLabel($language->text('base', 'questions_question_email_label'));
        //$email->setRequired();
        $email->addValidator(new BASE_CLASS_EmailVerifyValidator());
        $email->setValue($user->email);

        $emailVerifyForm->addElement($email);

        $submit = new Submit('sendVerifyMail');
        $submit->setValue($language->text('base', 'email_verify_send_verify_mail_button_label'));

        $emailVerifyForm->addElement($submit);
        $this->addForm($emailVerifyForm);

        if ( OW::getRequest()->isPost() )
        {
            if ( $emailVerifyForm->isValid($_POST) )
            {
                $data = $emailVerifyForm->getValues();

                $email = htmlspecialchars(trim($data['email']));

                if ( $user->email != $email )
                {
                    BOL_UserService::getInstance()->updateEmail($user->id, $email);
                    $user->email = $email;
                }

                $this->emailVerifyService->sendUserVerificationMail($user);

                $this->redirect();
            }
        }
    }

    public function verify( $params )
    {
        $language = OW::getLanguage();

        $this->setPageHeading($language->text('base', 'email_verify_index'));

        $code = null;
        if ( isset($params['code']) )
        {
            $code = $params['code'];
            $this->emailVerifyService->verifyEmail($code);
        }
    }

    public function verifyForm( $params )
    {
        $this->setMasterPage();
        $language = OW::getLanguage();

        $this->setPageHeading($language->text('base', 'email_verify_index'));

        $form = new Form('verificationForm');

        $verificationCode = new TextField('verificationCode');
        $verificationCode->setLabel($language->text('base', 'email_verify_verification_code_label'));
        $verificationCode->addValidator(new BASE_CLASS_VerificationCodeValidator());

        $form->addElement($verificationCode);

        $submit = new Submit('submit');
        $submit->setValue($language->text('base', 'email_verify_verification_code_submit_button_label'));
        $form->addElement($submit);
        $this->addForm($form);

        if ( OW::getRequest()->isPost() )
        {
            if ( $form->isValid($_POST) )
            {
                $data = $form->getValues();

                $code = $data['verificationCode'];

                $this->emailVerifyService->verifyEmail($code);
            }
        }
    }
}