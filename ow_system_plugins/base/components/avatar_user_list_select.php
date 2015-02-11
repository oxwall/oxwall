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
class BASE_CMP_AvatarUserListSelect extends OW_Component
{
    /**
     * @var string
     */
    private $buttonLabel;
    /**
     * @var string
     */
    private $countLabel;
    /**
     * @var string
     */
    private $headingLabel;
    /**
     * @var array
     */
    private $idList;

    /**
     * Constructor.
     *
     * @param array $idList
     */
    public function __construct( array $idList, $langs = array() )
    {
        parent::__construct();

        $this->countLabel = OW::getLanguage()->text('base', 'avatar_user_list_select_count_label');
        $this->buttonLabel = OW::getLanguage()->text('base', 'avatar_user_list_select_button_label');
        $this->idList = $idList;

        if ( !empty($langs['buttonLabel']) )
        {
            $this->buttonLabel = $langs['buttonLabel'];
        }

        if ( array_key_exists('countLabel', $langs) )
        {
            $this->countLabel = $langs['countLabel'];
        }

        if ( !empty($langs['headingLabel']) )
        {
            $this->headingLabel = $langs['headingLabel'];
        }
    }

    /**
     * @param string $buttonLabel
     */
    public function setButtonLabel( $buttonLabel )
    {
        $this->buttonLabel = $buttonLabel;
    }

    /**
     * @param string $countLabel
     */
    public function setCountLabel( $countLabel )
    {
        $this->countLabel = $countLabel;
    }

    /**
     * @param string $generalLabel
     */
    public function setGeneralLabel( $generalLabel )
    {
        $this->headingLabel = $generalLabel;
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $contexId = UTIL_HtmlTag::generateAutoId('cmp');
        $this->assign('contexId', $contexId);
        
        if ( empty($this->idList) )
        {
            return;
        }

        $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars($this->idList, true, false, false);
        $this->assign('avatars', $avatars);

        $displayNames = BOL_UserService::getInstance()->getDisplayNamesForList($this->idList);
        $usernames = BOL_UserService::getInstance()->getUserNamesForList($this->idList);
        $orderdList = BOL_UserService::getInstance()->getRecentlyActiveOrderedIdList($this->idList);

        $this->idList = array();

        foreach( $orderdList as $list )
        {
           $this->idList[] =  $list['id'];
        }

        $arrayToAssign = array();
        $jsArray = array();

        foreach ( $this->idList as $id )
        {
            $linkId = UTIL_HtmlTag::generateAutoId('user-select');

            if ( !empty($avatars[$id]) )
            {
                $avatars[$id]['url'] = 'javascript://';
            }

            $arrayToAssign[$id] = array(
                'id' => $id,
                'title' => empty($displayNames[$id]) ? '_DISPLAY_NAME_' : $displayNames[$id],
                'linkId' => $linkId,
                'username' => $usernames[$id]
            );

            $jsArray[$id] = array(
                'linkId' => $linkId,
                'userId' => $id
            );
        }

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'avatar_user_select.js');
        OW::getDocument()->addOnloadScript("
            var cmp = new AvatarUserSelect(" . json_encode($jsArray) . ", '" . $contexId . "');
            cmp.init();  ");

        OW::getLanguage()->text('base', 'avatar_user_select_empty_list_message');

        $this->assign('users', $arrayToAssign);

        $langs = array(
            'countLabel' => $this->countLabel,
            'startCountLabel' => (!empty($this->countLabel) ? str_replace('#count#', '0', $this->countLabel) : null ),
            'buttonLabel' => $this->buttonLabel,
            'startButtonLabel' => str_replace('#count#', '0', $this->buttonLabel)
        );
        $this->assign('langs', $langs);
    }
}