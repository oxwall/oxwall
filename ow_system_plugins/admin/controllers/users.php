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
 * Users action controller
 *
 * @package ow.ow_system_plugins.admin.controllers
 * @since 1.0
 */
class ADMIN_CTRL_Users extends ADMIN_CTRL_Abstract
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Returns menu component
     *
     * @return BASE_CMP_ContentMenu
     */
    private function getMenu()
    {
        $language = OW::getLanguage();

        $menuItems = array();

        $keys = array('recent', 'suspended', 'unverified', 'unapproved');
        $labels = array('recently_active', 'suspended', 'unverified', 'unapproved');
        $icons = array('clock', 'delete', 'mail', 'ok');

        $approveEnabled = OW::getConfig()->getValue('base', 'mandatory_user_approve');
        foreach ( $keys as $ord => $key )
        {
            if ( $key == 'unapproved' && !$approveEnabled )
            {
                continue;
            }
            
            $urlParams = $key == 'recent' ? array() : array('list' => $key);

            $item = new BASE_MenuItem();
            $item->setLabel($language->text('admin', 'menu_item_users_' . $labels[$ord]));
            $item->setUrl(OW::getRouter()->urlForRoute('admin_users_browse', $urlParams));
            $item->setKey($key);
            $item->setIconClass('ow_ic_' . $icons[$ord]);
            $item->setOrder($ord);

            array_push($menuItems, $item);
        }

        return new BASE_CMP_ContentMenu($menuItems);
    }

    /**
     * User list page controller
     *
     * @param array $params
     */
    public function index( array $params )
    {
        $language = OW::getLanguage();
        
        OW::getDocument()->getMasterPage()->getMenu(OW_Navigation::ADMIN_USERS)->getElement('sidebar_menu_item_users')->setActive(true);

        // invite members
        $form = new Form('invite-members');

        $hidden = new HiddenField('invite_members');
        $hidden->setValue('1');
        $form->addElement($hidden);

        $emails = new Textarea('emails');
        $form->addElement($emails);
        $emails->setRequired();
        $emails->setHasInvitation(true);
        $emails->setInvitation($language->text('admin', 'invite_members_textarea_invitation_text', array('limit' => (int)OW::getConfig()->getValue('base', 'user_invites_limit'))));

        $submit = new Submit('submit');
        $submit->setValue($language->text('admin', 'invite_members_submit_label'));
        $form->addElement($submit);

        $this->addForm($form);

        if ( OW::getRequest()->isPost() && isset($_POST['invite_members']) )
        {
            if ( $form->isValid($_POST) )
            {
                $data = $form->getValues();
                $emails = array_unique(preg_split('/\n/', $data['emails']));

                $emailList = array();

                foreach ( $emails as $email )
                {
                    if ( UTIL_Validator::isEmailValid(trim($email)) )
                    {
                        $emailList[] = trim($email);
                    }
                }

                if ( sizeof($emailList) > (int)OW::getConfig()->getValue('base', 'user_invites_limit') )
                {
                    OW::getFeedback()->error($language->text('admin', 'invite_members_max_limit_message', array('limit' => (int)OW::getConfig()->getValue('base', 'user_invites_limit'))));
                    $form->getElement('emails')->setValue($data['emails']);
                    $this->redirect();
                }

                if ( empty($emailList) )
                {
                    OW::getFeedback()->error($language->text('admin', 'invite_members_min_limit_message'));
                    $form->getElement('emails')->setValue($data['emails']);
                    $this->redirect();
                }

                foreach ( $emailList as $email )
                {                    
                    BOL_UserService::getInstance()->sendAdminInvitation($email);
                }

                OW::getFeedback()->info($language->text('admin', 'invite_members_success_message'));
                $this->redirect();
            }
        }

        $language->addKeyForJs('admin', 'invite_members_cap_label');
        $language->addKeyForJs('admin', 'admin_suspend_floatbox_title');

        $menu = $this->getMenu();
        $this->addComponent('menu', $menu);

        if ( !empty($_GET['search']) && !empty($_GET['search_by']) )
        {
            $extra = array('question' => $_GET['search_by'], 'value' => $_GET['search']);
            $type = 'search';
        }
        else
        {
            $extra = null;
            $type = isset($params['list']) ? $params['list'] : 'recent';
        }
        
        $buttons['suspend'] = array('name' => 'suspend', 'id' => 'suspend_user_btn', 'label' => $language->text('base', 'suspend_user_btn'), 'class' => 'ow_mild_red');
        $buttons['suspend']['js'] = ' $("#suspend_user_btn").click(function(e){ 
            e.preventDefault();
            OW.ajaxFloatBox("ADMIN_CMP_SetSuspendMessage", [],{width: 520, title: OW.getLanguageText(\'admin\', \'admin_suspend_floatbox_title\')}); 
            return false;
        }); ';
        
        $buttons['unverify'] = array('name' => 'email_unverify', 'id' => 'email_unverify_user_btn', 'label' => $language->text('base', 'mark_email_unverified_btn'), 'class' => 'ow_mild_red');
        $buttons['unsuspend'] = array('name' => 'reactivate', 'id' => 'unsuspend_user_btn', 'label' => $language->text('base', 'unsuspend_user_btn'), 'class' => 'ow_mild_green');
        $buttons['verify'] = array('name' => 'email_verify', 'id' => 'email_verify_user_btn', 'label' => $language->text('base', 'mark_email_verified_btn'), 'class' => 'ow_mild_green');
        $buttons['approve'] = array('name' => 'approve', 'id' => 'approve_user_btn', 'label' => $language->text('base', 'approve_user_btn'), 'class' => 'ow_mild_green');
        //$buttons['disapprove'] = array('name' => 'disapprove', 'id' => 'disapprove_user_btn', 'label' => $language->text('base', 'disapprove_user_btn'), 'class' => 'ow_mild_red');
        
        $par = new ADMIN_UserListParams();
        $par->setType($type);
        $par->setExtra($extra);
        
        switch ( $type )
        {
            case 'recent';
            case 'search':
                $par->addButton($buttons['suspend']);
                $par->addButton($buttons['unsuspend']);
                $par->addButton($buttons['unverify']);
                $par->addButton($buttons['verify']);
                $par->addButton($buttons['approve']);
                //$par->addButton($buttons['disapprove']);
                break;
                
            case 'suspended':
                $par->addButton($buttons['unsuspend']);
                break;
                
            case 'unverified':
                $par->addButton($buttons['verify']);
                break;
                
            case 'unapproved':
                $par->addButton($buttons['approve']);
                break;
        }
        
        $usersCmp = OW::getClassInstance("ADMIN_CMP_UserList", $par);
        $this->addComponent('userList', $usersCmp);

        if ( !OW::getRequest()->isAjax() )
        {
            OW::getDocument()->setHeading(OW::getLanguage()->text('admin', 'heading_browse_users'));
            OW::getDocument()->setHeadingIconClass('ow_ic_user');

            $menuElement = $menu->getElement($type);
            if ( $menuElement )
            {
                $menuElement->setActive(true);
            }
        }
        
        $this->assign('totalUsers', BOL_UserService::getInstance()->count(true));
        
        $question = OW::getConfig()->getValue('base', 'display_name_question');
        
        $searchQ = array(
            $question => $language->text('base', 'questions_question_'.$question.'_label'),
            'email' => $language->text('base', 'questions_question_email_label')
        );
        $this->assign('searchQ', $searchQ);
        
        $this->assign('currentSearch', array(
            'question' => !empty($_GET['search_by']) ? $_GET['search_by'] : '',
            'value' => !empty($_GET['search']) ? htmlspecialchars($_GET['search']) : ''
        ));
        
        $this->assign('userSearchUrl', OW::getRouter()->urlForRoute('admin_users_browse'));
    }

    public function roles( array $params )
    {        
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'jquery-ui.min.js');

        $service = BOL_UserService::getInstance();

        $roleService = BOL_AuthorizationService::getInstance();

        $roles = $roleService->findNonGuestRoleList();
       

        $list = array();

        $total = $service->count(true);

        foreach ( $roles as $role )
        {            
            $userCount = $roleService->countUserByRoleId($role->getId());           

            $list[$role->getId()] = array(
                'dto' => $role,
                'userCount' => $userCount,
            );
        }
        
        $this->assign( 'set', $list );

        $this->assign( 'total', $total );

        $addRoleForm = new AddRoleForm();

        if ( OW::getRequest()->isPost() && $addRoleForm->isValid( $_POST ) )
        {
            $addRoleForm->process($addRoleForm->getValues());

            $this->redirect();
        }

        $this->addForm( $addRoleForm );
        
        OW::getLanguage()->addKeyForJs('admin', 'permissions_edit_role_btn');

        OW::getDocument()->setHeadingIconClass('ow_ic_user');
        OW::getDocument()->setHeading(OW::getLanguage()->text('admin', 'heading_user_roles'));
        
        // users roles
        $service = BOL_AuthorizationService::getInstance();
        $this->assign('formAction', OW::getRouter()->urlFor('ADMIN_CTRL_Permissions', 'savePermissions'));

        $roles = $service->getRoleList();
        $actions = $service->getActionList();
        $groups = $service->getGroupList();
        $permissions = $service->getPermissionList();

        $groupActionList = array();

        foreach ( $groups as $group )
        {
            /* @var $group BOL_AuthorizationGroup */
            $groupActionList[$group->id]['name'] = $group->name;
            $groupActionList[$group->id]['actions'] = array();
        }

        foreach ( $actions as $action )
        {
            /* @var $action BOL_AuthorizationAction */
            $groupActionList[$action->groupId]['actions'][] = $action;
        }

        foreach ( $groupActionList as $key => $value )
        {
            if ( count($value['actions']) === 0 || !OW::getPluginManager()->isPluginActive($value['name']) )
            {
                unset($groupActionList[$key]);
            }
        }

        $perms = array();
        foreach ( $permissions as $permission )
        {
            /* @var $permission BOL_AuthorizationPermission */
            $perms[$permission->actionId][$permission->roleId] = true;
        }

        $tplRoles = array();
        foreach ( $roles as $role )
        {
            $tplRoles[$role->sortOrder] = $role;
        }

        ksort($tplRoles);

        $this->assign('perms', $perms);
        $this->assign('roles', $tplRoles);
        $this->assign('colspanForRoles', count($roles) + 1);
        $this->assign('groupActionList', $groupActionList);
        $this->assign('guestRoleId', $service->getGuestRoleId());

        // SD code below - collecting group labels
        $event = new BASE_CLASS_EventCollector('admin.add_auth_labels');
        OW::getEventManager()->trigger($event);
        $data = $event->getData();

        $dataLabels = empty($data) ? array() : call_user_func_array('array_merge', $data);
        $this->assign('labels', $dataLabels);
    }

    public function role( array $params )
    {
        if ( !empty($params['roleId']) )
        {
            $par = new ADMIN_UserListParams();
            $par->setType('role');
            $par->setExtra(array('roleId' => (int) $params['roleId']));
            
            $this->addComponent('userList', new ADMIN_CMP_UserList($par));

            $role = BOL_AuthorizationService::getInstance()->getRoleById((int) $params['roleId']);
            $roleLabel = OW::getLanguage()->text('base', 'authorization_role_' . $role->name);

            OW::getDocument()->setHeading(OW::getLanguage()->text('admin', 'heading_user_role', array('role' => $roleLabel)));
        }

        OW::getDocument()->setHeadingIconClass('ow_ic_user');

        $js = UTIL_JsGenerator::newInstance()
                ->newVariable('rolesUrl', OW::getRouter()->urlForRoute('admin_user_roles'))
                ->jQueryEvent('#back-to-roles', 'click', 'document.location.href = rolesUrl');

        OW::getDocument()->addOnloadScript($js);
    }

    public function deleteRoles()
    {
        $service = BOL_AuthorizationService::getInstance();

        if (empty($_POST['role']) || !is_array($_POST['role']))
        {
            $this->redirect(OW::getRouter()->urlFor('ADMIN_CTRL_Users', 'roles'));
        }

        foreach ( $_POST['role'] as $id )
        {
            $service->deleteRoleById($id);
        }

        $languageService = BOL_LanguageService::getInstance();

        $languageService->generateCache($languageService->getCurrent()->getId());

        OW::getFeedback()->info(OW::getLanguage()->text('admin', 'permissions_roles_deleted_msg'));

        $this->redirect(OW::getRouter()->urlFor('ADMIN_CTRL_Users', 'roles'));
    }

    public function ajaxReorder()
    {
        if ( !OW::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }

        if ( empty($_POST) )
        {
            exit('{}');
        }

        BOL_AuthorizationService::getInstance()->reorderRoles($_POST['order']);
        exit();
    }
    
    public function ajaxEditRole( )
    {
        if ( !OW::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }
        
        ADMIN_CMP_AuthorizationRoleEdit::process($_POST);
    }
    
}

class AddRoleForm extends Form
{

    public function __construct()
    {
        parent::__construct('add-role');

        $textField = new TextField('label');

        $this->addElement($textField->setRequired(true)->setLabel(OW::getLanguage()->text('admin', 'permissions_add_form_role_lbl')));

        $submit = new Submit('submit');

        $submit->setValue(OW::getLanguage()->text('admin', 'permissions_add_role_btn'));

        $this->addElement($submit);
    }

    public function process( $data )
    {
        $label = $data['label'];

        $service = BOL_AuthorizationService::getInstance();

        $service->addRole($label);

        OW::getFeedback()->info(OW::getLanguage()->text('admin', 'permissions_role_added_msg'));
    }
}
