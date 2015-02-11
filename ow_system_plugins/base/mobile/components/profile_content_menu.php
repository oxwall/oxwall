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
 * @author Egor Bulgakov <egor.bulgakov@gmail.com>
 * @package ow_system_plugins.base.mobile.components
 * @since 1.6.0
 */
class BASE_MCMP_ProfileContentMenu extends OW_MobileComponent
{
    const EVENT_NAME = 'base.mobile.add_profile_content_menu';
    const DATA_KEY_LABEL = 'label';
    const DATA_KEY_THUMB = 'thumb';
    const DATA_KEY_LINK_ID = 'id';
    const DATA_KEY_LINK_CLASS = 'linkClass';
    const DATA_KEY_LINK_HREF = 'href';
    const DATA_KEY_LINK_ORDER = 'order';
    const DATA_KEY_LINK_ATTRIBUTES = 'attributes';

    /**
     *
     * @var BOL_User
     */
    protected $user;

    /**
     * Constructor.
     */
    public function __construct( BOL_User $user )
    {
        parent::__construct();

        $this->user = $user;
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $event = new BASE_CLASS_EventCollector(self::EVENT_NAME, array('userId' => $this->user->id));

        OW::getEventManager()->trigger($event);

        $addedData = $event->getData();

        if ( empty($addedData) )
        {
            $this->setVisible(false);

            return;
        }

        $this->initMenu($addedData);
    }

    public function initMenu( $items )
    {
        $tplActions = array();

        foreach ( $items as $item  )
        {
            $action = &$tplActions[];

            $action['label'] = $item[self::DATA_KEY_LABEL];
            $action['order'] = count($tplActions);

            $attrs = isset($item[self::DATA_KEY_LINK_ATTRIBUTES]) && is_array($item[self::DATA_KEY_LINK_ATTRIBUTES])
                ? $item[self::DATA_KEY_LINK_ATTRIBUTES]
                : array();

            $attrs['href'] = isset($item[self::DATA_KEY_LINK_HREF]) ? $item[self::DATA_KEY_LINK_HREF] : 'javascript://';

            if ( isset($item[self::DATA_KEY_LINK_ID]) )
            {
                $attrs['id'] = $item[self::DATA_KEY_LINK_ID];
            }

            if ( isset($item[self::DATA_KEY_LINK_CLASS]) )
            {
                $action['class'] = $item[self::DATA_KEY_LINK_CLASS];
            }

            if ( isset($item[self::DATA_KEY_LINK_ORDER]) )
            {
                $action['order'] = $item[self::DATA_KEY_LINK_ORDER];
            }

            if ( isset($item[self::DATA_KEY_THUMB]) )
            {
                $action['img'] = $item[self::DATA_KEY_THUMB];
            }

            $_attrs = array();
            foreach ( $attrs as $name => $value )
            {
                $_attrs[] = $name . '="' . $value . '"';
            }

            $action['attrs'] = implode(' ', $_attrs);
        }

        $this->assign('actions', $tplActions);
    }
}