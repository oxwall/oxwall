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
 * @author Podiachev Evgenii <joker.OW2@gmail.com>
 * @package ow.ow_system_plugins.base.bol
 * @since 1.8.6
 */
class BASE_MCLASS_EditAvatarFieldValidator extends BASE_MCLASS_JoinAvatarFieldValidator
{
    /**
     * @param mixed $value
     * @return bool
     */
    public function isValid( $value )
    {
        if ( !$this->required
            || BOL_AvatarService::getInstance()->getAvatarUrl($this->userId, 1, null, true, false))
        {
            return true;
        }

        $language = OW::getLanguage();

        if ( !UTIL_File::validateImage($_FILES['userPhoto']['name']) || !getimagesize($_FILES['userPhoto']['tmp_name']) )
        {
            $this->setErrorMessage($language->text('base', 'not_valid_image'));

            return false;
        }

        if ( !is_writable(BOL_AvatarService::getInstance()->getAvatarsDir()) )
        {
            $this->setErrorMessage($language->text('base', 'not_writable_avatar_dir'));

            return false;
        }

        if ( empty($_FILES['userPhoto']['name']) )
        {
            return false;
        }

        if ( !empty($_FILES['userPhoto']['error']) )
        {
            $this->setErrorMessage(BOL_FileService::getInstance()->getUploadErrorMessage($_FILES['userPhoto']['error']));

            return false;
        }
        
        return true;
    }
}
