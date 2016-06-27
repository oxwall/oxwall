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
$owBasePlugin = OW::getPluginManager()->getPlugin('base');
$themeManager = OW::getThemeManager();
$baseDecoratorsToRegister = array('form_base', 'main_menu', 'box_toolbar', 'avatar_item', 'box_cap', 'box', 'ipc',
    'mini_ipc', 'tooltip', 'paging', 'floatbox', 'button', 'user_list_item', 'button_list_item', 'ic');

foreach ( $baseDecoratorsToRegister as $name )
{
    $themeManager->addDecorator($name, $owBasePlugin->getKey());
}

$classesToAutoload = array(
    'BASE_Members' => $owBasePlugin->getCtrlDir() . 'user_list.php',
    'BASE_MenuItem' => $owBasePlugin->getCmpDir() . 'menu.php',
    'BASE_CommentsParams' => $owBasePlugin->getCmpDir() . 'comments.php',
    'BASE_ContextAction' => $owBasePlugin->getCmpDir() . 'context_action.php',
    'JoinForm' => $owBasePlugin->getCtrlDir() . 'join.php'
);

OW::getAutoloader()->addClassArray($classesToAutoload);

$router = OW::getRouter();

$router->addRoute(new OW_Route('static_sign_in', 'sign-in', 'BASE_CTRL_User', 'standardSignIn'));
$router->addRoute(new OW_Route('base_forgot_password', 'forgot-password', 'BASE_CTRL_User', 'forgotPassword'));
$router->addRoute(new OW_Route('base_sign_out', 'sign-out', 'BASE_CTRL_User', 'signOut'));
$router->addRoute(new OW_Route('ajax-form', 'ajax-form', 'BASE_CTRL_AjaxForm', 'index'));

$router->addRoute(new OW_Route('users', 'users', 'BASE_CTRL_UserList', 'index', array('list' => array(OW_Route::PARAM_OPTION_HIDDEN_VAR => 'latest'))));
$router->addRoute(new OW_Route('base_user_lists', 'users/:list', 'BASE_CTRL_UserList', 'index'));

$router->addRoute(new OW_Route('users-waiting-for-approval', 'users/waiting-for-approval', 'BASE_CTRL_UserList', 'forApproval'));
$router->addRoute(new OW_Route('users-blocked', 'users/blocked', 'BASE_CTRL_UserList', 'blocked'));


$router->addRoute(new OW_Route('users-search', 'users/search', 'BASE_CTRL_UserSearch', 'index'));
$router->addRoute(new OW_Route('users-search-result', 'users/search-result', 'BASE_CTRL_UserSearch', 'result'));

$router->addRoute(new OW_Route('base_join', 'join', 'BASE_CTRL_Join', 'index'));
$router->addRoute(new OW_Route('base_edit', 'profile/edit', 'BASE_CTRL_Edit', 'index'));
$router->addRoute(new OW_Route('base_edit_user_datails', 'profile/:userId/edit/', 'BASE_CTRL_Edit', 'index'));

$router->addRoute(new OW_Route('base_email_verify', 'email-verify', 'BASE_CTRL_EmailVerify', 'index'));
$router->addRoute(new OW_Route('base_email_verify_code_form', 'email-verify-form', 'BASE_CTRL_EmailVerify', 'verifyForm'));
$router->addRoute(new OW_Route('base_email_verify_code_check', 'email-verify-check/:code', 'BASE_CTRL_EmailVerify', 'verify'));

$router->addRoute(new OW_Route('base_massmailing_unsubscribe', 'unsubscribe/:id/:code', 'BASE_CTRL_Unsubscribe', 'index'));
$router->addRoute(new OW_Route('base.mobile_version', 'mobile-version', 'BASE_CTRL_BaseDocument', 'redirectToMobile'));

// Drag And Drop panels
$router->addRoute(new OW_Route('base_member_dashboard', 'dashboard', 'BASE_CTRL_ComponentPanel', 'dashboard'));
$router->addRoute(new OW_Route('base_member_dashboard_customize', 'dashboard/customize', 'BASE_CTRL_ComponentPanel', 'dashboard', array(
        'mode' => array(OW_Route::PARAM_OPTION_HIDDEN_VAR => 'customize'
        ))));

$router->addRoute(new OW_Route('base_member_profile_customize', 'my-profile/customize', 'BASE_CTRL_ComponentPanel', 'myProfile', array(
        'mode' => array(OW_Route::PARAM_OPTION_HIDDEN_VAR => 'customize'
        ))));

$router->addRoute(new OW_Route('base_index_customize', 'index/customize', 'BASE_CTRL_ComponentPanel', 'index', array(
        'mode' => array(OW_Route::PARAM_OPTION_HIDDEN_VAR => 'customize'
        ))));

$router->addRoute(new OW_Route('base_index', 'index', 'BASE_CTRL_ComponentPanel', 'index'));
$router->addRoute(new OW_Route('base_member_profile', 'my-profile', 'BASE_CTRL_ComponentPanel', 'myProfile'));

$router->addRoute(new OW_Route('base_user_profile', 'user/:username', 'BASE_CTRL_ComponentPanel', 'profile'));
$router->addRoute(new OW_Route('base_page_404', '404', 'BASE_CTRL_BaseDocument', 'page404'));
$router->addRoute(new OW_Route('base_page_403', '403', 'BASE_CTRL_BaseDocument', 'page403'));
$router->addRoute(new OW_Route('base_page_auth_failed', 'authorization-failed', 'BASE_CTRL_BaseDocument', 'authorizationFailed'));
$router->addRoute(new OW_Route('base_page_splash_screen', 'splash-screen', 'BASE_CTRL_BaseDocument', 'splashScreen'));
$router->addRoute(new OW_Route('base_page_alert', 'alert-page', 'BASE_CTRL_BaseDocument', 'alertPage'));
$router->addRoute(new OW_Route('base_page_confirm', 'confirm-page', 'BASE_CTRL_BaseDocument', 'confirmPage'));
$router->addRoute(new OW_Route('base_page_install_completed', 'install/completed', 'BASE_CTRL_BaseDocument', 'installCompleted'));

$router->addRoute(new OW_Route('base_delete_user', 'profile/delete', 'BASE_CTRL_DeleteUser', 'index'));
$router->addRoute(new OW_Route('base.reset_user_password', 'reset-password/:code', 'BASE_CTRL_User', 'resetPassword'));
$router->addRoute(new OW_Route('base.reset_user_password_request', 'reset-password-request', 'BASE_CTRL_User', 'resetPasswordRequest'));
$router->addRoute(new OW_Route('base.reset_user_password_expired_code', 'reset-password-code-expired', 'BASE_CTRL_User', 'resetPasswordCodeExpired'));

$router->addRoute(new OW_Route('base_billing_completed', 'order/:hash/completed', 'BASE_CTRL_Billing', 'completed'));
$router->addRoute(new OW_Route('base_billing_completed_st', 'order/completed', 'BASE_CTRL_Billing', 'completed'));
$router->addRoute(new OW_Route('base_billing_canceled', 'order/:hash/canceled', 'BASE_CTRL_Billing', 'canceled'));
$router->addRoute(new OW_Route('base_billing_canceled_st', 'order/canceled', 'BASE_CTRL_Billing', 'canceled'));
$router->addRoute(new OW_Route('base_billing_error', 'order/incomplete', 'BASE_CTRL_Billing', 'error'));

$router->addRoute(new OW_Route('base_preference_index', 'profile/preference', 'BASE_CTRL_Preference', 'index'));

$router->addRoute(new OW_Route('base_user_privacy_no_permission', 'profile/:username/no-permission', 'BASE_CTRL_ComponentPanel', 'privacyMyProfileNoPermission'));

$router->addRoute(new OW_Route('base-api-server', 'api-server', 'BASE_CTRL_ApiServer', 'request'));
$router->addRoute(new OW_Route('base.robots_txt', 'robots.txt', 'BASE_CTRL_Base', 'robotsTxt'));
$router->addRoute(new OW_Route('base.sitemap', 'sitemap.xml', 'BASE_CTRL_Base', 'sitemap'));

$router->addRoute(new OW_Route('base.complete_account_type', 'fill/account_type', 'BASE_CTRL_CompleteProfile', 'fillAccountType'));
$router->addRoute(new OW_Route('base.complete_required_questions', 'fill/profile_questions', 'BASE_CTRL_CompleteProfile', 'fillRequiredQuestions'));

$router->addRoute(new OW_Route('base.moderation_flags', 'moderation/flags/:group', 'BASE_CTRL_Moderation', 'flags'));
$router->addRoute(new OW_Route('base.moderation_flags_index', 'moderation/flags', 'BASE_CTRL_Moderation', 'flags'));
$router->addRoute(new OW_Route('base.moderation_tools', 'moderation', 'BASE_CTRL_Moderation', 'index'));


OW_ViewRenderer::getInstance()->registerFunction('display_rate', array('BASE_CTRL_Rate', 'displayRate'));

$eventHandler = new BASE_CLASS_EventHandler();
$eventHandler->init();

//OW::getRegistry()->setArray('users_page_data', array());
BASE_CLASS_ConsoleEventHandler::getInstance()->init();
BASE_CLASS_InvitationEventHandler::getInstance()->init();