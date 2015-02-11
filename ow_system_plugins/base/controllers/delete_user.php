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
 * @package ow_system_plugins.base.controllers
 * @since 1.0
 */

class BASE_CTRL_DeleteUser extends OW_ActionController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index( $params )
    {
        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        if ( OW::getUser()->isAdmin() )
        {
            throw new Redirect404Exception();
        }

        $language = OW::getLanguage();

        $this->setPageHeading($language->text('base', 'delete_user_index'));

        $userId = OW::getUser()->getId();

        if ( OW::getRequest()->isPost() && !(OW::getRequest()->isAjax()) )
        {
            if ( isset( $_POST['delete_user_button'] ) )
            {
                OW::getUser()->logout();

                BOL_UserService::getInstance()->deleteUser($userId, true);

                $this->redirect( OW::getRouter()->urlForRoute('base_index') );
            }

            if ( isset( $_POST['cansel_button'] ) )
            {
                $this->redirect( OW::getRouter()->urlForRoute('base_edit') );
            }
        }
    }
}
