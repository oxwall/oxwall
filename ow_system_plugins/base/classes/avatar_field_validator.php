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
 * Avatar field form element validator.
 *
 * @author Egor Bulgakov <egor.bulgakov@gmail.com>
 * @package ow.ow_system_plugins.base.bol
 * @since 1.7.2
 */
class BASE_CLASS_AvatarFieldValidator extends OW_Validator
{
    protected $required = false;
    protected $userId = null;

    /**
     * @param bool $required
     */
    public function __construct( $required = false, $userId = null )
    {
        $this->required = $required;
        $this->userId = $userId;

        $language = OW::getLanguage();
        $this->setErrorMessage($language->text('base', 'form_validator_required_error_message'));
    }

    /**
     * Is avatar valid
     *
     * @param string $value
     * @return bool
     */
    public function isValid( $value )
    {
        if ( !$this->required )
        {
            return true;
        }

        $language = OW::getLanguage();
        $avatarService = BOL_AvatarService::getInstance();

        $key = $avatarService->getAvatarChangeSessionKey();
        $path = $avatarService->getTempAvatarPath($key, 3);

        $userId = $this->userId ? $this->userId : OW::getUser()->getId();

        if ( !$value  || (!file_exists($path) && !BOL_AvatarService::getInstance()->getAvatarUrl($userId, 1)) )
        {
            return false;
        }

        if ( !is_writable(BOL_AvatarService::getInstance()->getAvatarsDir()) )
        {
            $this->setErrorMessage($language->text('base', 'not_writable_avatar_dir'));

            return false;
        }

        return true;
    }

    /**
     * @see Validator::getJsValidator()
     *
     * @return string
     */
    public function getJsValidator()
    {
        $condition = '';

        if ( $this->required )
        {
            $condition = "
            if ( value == undefined || $.trim(value).length == 0 ) {
                throw " . json_encode($this->getError()) . ";
            }";
        }

        return "{
                validate : function( value ){ " . $condition . " },
                getErrorMessage : function(){ return " . json_encode($this->getError()) . " }
        }";
    }
}