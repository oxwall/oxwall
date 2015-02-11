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
 * User console component class.
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
//class BASE_CMP_AjaxSignIn extends OW_Component
//{
//    const HOOK_REMOTE_AUTH_BUTTON_LIST = 'base_hook_remote_auth_button_list';
//
//    /**
//     * Constructor.
//     */
//    public function __construct( $formName, $ajax = false )
//    {
//        parent::__construct();
//
//        $form = new Form($formName);
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
//        $submit = new Submit('submit');
//        $submit->setValue(OW::getLanguage()->text('base', 'sign_in_submit_label'));
//        $form->addElement($submit);
//
//
//
//
//        $form = BASE_CTRL_User::getSignInForm();
//        $form->setAjax();
//        $form->setAction(OW::getRouter()->urlFor('BASE_CTRL_User', 'ajaxSignIn'));
//        $form->bindJsFunction(Form::BIND_SUCCESS, 'function(data){if( data.result ){OW.info(data.message);setTimeout(function(){window.location.reload();}, 1000);}else{OW.error(data.message);}}');
//        $this->addForm($form);
//        $this->assign('forgot_url', OW::getRouter()->urlForRoute('base_forgot_password'));
//        $this->assign('buttonList', implode('', $this->getRemoteAuthButtonList()));
//    }
//
//    private function getRemoteAuthButtonList()
//    {
//        $items = OW::getRegistry()->getArray(self::HOOK_REMOTE_AUTH_BUTTON_LIST);
//
//        if ( empty($items) )
//        {
//            return array();
//        }
//
//        $tplItems = array();
//        foreach ( $items as $item )
//        {
//            if ( is_callable($item) )
//            {
//                $tplItems[] = call_user_func($item);
//            }
//        }
//
//        return array_filter($tplItems);
//    }
//}