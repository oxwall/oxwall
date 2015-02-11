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
 * @package ow.ow_system_plugins.base.mobile.classes
 * @since 1.6.0
 */
class BASE_MCLASS_ConsoleEventHandler
{
    /**
     * Class instance
     *
     * @var BASE_MCLASS_ConsoleEventHandler
     */
    private static $classInstance;

    const CONSOLE_NOTIFICATIONS_PAGE_KEY = 'notifications';
    const CONSOLE_PROFILE_PAGE_KEY = 'profile';
    const CONSOLE_INVITATIONS_SECTION_KEY = 'invitations';

    /**
     * Returns class instance
     *
     * @return BASE_MCLASS_ConsoleEventHandler
     */
    public static function getInstance()
    {
        if ( !isset(self::$classInstance) )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function addNotificationsPage( BASE_CLASS_EventCollector $event )
    {
        $event->add(array(
            'key' => self::CONSOLE_NOTIFICATIONS_PAGE_KEY,
            'cmpClass' => 'BASE_MCMP_ConsoleNotificationsPage',
            'order' => 1,
            'counter' => MBOL_ConsoleService::getInstance()->countPageNewItems('notifications')
        ));
    }

    public function addProfilePage( BASE_CLASS_EventCollector $event )
    {
        $event->add(array(
            'key' => self::CONSOLE_PROFILE_PAGE_KEY,
            'cmpClass' => 'BASE_MCMP_ConsoleProfilePage',
            'order' => 2
        ));
    }

    public function addInvitationsSection( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();

        if ( $params['page'] == self::CONSOLE_NOTIFICATIONS_PAGE_KEY )
        {
            $event->add(array(
                'key' => self::CONSOLE_INVITATIONS_SECTION_KEY,
                'component' => new BASE_MCMP_ConsoleInvitationsSection(),
                'order' => 2
            ));
        }
    }

    public function countNewInvitations( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();

        if ( $params['page'] == self::CONSOLE_NOTIFICATIONS_PAGE_KEY )
        {
            $service = BOL_InvitationService::getInstance();
            $event->add(
                array(self::CONSOLE_INVITATIONS_SECTION_KEY => $service->findInvitationCount(OW::getUser()->getId(), false))
            );
        }
    }

    public function getNewInvitations( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();

        if ( $params['page'] == self::CONSOLE_NOTIFICATIONS_PAGE_KEY )
        {
            $event->add(array(
                self::CONSOLE_INVITATIONS_SECTION_KEY => new BASE_MCMP_ConsoleNewInvitations($params['timestamp'])
            ));
        }
    }

    public function ping( OW_Event $e )
    {
        $params = $e->getParams();
        $state = $params['state'];
        $timestamp = $params['timestamp'];

        $service = MBOL_ConsoleService::getInstance();
        $count = $service->countNewItems();

        $response = array('count' => $count);
        if ( $state == "open" && $count )
        {
            $response['new_items'] = $service->getNewItems(self::CONSOLE_NOTIFICATIONS_PAGE_KEY, $timestamp);
        }
        $response['timestamp'] = time();

        $e->setData($response);
    }

    public function init()
    {
        $em = OW::getEventManager();
        $em->bind(
            MBOL_ConsoleService::EVENT_COLLECT_CONSOLE_PAGES,
            array($this, 'addNotificationsPage')
        );
        $em->bind(
            MBOL_ConsoleService::EVENT_COLLECT_CONSOLE_PAGES,
            array($this, 'addProfilePage')
        );

        $em->bind(
            MBOL_ConsoleService::EVENT_COLLECT_CONSOLE_PAGE_SECTIONS,
            array($this, 'addInvitationsSection')
        );

        $em->bind(
            MBOL_ConsoleService::EVENT_COUNT_CONSOLE_PAGE_NEW_ITEMS,
            array($this, 'countNewInvitations')
        );

        $em->bind(
            MBOL_ConsoleService::EVENT_COLLECT_CONSOLE_PAGE_NEW_ITEMS,
            array($this, 'getNewInvitations')
        );

        $em->bind(
            BASE_CTRL_Ping::PING_EVENT . '.mobileConsole',
            array($this, 'ping')
        );
    }
}