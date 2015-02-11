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
 * Console invitations section items component
 *
 * @author Egor Bulgakov <egor.bulgakov@gmail.com>
 * @package ow.ow_system_plugins.base.mobile.components
 * @since 1.6.0
 */
class BASE_MCMP_ConsoleInvitations extends OW_MobileComponent
{
    /**
     * Constructor.
     */
    public function __construct(  $limit, $exclude = null )
    {
        parent::__construct();

        $service = BOL_InvitationService::getInstance();
        $userId = OW::getUser()->getId();
        $invitations = $service->findInvitationList($userId, time(), $exclude, $limit);
        $items = $this->prepareData($invitations);
        $this->assign('items', $items);

        $invitationIdList = array();
        foreach ( $items as $id => $item )
        {
            $invitationIdList[] = $id;
        }

        // Mark as viewed
        $service->markViewedByUserId($userId);

        $exclude = is_array($exclude) ? array_merge($exclude, $invitationIdList) : $invitationIdList;
        $loadMore = (bool) $service->findInvitationCount($userId, null, $exclude);
        if ( !$loadMore )
        {
            $script = "OWM.trigger('mobile.console_hide_invitations_load_more', {});";
            OW::getDocument()->addOnloadScript($script);
        }
    }

    public static function prepareData( $invitations )
    {
        $avatars = array();
        $router = OW::getRouter();
        foreach ( $invitations as $invitation )
        {
            $data = json_decode($invitation->data, true);
            $avatars[$invitation->id] = array(
                'src' => $data['avatar']['src'],
                'title' => $data['avatar']['title'],
                'url' => isset($data['avatar']['urlInfo']) ?
                    $router->urlForRoute($data['avatar']['urlInfo']['routeName'], $data['avatar']['urlInfo']['vars']) : null
            );
        }

        $items = array();

        foreach ( $invitations as $invitation )
        {
            // backward compatibility: row will be not clickable
            $disabled = true;

            /** @var $invitation BOL_Invitation  */
            $item = $invitation->getData();

            $itemEvent = new OW_Event('mobile.invitations.on_item_render', array(
                'entityType' => $invitation->entityType,
                'entityId' => $invitation->entityId,
                'pluginKey' => $invitation->pluginKey,
                'userId' => $invitation->userId,
                'data' => $item
            ));

            OW::getEventManager()->trigger($itemEvent);
            $eData = $itemEvent->getData();

            if ( $eData )
            {
                if ( !empty($eData) )
                {
                    $item = $eData;
                    $disabled = false;
                }
            }

            $item['avatar'] = $avatars[$invitation->id];
            $item['entityId'] = $invitation->entityId;

            if ( !empty($item['string']) && is_array($item['string']) )
            {
                $key = explode('+', $item['string']['key']);
                $vars = empty($item['string']['vars']) ? array() : $item['string']['vars'];
                $item['string'] = OW::getLanguage()->text($key[0], $key[1], $vars);
                if ( $disabled )
                {
                    $item['string'] = strip_tags($item['string']);
                }
            }

            if ( !empty($item['contentImage']) )
            {
                $item['contentImage'] = is_string($item['contentImage'])
                    ? array( 'src' => $item['contentImage'] )
                    : $item['contentImage'];
            }
            else
            {
                $item['contentImage'] = null;
            }

            $item['viewed'] = (bool) $invitation->viewed;
            $item['disabled'] = $disabled;
            $items[$invitation->id] = $item;
        }

        return $items;
    }
}