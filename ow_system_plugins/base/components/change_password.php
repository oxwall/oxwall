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
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow_system_plugins.base.components
 * @since 1.0
 */

class BASE_CMP_ChangePassword extends OW_Component
{
    public function __construct()
    {
        parent::__construct();

        $language = OW::getLanguage();

        $form = new Form("change-user-password");
        $form->setId("change-user-password");

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

        $submit = new Submit("change");
        $submit->setLabel($language->text('base', 'change_password_submit'));

        $form->setAjax(true);
        $form->setAjaxResetOnSuccess(false);

        $form->addElement($submit);

        if ( OW::getRequest()->isAjax() )
        {
            $result = false;
            
            if ( $form->isValid($_POST) )
            {
                $data = $form->getValues();
                
                BOL_UserService::getInstance()->updatePassword( OW::getUser()->getId(), $data['password'] );

                $result = true;
            }
            
            echo json_encode( array( 'result' => $result ) );
            exit;
        }
        else
        {
            $messageError = $language->text('base', 'change_password_error');
            $messageSuccess = $language->text('base', 'change_password_success');

            $form->bindJsFunction(FORM::BIND_SUCCESS, "function( json )
            {
            	if( json.result )
            	{
            	    var floatbox = OW.getActiveFloatBox();

                    if ( floatbox )
                    {
                        floatbox.close();
                    }

            	    OW.info(".json_encode($messageSuccess).");
                }
                else
                {
                    OW.error(".json_encode($messageError).");
                }

            } " );

            $this->addForm($form);

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
}

class NewPasswordValidator extends BASE_CLASS_PasswordValidator
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @see Validator::getJsValidator()
     *
     * @return string
     */
    public function getJsValidator()
    {
        return "{
                validate : function( value )
                {
                    if( !window.changePassword.validatePassword() )
                    {
                        throw window.changePassword.errors['password']['error'];
                    }
                },
                getErrorMessage : function()
                {
                       if( window.changePassword.errors['password']['error'] !== undefined ){ return window.changePassword.errors['password']['error'] }
                       else{ return ".json_encode($this->getError())." }
                }
        }";
    }
}

class OldPasswordValidator extends OW_Validator
{
    public function __construct()
    {
        $language = OW::getLanguage();
        $this->setErrorMessage($language->text('base', 'join_error_password_not_valid'));
    }

    public function isValid( $value )
    {
        $result = BOL_UserService::getInstance()->isValidPassword( OW::getUser()->getId(), $value );
        
        return $result;
    }

    /**
     * @see Validator::getJsValidator()
     *
     * @return string
     */
    public function getJsValidator()
    {
        return "{
                validate : function( value )
                {
                    if( !window.oldPassword.validatePassword() )
                    {
                        throw window.oldPassword.errors['password']['error'];
                    }
                },
                getErrorMessage : function()
                {
                       if( window.oldPassword.errors['password']['error'] !== undefined ){ return window.oldPassword.errors['password']['error'] }
                       else{ return ".json_encode($this->getError())." }
                }
        }";
    }
}