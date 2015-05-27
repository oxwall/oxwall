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
 * @package ow_core
 * @since 1.0
 */
class BASE_MCLASS_EventHandler extends BASE_CLASS_EventHandler
{

    public function init()
    {
        $this->genericInit();
        $eventManager = OW::getEventManager();
        $eventManager->bind(OW_EventManager::ON_BEFORE_DOCUMENT_RENDER, array($this, 'onDocRenderAddJsDeclarations'));
        $eventManager->bind(BASE_MCMP_ProfileContentMenu::EVENT_NAME, array($this, 'onMobileProfileContentMenu'));
        //$eventManager->bind(BASE_MCMP_ProfileContentMenu::EVENT_NAME, array($this, 'onFakeMobileProfileContentMenu'));

        $eventManager->bind(BASE_MCMP_ProfileActionToolbar::EVENT_NAME, array($this, 'onActionToolbarAddDeleteActionTool'));
        $eventManager->bind(BASE_MCMP_ProfileActionToolbar::EVENT_NAME, array($this, 'onActionToolbarAddSuspendActionTool'));
        $eventManager->bind(BASE_MCMP_ProfileActionToolbar::EVENT_NAME, array($this, 'onActionToolbarAddUserApproveActionTool'));
        $eventManager->bind(BASE_MCMP_ProfileActionToolbar::EVENT_NAME, array($this, 'onActionToolbarAddUserFeatureActionTool'));
        $eventManager->bind(BASE_MCMP_ProfileActionToolbar::EVENT_NAME, array($this, 'onActionToolbarAddUserBlockActionTool'));
        $eventManager->bind(OW_EventManager::ON_PLUGINS_INIT, array($this, 'onPluginsInitCheckUserStatus'));
        $eventManager->bind('mobile.notifications.on_item_render', array($this, 'onNotificationRender'));
        $eventManager->bind(BASE_MCMP_ConnectButtonList::HOOK_REMOTE_AUTH_BUTTON_LIST, array($this, "onCollectButtonList"));
        $eventManager->bind('class.get_instance', array($this, "onGetClassInstance"));
    }
    
    public function onGetClassInstance( OW_Event $event )
    {
        $params = $event->getParams();
        
        if ( !empty($params['className']) && $params['className'] == 'BASE_CLASS_JoinUploadPhotoField' )
        {
            $rClass = new ReflectionClass('FileField');
            
            $arguments = array();
            
            if ( !empty($params['arguments']) )
            {
                $arguments = $params['arguments'];
            }
            
            $event->setData($rClass->newInstanceArgs($arguments));
        }
        
        if ( !empty($params['className']) && $params['className'] == 'BASE_CLASS_AvatarFieldValidator' )
        {
            $rClass = new ReflectionClass('BASE_MCLASS_JoinAvatarFieldValidator');
            
            $arguments = array();
            
            if ( !empty($params['arguments']) )
            {
                $arguments = $params['arguments'];
            }
            
            $event->setData($rClass->newInstanceArgs($arguments));
        }
    }
    
    public function onCollectButtonList( BASE_CLASS_EventCollector $e )
    {
        $button = new BASE_MCMP_JoinButton();
        $e->add(array('iconClass' => 'ow_ico_signin_f', 'markup' => $button->render()));
    }

    public function onBeforeDecoratorRender( BASE_CLASS_PropertyEvent $e )
    {
        switch ( $e->getProperty('decoratorName') )
        {
            case 'avatar_item':
                if ( $e->getProperty('fullLabel') === null )
                {
                    $e->setProperty('label', mb_substr($e->getProperty('label'), 0, 1));
                }
                break;
        }
    }

    public function onAddMaintenanceModeExceptions( BASE_CLASS_EventCollector $event )
    {
        $event->add(array('controller' => 'BASE_MCTRL_User', 'action' => 'standardSignIn'));
        $event->add(array('controller' => 'BASE_MCTRL_User', 'action' => 'forgotPassword'));
        $event->add(array('controller' => 'BASE_MCTRL_User', 'action' => 'resetPasswordRequest'));
        $event->add(array('controller' => 'BASE_MCTRL_User', 'action' => 'resetPassword'));
        $event->add(array('controller' => 'BASE_MCTRL_User', 'action' => 'resetPasswordCodeExpired'));
    }

    public function onAddPasswordProtectedExceptions( BASE_CLASS_EventCollector $event )
    {
        $event->add(array('controller' => 'BASE_MCTRL_User', 'action' => 'standardSignIn'));
        $event->add(array('controller' => 'BASE_MCTRL_User', 'action' => 'forgotPassword'));
        $event->add(array('controller' => 'BASE_MCTRL_User', 'action' => 'resetPasswordRequest'));
        $event->add(array('controller' => 'BASE_MCTRL_User', 'action' => 'resetPassword'));
        $event->add(array('controller' => 'BASE_MCTRL_User', 'action' => 'resetPasswordCodeExpired'));
        $event->add(array('controller' => 'BASE_MCTRL_BaseDocument', 'action' => 'redirectToDesktop'));
    }

    public function onAddMembersOnlyException( BASE_CLASS_EventCollector $event )
    {
        $event->add(array('controller' => 'BASE_MCTRL_User', 'action' => 'standardSignIn'));
        $event->add(array('controller' => 'BASE_MCTRL_User', 'action' => 'forgotPassword'));
        $event->add(array('controller' => 'BASE_MCTRL_User', 'action' => 'resetPasswordRequest'));
        $event->add(array('controller' => 'BASE_MCTRL_User', 'action' => 'resetPassword'));
        $event->add(array('controller' => 'BASE_MCTRL_User', 'action' => 'resetPasswordCodeExpired'));
        $event->add(array('controller' => 'BASE_MCTRL_BaseDocument', 'action' => 'redirectToDesktop'));
        $event->add(array('controller' => 'BASE_MCTRL_Join', 'action' => 'index'));
        $event->add(array('controller' => 'BASE_MCTRL_Join', 'action' => 'joinFormSubmit'));
        $event->add(array('controller' => 'BASE_MCTRL_Join', 'action' => 'ajaxResponder'));

    }

    public function onMobileProfileContentMenu( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();

        if ( empty($params['userId']) )
        {
            return;
        }

        $userId = (int) $params['userId'];

        $lang = OW::getLanguage();
        $userName = BOL_UserService::getInstance()->getUserName($userId);
        $url = OW::getRouter()->urlForRoute('base_about_profile', array('username' => $userName));
        $resultArray = array(
            BASE_MCMP_ProfileContentMenu::DATA_KEY_LABEL => $lang->text('mobile', 'about'),
            BASE_MCMP_ProfileContentMenu::DATA_KEY_LINK_HREF => $url,
            BASE_MCMP_ProfileContentMenu::DATA_KEY_LINK_CLASS => 'owm_profile_nav_about'
        );

        $event->add($resultArray);
    }

    public function onDocRenderAddJsDeclarations( $e )
    {
        // Langs
        OW::getLanguage()->addKeyForJs('base', 'flag_as');
        
        $scriptGen = UTIL_JsGenerator::newInstance()->setVariable(
                array('OWM', 'ajaxComponentLoaderRsp'), OW::getRouter()->urlFor('BASE_MCTRL_AjaxLoader', 'component')
        );

        //UsersApi
        $scriptGen->newObject(array('OW', 'Users'), 'OWM_UsersApi', array(array(
                "rsp" => OW::getRouter()->urlFor('BASE_CTRL_AjaxUsersApi', 'rsp')
            )));

        // Right console initialization
        if ( OW::getUser()->isAuthenticated() )
        {
            OW::getLanguage()->addKeyForJs('base', 'mobile_disabled_item_message');
            $params = array(
                'pages' => MBOL_ConsoleService::getInstance()->getPages(),
                'rspUrl' => OW::getRouter()->urlFor('BASE_MCTRL_Ping', 'index'),
                'lastFetchTime' => time(),
                'pingInterval' => 10,
                'desktopUrl' => OW::getRouter()->urlForRoute('base.desktop_version')
            );

            $scriptGen->addScript('
            var mconsole = new OWM_Console(' . json_encode($params) . ');
            mconsole.init();
        ');
        }

        OW::getDocument()->addScriptDeclaration($scriptGen->generateJs());
    }

    public function onUserToolbar( BASE_CLASS_EventCollector $e )
    {
        //TODO

        $e->add(array(
            "label" => "Block",
            "order" => 4,
            "group" => "addition",
            "class" => "owm_red_btn"
        ));

        $e->add(array(
            "label" => "Send Message",
            "order" => 1
        ));

        $e->add(array(
            "label" => "Follow",
            "order" => 2
        ));

        $e->add(array(
            "label" => "Mark as Featured",
            "order" => 3,
            "group" => "addition"
        ));



        $e->add(array(
            "label" => "Delete",
            "order" => 5,
            "group" => "addition",
            "class" => "owm_red_btn"
        ));

        $e->add(array(
            "label" => "Suspend",
            "order" => 6,
            "group" => "addition",
            "class" => "owm_red_btn"
        ));
    }

    public function onActionToolbarAddUserBlockActionTool( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();

        if ( !OW::getUser()->isAuthenticated() )
        {
            return;
        }

        if ( empty($params['userId']) )
        {
            return;
        }

        if ( $params['userId'] == OW::getUser()->getId() )
        {
            return;
        }

        $authorizationService = BOL_AuthorizationService::getInstance();

        if ( $authorizationService->isActionAuthorizedForUser($params['userId'], 'base') || $authorizationService->isSuperModerator($params['userId']) )
        {
            return;
        }

        $userId = (int) $params['userId'];

        $resultArray = array();

        $uniqId = uniqid("block-");
        $isBlocked = BOL_UserService::getInstance()->isBlocked($userId, OW::getUser()->getId());

        $resultArray["label"] = $isBlocked ? OW::getLanguage()->text('base', 'user_unblock_btn_lbl') : OW::getLanguage()->text('base', 'user_block_btn_lbl');

        $toggleText = !$isBlocked ? OW::getLanguage()->text('base', 'user_unblock_btn_lbl') : OW::getLanguage()->text('base', 'user_block_btn_lbl');

        $toggleClass = !$isBlocked ? 'owm_context_action_list_item' : 'owm_context_action_list_item owm_red_btn';

        $resultArray["attributes"] = array();
        $resultArray["attributes"]["data-command"] = $isBlocked ? "unblock" : "block";

        $toggleCommand = !$isBlocked ? "unblock" : "block";

        $resultArray["href"] = 'javascript://';
        $resultArray["id"] = $uniqId;

        $js = UTIL_JsGenerator::newInstance();
        $js->jQueryEvent("#" . $uniqId, "click",
            'var toggle = false; if ( $(this).attr("data-command") == "block" && confirm(e.data.msg) ) { OWM.Users.blockUser(e.data.userId); toggle = true; };
            if ( $(this).attr("data-command") != "block") { OWM.Users.unBlockUser(e.data.userId); toggle =true;}
            toggle && OWM.Utils.toggleText($("span:eq(0)", this), e.data.toggleText);
            toggle && OWM.Utils.toggleAttr(this, "class", e.data.toggleClass);
            toggle && OWM.Utils.toggleAttr(this, "data-command", e.data.toggleCommand);',
            array("e"), array(
            "userId" => $userId,
            "toggleText" => $toggleText,
            "toggleCommand" => $toggleCommand,
            "toggleClass" => $toggleClass,
            "msg" => strip_tags(OW::getLanguage()->text("base", "user_block_confirm_message"))
        ));

        OW::getDocument()->addOnloadScript($js);

        $resultArray["key"] = "base.block_user";
        $resultArray["group"] = "addition";

        $resultArray["class"] = $isBlocked ? '' : 'owm_red_btn';
        $resultArray["order"] = 3;

        $event->add($resultArray);
    }

    public function onActionToolbarAddUserFeatureActionTool( BASE_CLASS_EventCollector $event )
    {
        if ( !OW::getUser()->isAuthorized('base') )
        {
            return;
        }

        $params = $event->getParams();

        if ( empty($params['userId']) )
        {
            return;
        }

        $action = array(
            "group" => 'addition',
            "label" => OW::getLanguage()->text('base', 'profile_toolbar_group_moderation'),
            "order" => 2
        );

        $userId = (int) $params['userId'];

        $uniqId = uniqid("feature-");
        $isFeatured = BOL_UserService::getInstance()->isUserFeatured($userId);

        $action["label"] = $isFeatured ? OW::getLanguage()->text('base', 'user_action_unmark_as_featured') : OW::getLanguage()->text('base', 'user_action_mark_as_featured');

        $toggleText = !$isFeatured ? OW::getLanguage()->text('base', 'user_action_unmark_as_featured') : OW::getLanguage()->text('base', 'user_action_mark_as_featured');

        $action["attributes"] = array();
        $action["attributes"]["data-command"] = $isFeatured ? "unfeature" : "feature";

        $toggleCommand = !$isFeatured ? "unfeature" : "feature";

        $action["href"] = 'javascript://';
        $action["id"] = $uniqId;

        $js = UTIL_JsGenerator::newInstance();
        $js->jQueryEvent("#" . $uniqId, "click",
            'OWM.Users[$(this).attr("data-command") == "feature" ? "featureUser" : "unFeatureUser"](e.data.userId);
            OWM.Utils.toggleText($("span:eq(0)", this), e.data.toggleText);
            OWM.Utils.toggleAttr(this, "data-command", e.data.toggleCommand);'
            , array("e"), array(
            "userId" => $userId,
            "toggleText" => $toggleText,
            "toggleCommand" => $toggleCommand
        ));

        OW::getDocument()->addOnloadScript($js);

        $action["key"] = "base.make_featured";
        $event->add($action);
    }

    public function onActionToolbarAddUserApproveActionTool( BASE_CLASS_EventCollector $event )
    {
        if ( !OW::getUser()->isAuthorized('base') )
        {
            return;
        }

        $params = $event->getParams();

        if ( empty($params['userId']) )
        {
            return;
        }

        $userId = (int) $params['userId'];

        if ( BOL_UserService::getInstance()->isApproved($userId) )
        {
            return;
        }

        $action = array(
            "group" => 'addition',
            "label" => OW::getLanguage()->text('base', 'profile_toolbar_group_moderation'),
            "href" => OW::getRouter()->urlFor('BASE_CTRL_User', 'approve', array('userId' => $userId)),
            "label" => OW::getLanguage()->text('base', 'profile_toolbar_user_approve_label'),
            "class" => '',
            "key" => "base.approve_user",
            "order" => 1
        );

        $event->add($action);
    }

    public function onActionToolbarAddSuspendActionTool( BASE_CLASS_EventCollector $event )
    {
        if ( !OW::getUser()->isAuthorized('base') )
        {
            return;
        }

        $params = $event->getParams();

        if ( empty($params['userId']) )
        {
            return;
        }

        if ( BOL_AuthorizationService::getInstance()->isSuperModerator($params['userId']) )
        {
            return;
        }

        $userService = BOL_UserService::getInstance();
        $userId = (int) $params['userId'];

        $action = array(
            "group" => 'addition',
            "label" => OW::getLanguage()->text('base', 'profile_toolbar_group_moderation'),
            "order" => 5
        );

        $action["href"] = 'javascript://';

        $uniqId = uniqid('pat-suspend-');
        $action["id"] = $uniqId;

        $toogleText = null;
        $toggleCommand = null;
        $toggleClass = null;

        $suspended = $userService->isSuspended($userId);

        $action["attributes"] = array();
        $action["label"] = $suspended ? OW::getLanguage()->text('base', 'user_unsuspend_btn_lbl') : OW::getLanguage()->text('base', 'user_suspend_btn_lbl');

        $toggleText = !$suspended ? OW::getLanguage()->text('base', 'user_unsuspend_btn_lbl') : OW::getLanguage()->text('base', 'user_suspend_btn_lbl');

        $action["attributes"]["data-command"] = $suspended ? "unsuspend" : "suspend";

        $toggleCommand = !$suspended ? "unsuspend" : "suspend";

        $action["class"] = $suspended ? "" : "owm_red_btn";

        $toggleClass = !$suspended ? "owm_context_action_list_item" : "owm_context_action_list_item owm_red_btn";

        $rsp = OW::getRouter()->urlFor('BASE_CTRL_SuspendedUser', 'ajaxRsp');
        $rsp = OW::getRequest()->buildUrlQueryString($rsp, array(
                "userId" => $userId
            ));

        $js = UTIL_JsGenerator::newInstance();
        $js->jQueryEvent("#" . $uniqId, "click",
            'OWM.Users[$(this).attr("data-command") == "suspend" ? "suspendUser" : "unSuspendUser"](e.data.userId);
            OWM.Utils.toggleText($("span:eq(0)", this), e.data.toggleText);
            OWM.Utils.toggleAttr(this, "class", e.data.toggleClass);
            OWM.Utils.toggleAttr(this, "data-command", e.data.toggleCommand);'
            , array("e"), array(
            "userId" => $userId,
            "toggleText" => $toggleText,
            "toggleCommand" => $toggleCommand,
            "toggleClass" => $toggleClass
        ));

        OW::getDocument()->addOnloadScript($js);

        $action["key"] = "base.suspend_user";

        $event->add($action);
    }

    public function onActionToolbarAddDeleteActionTool( BASE_CLASS_EventCollector $event )
    {
        if ( !OW::getUser()->isAuthorized('base') )
        {
            return;
        }

        $params = $event->getParams();

        if ( empty($params['userId']) )
        {
            return;
        }

        if ( BOL_AuthorizationService::getInstance()->isSuperModerator($params['userId']) )
        {
            return;
        }

        $userId = (int) $params['userId'];

        $confirmMsg = OW::getLanguage()->text('base', 'are_you_sure');
        $callbackUrl = OW::getRouter()->urlFor('BASE_MCTRL_User', 'userDeleted');

        $linkId = 'ud' . rand(10, 1000000);
        $script = UTIL_JsGenerator::newInstance()->jQueryEvent('#' . $linkId, 'click',
                'if (confirm(e.data.confirmMsg)) OWM.Users.deleteUser(e.data.userId, e.data.callbackUrl);'
                , array('e'), array('userId' => $userId, "confirmMsg" => $confirmMsg, 'callbackUrl' => $callbackUrl));

        OW::getDocument()->addOnloadScript($script);

        $resultArray = array(
            "label" => OW::getLanguage()->text('base', 'profile_toolbar_user_delete_label'),
            "class" => 'owm_red_btn',
            "href" => 'javascript://',
            "id" => $linkId,
            "group" => 'addition',
            "order" => 5,
            "key" => "base.delete_user"
        );

        $event->add($resultArray);
    }

    public function onPluginsInitCheckUserStatus()
    {
        if ( OW::getUser()->isAuthenticated() )
        {
            $user = BOL_UserService::getInstance()->findUserById(OW::getUser()->getId());

            $signOutDispatchAttrs = OW::getRouter()->getRoute('base_sign_out')->getDispatchAttrs();

            if ( empty($signOutDispatchAttrs['controller']) || empty($signOutDispatchAttrs['action']) )
            {
                $signOutDispatchAttrs['controller'] = 'BASE_CTRL_User';
                $signOutDispatchAttrs['action'] = 'signOut';
            }

            if ( OW::getConfig()->getValue('base', 'mandatory_user_approve') && !BOL_UserService::getInstance()->isApproved() )
            {
                OW::getRequestHandler()->setCatchAllRequestsAttributes('base.wait_for_approval', array('controller' => 'BASE_MCTRL_WaitForApproval', 'action' => 'index'));
                OW::getRequestHandler()->addCatchAllRequestsExclude('base.wait_for_approval', $signOutDispatchAttrs['controller'], $signOutDispatchAttrs['action']);
                OW::getRequestHandler()->addCatchAllRequestsExclude('base.wait_for_approval', 'BASE_MCTRL_AjaxLoader', 'component');
                OW::getRequestHandler()->addCatchAllRequestsExclude('base.wait_for_approval', 'BASE_MCTRL_Invitations', 'command');
                OW::getRequestHandler()->addCatchAllRequestsExclude('base.wait_for_approval', 'BASE_MCTRL_Ping', 'index');
            }

            if ( $user !== null )
            {
                if ( BOL_UserService::getInstance()->isSuspended($user->getId()) && !OW::getUser()->isAdmin() )
                {
                    OW::getRequestHandler()->setCatchAllRequestsAttributes('base.suspended_user', array('controller' => 'BASE_MCTRL_SuspendedUser', 'action' => 'index'));
                    OW::getRequestHandler()->addCatchAllRequestsExclude('base.suspended_user', $signOutDispatchAttrs['controller'], $signOutDispatchAttrs['action']);
                    OW::getRequestHandler()->addCatchAllRequestsExclude('base.suspended_user', 'BASE_MCTRL_AjaxLoader');
                    OW::getRequestHandler()->addCatchAllRequestsExclude('base.suspended_user', 'BASE_MCTRL_Invitations', 'command');
                    OW::getRequestHandler()->addCatchAllRequestsExclude('base.suspended_user', 'BASE_MCTRL_Ping', 'index');
                }

                if ( (int) $user->emailVerify === 0 && OW::getConfig()->getValue('base', 'confirm_email') )
                {
                    OW::getRequestHandler()->setCatchAllRequestsAttributes('base.email_verify', array(OW_RequestHandler::CATCH_ALL_REQUEST_KEY_CTRL => 'BASE_MCTRL_EmailVerify', OW_RequestHandler::CATCH_ALL_REQUEST_KEY_ACTION => 'index'));

                    OW::getRequestHandler()->addCatchAllRequestsExclude('base.email_verify', $signOutDispatchAttrs['controller'], $signOutDispatchAttrs['action']);
                    OW::getRequestHandler()->addCatchAllRequestsExclude('base.email_verify', 'BASE_MCTRL_EmailVerify');
                }

                $accountType = BOL_QuestionService::getInstance()->findAccountTypeByName($user->accountType);

                if ( empty($accountType) )
                {
                    OW::getRequestHandler()->setCatchAllRequestsAttributes('base.complete_profile.account_type', array('controller' => 'BASE_MCTRL_CompleteProfile', 'action' => 'fillAccountType'));
                    OW::getRequestHandler()->addCatchAllRequestsExclude('base.complete_profile.account_type', $signOutDispatchAttrs['controller'], $signOutDispatchAttrs['action']);
                    OW::getRequestHandler()->addCatchAllRequestsExclude('base.complete_profile.account_type', 'BASE_MCTRL_AjaxLoader');
                    OW::getRequestHandler()->addCatchAllRequestsExclude('base.complete_profile.account_type', 'BASE_MCTRL_Invitations');
                    OW::getRequestHandler()->addCatchAllRequestsExclude('base.complete_profile.account_type', 'BASE_MCTRL_Ping');
                    
                }
                else
                {
                    $questionsEditStamp = OW::getConfig()->getValue('base', 'profile_question_edit_stamp');
                    $updateDetailsStamp = BOL_PreferenceService::getInstance()->getPreferenceValue('profile_details_update_stamp', OW::getUser()->getId());

                    if ( $questionsEditStamp >= (int) $updateDetailsStamp )
                    {
                        require_once OW_DIR_CORE . 'validator.php';
                        $questionList = BOL_QuestionService::getInstance()->getEmptyRequiredQuestionsList($user->id);

                        if ( !empty($questionList) )
                        {
                            OW::getRequestHandler()->setCatchAllRequestsAttributes('base.complete_profile', array('controller' => 'BASE_MCTRL_CompleteProfile', 'action' => 'fillRequiredQuestions'));
                            OW::getRequestHandler()->addCatchAllRequestsExclude('base.complete_profile', $signOutDispatchAttrs['controller'], $signOutDispatchAttrs['action']);
                            OW::getRequestHandler()->addCatchAllRequestsExclude('base.complete_profile', 'BASE_MCTRL_AjaxLoader');
                            OW::getRequestHandler()->addCatchAllRequestsExclude('base.complete_profile', 'BASE_MCTRL_Invitations');
                            OW::getRequestHandler()->addCatchAllRequestsExclude('base.complete_profile', 'BASE_MCTRL_Ping');
                        }
                        else
                        {
                            BOL_PreferenceService::getInstance()->savePreferenceValue('profile_details_update_stamp', time(), OW::getUser()->getId());
                        }
                    }
                }
            }
            else
            {
                OW::getUser()->logout();
            }
        }
    }

    public function onNotificationRender( OW_Event $event )
    {
        $params = $event->getParams();
        if ( $params['entityType'] == 'base_profile_wall' )
        {
            $data = $params['data'];
            $event->setData($data);
        }
    }
}
