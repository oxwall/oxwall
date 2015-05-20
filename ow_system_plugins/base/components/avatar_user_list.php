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
class BASE_CMP_AvatarUserList extends OW_Component
{
    const CSS_CLASS_MINI_AVATAR = 'ow_mini_avatar';

    /**
     * @var array
     */
    protected $idList;
    /**
     * @var string
     */
    protected $viewMoreUrl;
    /**
     * @var boolean
     */
    protected $emptyListNoRender = false;
    /**
     * @var atring
     */
    protected $customCssClass = '';

    public function setViewMoreUrl( $viewMoreUrl )
    {
        $this->viewMoreUrl = trim($viewMoreUrl);
    }

    public function setEmptyListNoRender( $emptyListNoRender )
    {
        $this->emptyListNoRender = (bool) $emptyListNoRender;
    }

    public function setIdList( array $idList )
    {
        $this->idList = $idList;
    }

    public function setCustomCssClass( $customCssClass )
    {
        $this->customCssClass = (string) $customCssClass;
    }

    /**
     * Constructor.
     *
     * @param array $idList
     */
    public function __construct( array $idList = array() )
    {
        parent::__construct();
        $this->idList = $idList;
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        if ( empty($this->idList) )
        {
            if ( $this->emptyListNoRender )
            {
                $this->setVisible(false);
            }

            return;
        }

        $avatars = $this->getAvatarInfo($this->idList);
        
        $event = new OW_Event('bookmarks.is_mark', array(), $avatars);
        OW::getEventManager()->trigger($event);
        
        if ( $event->getData() )
        {
            $avatars = $event->getData();
        }

        if ( $this->viewMoreUrl !== null )
        {
            $this->assign('view_more_array', array('url' => $this->viewMoreUrl, 'title' => OW::getLanguage()->text('base', 'view_more_label')));
        }

        $this->assign('users', $avatars);
        $this->assign('css_class', $this->customCssClass);
    }

    public function getAvatarInfo( $idList )
    {
        return BOL_AvatarService::getInstance()->getDataForUserAvatars($idList);
    }
}