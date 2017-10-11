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
 * Change password class.
 *
 * @author Alex Ermashev <alexermashev@gmail.com>
 * @package ow_system_plugins.base.mobile.components
 * @since 1.8.6
 */
class BASE_MCMP_ChangePassword extends BASE_CMP_ChangePassword
{

    /**
     * Constructor.
     */
    public function __construct()
    {
        $language = OW::getLanguage();

        $this->setTemplate(OW::getPluginManager()->getPlugin('base')->getMobileCmpViewDir() . 'change_password.html');

        // init form
        $form = new Form('changeUserPassword');
        $form->setId('changeUserPassword');

        $oldPassword = new PasswordField('oldPassword');
        $oldPassword->setLabel($language->text('base', 'change_password_old_password'));
        $oldPassword->addValidator(new OldPasswordValidator());
        $oldPassword->setRequired();

        $form->addElement( $oldPassword );

        $newPassword = new PasswordField('password');
        $newPassword->setLabel($language->text('base', 'change_password_new_password'));
        $newPassword->setRequired();
        $newPassword->addValidator( new NewPasswordValidator() );

        $form->addElement( $newPassword );

        $repeatPassword = new PasswordField('repeatPassword');
        $repeatPassword->setLabel($language->text('base', 'change_password_repeat_password'));
        $repeatPassword->setRequired();

        $form->addElement( $repeatPassword );

        $submit = new Submit('change');
        $submit->setLabel($language->text('base', 'change_password_submit'));

        $form->setAjax(true);
        $form->setAjaxResetOnSuccess(false);

        $form->addElement($submit);

        $messageError = $language->text('base', 'change_password_error');
        $messageSuccess = $language->text('base', 'change_password_success');

        $form->bindJsFunction(FORM::BIND_SUCCESS, "function( json )
            {
                if( json.result )
                {
                    OW.info(".json_encode($messageSuccess).");
                }
                else
                {
                    OW.error(".json_encode($messageError).");
                }

            } " );

        $this->addForm($form);

        // validate form
        if ( OW::getRequest()->isAjax() )
        {
            $result = false;

            if ( $form->isValid($_POST) )
            {
                $data = $form->getValues();

                BOL_UserService::getInstance()->updatePassword( OW::getUser()->getId(), $data['password'] );

                $result = true;
                OW::getFeedback()->info($language->text('mobile', 'password_changed'));
            }

            echo json_encode( array( 'result' => $result ) );
            exit;
        }

        $language->addKeyForJs('base', 'join_error_password_not_valid');
        $language->addKeyForJs('base', 'join_error_password_too_short');
        $language->addKeyForJs('base', 'join_error_password_too_long');

        //include js
        $onLoadJs = " window.changePassword = new OW_BaseFieldValidators( " .
            json_encode( array (
                'formName' => $form->getName(),
                'responderUrl' => OW::getRouter()->urlFor("BASE_CTRL_Join", "ajaxResponder"),
                'passwordMaxLength' => UTIL_Validator::PASSWORD_MAX_LENGTH,
                'passwordMinLength' => UTIL_Validator::PASSWORD_MIN_LENGTH ) ) . ",
                                                            " . UTIL_Validator::EMAIL_PATTERN . ", " . UTIL_Validator::USER_NAME_PATTERN . " ); ";

        $onLoadJs .= " window.oldPassword = new OW_ChangePassword( " .
            json_encode( array (
                'formName' => $form->getName(),
                'responderUrl' => OW::getRouter()->urlFor("BASE_CTRL_Edit", "ajaxResponder") ) ) ." ); ";

        OW::getDocument()->addOnloadScript($onLoadJs);

        $jsDir = OW::getPluginManager()->getPlugin("base")->getStaticJsUrl();

        OW::getDocument()->addScript($jsDir . "base_field_validators.js");
        OW::getDocument()->addScript($jsDir . "change_password.js");
    }
}