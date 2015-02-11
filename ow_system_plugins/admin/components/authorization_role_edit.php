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
 * Authorization role edit component class 
 *
 * @author Egor Bulgakov <egor.bulgakov@gmail.com>
 * @package ow.ow_system_plugins.admin.components
 * @since 1.0
 */
class ADMIN_CMP_AuthorizationRoleEdit extends OW_Component
{
    /**
     * @param int $roleId
     */
    public function __construct( $roleId )
    {
        parent::__construct();

        $language = OW::getLanguage();
        
        $role = BOL_AuthorizationService::getInstance()->getRoleById($roleId);
        
        $this->assign('role', $role);
        $this->assign('roleLabel', BOL_AuthorizationService::getInstance()->getRoleLabel($role->name));
        
        $form = new EditRoleForm($role);
        $this->addForm($form);
        
        $colors = array(
            '#999999', '#85db18', '#a7c520', '#046390', '#db4105', '#ff9800', '#01a2a6', 
            '#29d9c2', '#dc3522', '#1a9481', '#003d5c', '#046380', '#f23005', '#8b0f03', 
            '#2f6d7a', '#70a99a', '#b6d051', '#b52841', '#ff8939', '#e85f4d', '#590051', 
            '#303133', '#585956', '#99917c', '#ccc794', '#e66a00'
        );
        
        $this->assign('colors', $colors);
        
        $this->assign('defaultAvatarUrl', BOL_AvatarService::getInstance()->getDefaultAvatarUrl());
                
        $js = '$("input[name=displayLabel]", "#role-edit-cont").change(function(){
            if ( $(this).attr("checked") )
            {
                $("#color-select-cont").css("display", "block");
                $(".ow_avatar_label", "#role-edit-cont").css("display", "inline-block");
            }
            else
            {
                $("#color-select-cont").css("display", "none");
                $(".ow_avatar_label", "#role-edit-cont").css("display", "none");
            }
        });
        $(".color_sample", "#role-edit-cont").click(function(){
            var color = $(this).css("background-color");
            $("#label-color-field").val(color);
            $(".ow_avatar_label", "#role-edit-cont").css("background-color", color);
        });';
        
        if ( !$role->displayLabel )
        {
            $js .= '$(".ow_avatar_label", "#role-edit-cont").css("display", "none");';
        }
        if ( !empty($role->custom) )
        {
            $js .= '$(".ow_avatar_label", "#role-edit-cont").css("background-color", "'.$role->custom.'");';
        }
        
        OW::getDocument()->addOnloadScript($js);
    }
    
    public static function process( $data )
    {
        $authService = BOL_AuthorizationService::getInstance();
        
        $roleId = (int) $data['roleId'];
        $role = $authService->getRoleById($roleId);
        
        $resp = array();
        
        if ( !$role )
        {
            $resp['error'] = "Role not found";
            echo json_encode($resp);
            exit;
        }
        
        $role->displayLabel = $data['displayLabel'] == 'on' ? 1 : 0;
        $role->custom = $role->displayLabel ? $data['labelColor'] : null;
        
        $authService->saveRole($role);
        
        $resp['message'] = OW::getLanguage()->text('admin', 'permissions_role_updated');
        echo json_encode($resp);
        exit;
    }
}

class EditRoleForm extends Form
{
    public function __construct( BOL_AuthorizationRole $role )
    {
        parent::__construct('edit-role-form');
        
        $this->setAjax(true);
        $this->setAction(OW::getRouter()->urlFor('ADMIN_CTRL_Users', 'ajaxEditRole'));
        
        $roleId = new HiddenField('roleId');
        $roleId->setValue($role->id);
        $this->addElement($roleId);
        
        $displayLabel = new CheckboxField('displayLabel');
        $displayLabel->setValue($role->displayLabel);
        $this->addElement($displayLabel);
        
        $color = new HiddenField('labelColor');
        $color->setValue(!empty($role->custom) ? $role->custom : '#999999');
        $color->setId('label-color-field');
        $this->addElement($color);
        
        $submit = new Submit('save');
        $this->addElement($submit);
        
        $js = 'owForms["'.$this->getName().'"].bind("success", function(data){
            if ( data.error != undefined ){
                OW.error(data.error);
            }
            if ( data.message != undefined ){
                OW.info(data.message);
            }
            document.location.reload();
        });';
        
        OW::getDocument()->addOnloadScript($js);
    }
}