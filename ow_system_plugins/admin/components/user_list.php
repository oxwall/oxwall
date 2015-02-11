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
 * User list component class. 
 *
 * @author Egor Bulgakov <egor.bulgakov@gmail.com>
 * @package ow.ow_system_plugins.admin.components
 * @since 1.0
 */
class ADMIN_CMP_UserList extends OW_Component
{    
    /**
     * Constructor.
     * 
     * @param string $type
     * @param array $extra
     */
    public function __construct( ADMIN_UserListParams $params )
    {
        parent::__construct();
        
        $language = OW::getLanguage();
        $userService = BOL_UserService::getInstance();
        $authService = BOL_AuthorizationService::getInstance();

        $type = $params->getType();
        $extra = $params->getExtra();
        $formAction = $params->getAction();
        $this->assign('action', $formAction);
        
        // handle form
        if ( OW::getRequest()->isPost() && !empty($_POST['users']) )
        {
            $users = $_POST['users'];

            if ( isset($_POST['suspend']) )
            {
                foreach ( $users as $id )
                {
                    // admin user cannot be suspended
                    if ( $authService->isActionAuthorizedForUser($id, BOL_AuthorizationService::ADMIN_GROUP_NAME) )
                    {
                        continue;
                    }
                    
                    $userService->suspend($id, $_POST['suspend_message']);
                }

                OW::getFeedback()->info($language->text('admin', 'user_feedback_profiles_suspended'));
            }
            else if ( isset($_POST['reactivate']) )
            {
                foreach ( $users as $id )
                {
                    $userService->unsuspend($id);
                }

                OW::getFeedback()->info($language->text('admin', 'user_feedback_profiles_unsuspended'));                
            }
            else if ( isset($_POST['delete']) )
            {
                $deleted = 0;

                foreach ( $users as $id )
                {
                    // admin user cannot be deleted
                    if ( $authService->isActionAuthorizedForUser($id, BOL_AuthorizationService::ADMIN_GROUP_NAME) )
                    {
                        continue;
                    }

                    if ( $userService->deleteUser($id, true) )
                    {
                        $deleted++;
                    }
                }

                OW::getFeedback()->info($language->text('admin', 'user_delete_msg', array('count' => $deleted)));
            }
            else if ( isset($_POST['email_verify']) )
            {
                $userDtos = $userService->findUserListByIdList($users);
                
                foreach ( $userDtos as $dto )
                {
                    /* @var $dto BOL_User */
                    $dto->emailVerify = 1;
                    $userService->saveOrUpdate($dto);
                }

                OW::getFeedback()->info($language->text('admin', 'user_feedback_email_verified'));
            }
            else if ( isset($_POST['email_unverify']) )
            {
                $userDtos = $userService->findUserListByIdList($users);
                
                foreach ( $userDtos as $dto )
                {
                    // admin user cannot be unverified
                    if ( $authService->isActionAuthorizedForUser($dto->id, BOL_AuthorizationService::ADMIN_GROUP_NAME) )
                    {
                        continue;
                    }
                    
                    /* @var $dto BOL_User */
                    $dto->emailVerify = 0;
                    $userService->saveOrUpdate($dto);
                }

                OW::getFeedback()->info($language->text('admin', 'user_feedback_email_unverified'));
            }
            else if ( isset($_POST['disapprove']) )
            {
                foreach ( $users as $id )
                {
                    // admin user cannot be disapproved
                    if ( $authService->isActionAuthorizedForUser($id, BOL_AuthorizationService::ADMIN_GROUP_NAME) )
                    {
                        continue;
                    }
                    
                    $userService->disapprove($id);
                }

                OW::getFeedback()->info($language->text('admin', 'user_feedback_profiles_disapproved'));
            }
            else if ( isset($_POST['approve']) )
            {
                foreach ( $users as $id )
                {
                    if ( !$userService->isApproved($id) )
                    {
                        $userService->approve($id);
                        $userService->sendApprovalNotification($id);
                    }
                }

                OW::getFeedback()->info($language->text('admin', 'user_feedback_profiles_approved'));
            }

            $this->reloadParentPage();
        }

        $onPage = 20;

        $page = isset($_GET['page']) && (int) $_GET['page'] ? (int) $_GET['page'] : 1;
        $first = ( $page - 1 ) * $onPage;

        switch ( $type )
        {
            case 'recent':
                $userList = $userService->findRecentlyActiveList($first, $onPage, false);
                $userCount = $userService->count(false);
                break;

            case 'suspended':
                $userList = $userService->findSuspendedList($first, $onPage);
                $userCount = $userService->countSuspended();
                break;

            case 'unverified':
                $userList = $userService->findUnverifiedList($first, $onPage);
                $userCount = $userService->countUnverified();
                break;

            case 'unapproved':
                $userList = $userService->findUnapprovedList($first, $onPage);
                $userCount = $userService->countUnapproved();
                break;

            case 'search':
                if ( isset($extra['question']) )
                {
                    $search = htmlspecialchars(urldecode($extra['value']));
                    $this->assign('search', $search);

                    $userList = $userService->findUserListByQuestionValues(array($extra['question'] => $search), $first, $onPage, true);
                    $userCount = $userService->countUsersByQuestionValues(array($extra['question'] => $search), true);
                }
                break;

            case 'role':
                $roleId = $extra['roleId'];
                $userList = $userService->findListByRoleId($roleId, $first, $onPage);
                $userCount = $userService->countByRoleId($roleId);
                break;
        }

        if ( !$userList && $page > 1 )
        {
            OW::getApplication()->redirect(OW::getRequest()->buildUrlQueryString(null, array('page' => $page - 1)));
        }
        
        if ( $userList )
        {
            $this->assign('users', $userList);
            $this->assign('total', $userCount);

            // Paging
            $pages = (int) ceil($userCount / $onPage);
            $paging = new BASE_CMP_Paging($page, $pages, $onPage);

            $this->addComponent('paging', $paging);

            $userIdList = array();

            foreach ( $userList as $user )
            {
                if ( !in_array($user->id, $userIdList) )
                {
                    array_push($userIdList, $user->id);
                }
            }

            $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars($userIdList);
            $this->assign('avatars', $avatars);
            
            $userNameList = $userService->getUserNamesForList($userIdList);
            $this->assign('userNameList', $userNameList);

            $questionList = BOL_QuestionService::getInstance()->getQuestionData($userIdList, array('sex', 'birthdate', 'email'));
            $this->assign('questionList', $questionList);

            $sexList = array();
            
            foreach ( $userIdList as $id )
            {
                if ( empty($questionList[$id]['sex']) )
                {
                    
                    continue;
                }

                $sex = $questionList[$id]['sex'];

                if ( !empty($sex) )
                {
                    $sexValue = '';

                    for ( $i = 0 ; $i < 31; $i++ )
                    {
                        $val = pow( 2, $i );
                        if ( (int)$sex & $val  )
                        {
                            $sexValue .= BOL_QuestionService::getInstance()->getQuestionValueLang('sex', $val) . ', ';
                        }
                    }

                    if ( !empty($sexValue) )
                    {
                        $sexValue = substr($sexValue, 0, -2);
                    }
                }

                $sexList[$id] = $sexValue;
            }
            
            $this->assign('sexList', $sexList);

            $userSuspendedList = $userService->findSupsendStatusForUserList($userIdList);
            $this->assign('suspendedList', $userSuspendedList);

            $userUnverfiedList = $userService->findUnverifiedStatusForUserList($userIdList);
            $this->assign('unverifiedList', $userUnverfiedList);

            $userUnapprovedList = $userService->findUnapprovedStatusForUserList($userIdList);
            $this->assign('unapprovedList', $userUnapprovedList);

            $onlineStatus = $userService->findOnlineStatusForUserList($userIdList);
            $this->assign('onlineStatus', $onlineStatus);
            
            $moderatorList = $authService->getModeratorList();
            $adminList = array();
            
            /* @var $moderator BOL_AuthorizationModerator */
            foreach ( $moderatorList as $moderator )
            {
                $userId = $moderator->getUserId();
                if ( $userService->findUserById($userId) !== null && $authService->isActionAuthorizedForUser($userId, BOL_AuthorizationService::ADMIN_GROUP_NAME) )
                {
                    $adminList[] = $userId;
                }
            }
            $this->assign('adminList', $adminList);
        }
        else
        {
            $this->assign('users', null);
        }

        $this->assign('adminId', OW::getUser()->getId());
        $this->assign('buttons', $params->getButtons());
        
        $script = '$("#check-all").click(function() {
            $("#user-list-form input:not(:disabled)[type=checkbox]").attr("checked", $(this).attr("checked") == "checked");
        });';
        
        OW::getDocument()->addOnloadScript($script);
    }

    private function reloadParentPage()
    {
        $router = OW::getRouter();

        OW::getApplication()->redirect(OW::getRequest()->buildUrlQueryString());
    }
}

final class ADMIN_UserListParams
{
    private $action;
    
    private $type;
    
    private $buttons = array();
    
    private $extra = array();
    
    public function __construct() 
    {
        $lang = OW::getLanguage();
        
        $this->buttons['delete'] = array('name' => 'delete', 'id' => 'delete_user_btn', 'label' => $lang->text('base', 'delete'), 'class' => 'ow_mild_red');
        $this->buttons['delete']['js'] = '$("#delete_user_btn").click(function(){
            
            var $form_content = $("#delete-user-confirm").children();
    
            window.delete_user_floatbox = new OW_FloatBox({
                $title: '.json_encode($lang->text('base', 'delete_user_confirmation_label')).',
                $contents: $form_content,
                icon_class: "ow_ic_delete",
                width: 450
            });
            
            return false;
        });
        
        $("#button-confirm-user-delete").click(function(){
            var $form = $("#user-list-form");
            $form.append("<input type=\"hidden\" name=\"delete\" value=\"Delete\" />");
            $form.submit();
        });';
    }
    
    public function addButton( array $button )
    {
        $this->buttons[$button['name']] = $button;
    }
    
    public function setAction( $action )
    {
        $this->action = $action;
    }
    
    public function setType( $type )
    {
        $this->type = $type;
    }
    
    public function setExtra( $extra )
    {
        $this->extra = $extra;
    }
    
    public function getButtons()
    {
        return $this->buttons;
    }
    
    public function getAction()
    {
        return $this->action;
    }
    
    public function getType()
    {
        return $this->type;
    }
    
    public function getExtra()
    {
        return $this->extra;
    }
}