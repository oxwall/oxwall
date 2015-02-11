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
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
class BASE_CMP_IndexWallWidget extends BASE_CLASS_Widget
{

    /**
     * @return Constructor.
     */
    public function __construct( $paramObj )
    {
        parent::__construct();

        // check if comments are empty and user can't add comments
        if( (int)BOL_CommentService::getInstance()->findCommentCount('base_index_wall', 1) === 0 )
        {
            if( !OW::getUser()->isAuthenticated() )
            {
                $this->setVisible(false);
            }
        }
        
        $params = $paramObj->customParamList;

        $commentParams = new BASE_CommentsParams('base', 'base_index_wall');

        if ( isset($params['comments_count']) )
        {
            $commentParams->setCommentCountOnPage($params['comments_count']);
        }

        $commentParams->setDisplayType(BASE_CommentsParams::DISPLAY_TYPE_TOP_FORM_WITH_PAGING);
        $commentParams->setWrapInBox(false);

        $this->addComponent('comments', new BASE_CMP_Comments($commentParams));
    }

    public static function getSettingList()
    {
        $settingList = array();
        $settingList['comments_count'] = array(
            'presentation' => self::PRESENTATION_SELECT,
            'label' => OW::getLanguage()->text('base', 'cmp_widget_wall_comments_count'),
            'optionList' => array('3' => 3, '5' => 5, '10' => 10, '20' => 20, '50' => 50),
            'value' => 10
        );

        return $settingList;
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => false,
            self::SETTING_TITLE => OW::getLanguage()->text('base', 'comments_widget_label'),
            self::SETTING_WRAP_IN_BOX => false
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }
}