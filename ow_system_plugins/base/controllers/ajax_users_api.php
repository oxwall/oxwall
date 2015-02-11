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
 * API Responder
 *
 * @author Sergey Kambalin <greyexpert@gmail.com>
 * @package ow_system_plugins.base.controllers
 * @since 1.0
 */
class BASE_CTRL_AjaxUsersApi extends OW_ActionController
{
    private function checkAdmin()
    {
        if ( !OW::getUser()->isAuthorized('base') )
        {
            throw Exception("Not authorized action");
        }
    }

    private function checkAuthenticated()
    {
        if ( !OW::getUser()->isAuthenticated() )
        {
            throw Exception("Not authenticated user");
        }
    }

    public function rsp()
    {
        if ( !OW::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }

        $command = trim($_GET['command']);
        $query = json_decode($_GET['params'], true);

        $response = call_user_func(array($this, $command), $query);
        
        /*try
        {
            $response = call_user_func(array($this, $command), $query);
        }
        catch ( Exception $e )
        {
            $response = array(
                "error" => $e->getMessage(),
                'type' => 'error'
            );
        }*/

        $response = empty($response) ? array() : $response;
        echo json_encode($response);
        exit;
    }

    private function suspend( $params )
    {
        $this->checkAdmin();

        BOL_UserService::getInstance()->suspend($params["userId"], $params["message"]);

        return array(
            "info" => OW::getLanguage()->text('base', 'user_feedback_profile_suspended')
        );
    }
    
    private function deleteUser( $params )
    {
        $this->checkAdmin();
        
        BOL_UserService::getInstance()->deleteUser($params["userId"]);

        return array(
            "info" => OW::getLanguage()->text('base', 'user_deleted_page_message')
        );
    }

    private function unsuspend( $params )
    {
        $this->checkAdmin();

        BOL_UserService::getInstance()->unsuspend($params["userId"]);

        return array(
            "info" => OW::getLanguage()->text('base', 'user_feedback_profile_unsuspended')
        );
    }

    private function block( $params )
    {
        $this->checkAuthenticated();
        BOL_UserService::getInstance()->block($params["userId"]);

        return array(
            "info" => OW::getLanguage()->text('base', 'user_feedback_profile_blocked')
        );
    }

    private function unblock( $params )
    {
        $this->checkAuthenticated();
        BOL_UserService::getInstance()->unblock($params["userId"]);

        return array(
            "info" => OW::getLanguage()->text('base', 'user_feedback_profile_unblocked')
        );
    }

    private function feature( $params )
    {
        $this->checkAdmin();
        BOL_UserService::getInstance()->markAsFeatured($params["userId"]);

        return array(
            "info" => OW::getLanguage()->text('base', 'user_feedback_marked_as_featured')
        );
    }

    private function unfeature( $params )
    {
        $this->checkAdmin();
        BOL_UserService::getInstance()->cancelFeatured($params["userId"]);

        return array(
            "info" => OW::getLanguage()->text('base', 'user_feedback_unmarked_as_featured')
        );
    }

}