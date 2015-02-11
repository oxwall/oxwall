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
 * Profile action toolbar change role component.
 *
 * @author Aybat Duyshokov <duyshokov@gmail.com>, Sardar Madumarov <madumarov@gmail.com>
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
class BASE_CMP_GiveUserRole extends OW_Component
{

    /**
     * @param integer $userId
     */
    public function __construct( $userId )
    {
        parent::__construct();

        $user = BOL_UserService::getInstance()->findUserById((int) $userId);

        if ( !OW::getUser()->isAuthorized('base') || $user === null )
        {
            $this->setVisible(false);
            return;
        }

        $aService = BOL_AuthorizationService::getInstance();
        $roleList = $aService->findNonGuestRoleList();

        $form = new Form('give-role');
        $form->setAjax(true);
        $form->setAction(OW::getRouter()->urlFor('BASE_CTRL_User', 'updateUserRoles'));
        $hidden = new HiddenField('userId');
        $form->addElement($hidden->setValue($userId));
        $userRoles = $aService->findUserRoleList($user->getId());

        $userRolesIdList = array();
        foreach ( $userRoles as $role )
        {
            $userRolesIdList[] = $role->getId();
        }

        $tplRoleList = array();

        /* @var $role BOL_AuthorizationRole */
        foreach ( $roleList as $role )
        {
            $field = new CheckboxField('roles[' . $role->getId() . ']');
            $field->setLabel(OW::getLanguage()->text('base', 'authorization_role_' . $role->getName()));
            $field->setValue(in_array($role->getId(), $userRolesIdList));
            if (in_array($role->getId(), $userRolesIdList) && $role->getSortOrder() == 1)
            {
                $field->addAttribute('disabled', 'disabled');
            }

            $form->addElement($field);

            $tplRoleList[$role->sortOrder] = $role;
        }

        ksort($tplRoleList);

        $form->addElement(new Submit('submit'));

        OW::getDocument()->addOnloadScript(
            "owForms['{$form->getName()}'].bind('success', function(data){
                if( data.result ){
                    if( data.result == 'success' ){
                         window.baseChangeUserRoleFB.close();
                         window.location.reload();
                         //OW.info(data.message);
                    }
                    else if( data.result == 'error'){
                        OW.error(data.message);
                    }
                }
		})");

        $this->addForm($form);
        $this->assign('list', $tplRoleList);
    }
}