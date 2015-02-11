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
 * Invitation Service
 *
 * @author Sergey Kambalin <greyexpert@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_InvitationService
{
    /**
     * Class instance
     *
     * @var BOL_InvitationService
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return BOL_InvitationService
     */
    public static function getInstance()
    {
        if ( !isset(self::$classInstance) )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     *
     * @var BOL_InvitationDao
     */
    private $invitationDao;

    private function __construct()
    {
        $this->invitationDao = BOL_InvitationDao::getInstance();
    }

    public function findInvitationList( $userId, $beforeStamp, $ignoreIds, $count )
    {
        return $this->invitationDao->findInvitationList($userId, $beforeStamp, $ignoreIds, $count);
    }

    public function findNewInvitationList( $userId, $afterStamp )
    {
        return $this->invitationDao->findNewInvitationList($userId, $afterStamp);
    }

    public function findInvitationListForSend( $userIdList )
    {
        return $this->invitationDao->findInvitationListForSend($userIdList);
    }

    public function findInvitationCount( $userId, $viewed = null, $exclude = null )
    {
        return $this->invitationDao->findInvitationCount($userId, $viewed, $exclude);
    }

    public function findEntityInvitationList( $entityType, $entityId, $offset = 0, $count = null)
    {
        return $this->invitationDao->findEntityInvitationList($entityType, $entityId, $offset, $count);
    }

    public function findEntityInvitationCount( $entityType, $entityId )
    {
        return $this->invitationDao->findEntityInvitationCount($entityType, $entityId);
    }

    public function saveInvitation( BOL_Invitation $invitation )
    {
        $this->invitationDao->saveInvitation($invitation);
    }

    /**
     *
     * @param string $entityType
     * @param int $entityId
     * @param int $userId
     * @return BOL_Invitation
     */
    public function findInvitation( $entityType, $entityId, $userId )
    {
        return $this->invitationDao->findInvitation($entityType, $entityId, $userId);
    }

    public function markViewedByIds( $idList, $viewed = true )
    {
        $this->invitationDao->markViewedByIds($idList, $viewed);
    }

    public function markViewedByUserId( $userId, $viewed = true )
    {
        $this->invitationDao->markViewedByUserId($userId, $viewed);
    }

    public function markSentByIds( $idList, $sent = true )
    {
        $this->invitationDao->markSentByIds($idList, $sent);
    }

    public function deleteInvitation( $entityType, $entityId, $userId )
    {
        $this->invitationDao->deleteInvitation($entityType, $entityId, $userId);
    }

    public function deleteInvitationByEntity( $entityType, $entityId )
    {
        $this->invitationDao->deleteInvitationByEntity($entityType, $entityId);
    }

    public function deleteInvitationByPluginKey( $pluginKey )
    {
        $this->invitationDao->deleteInvitationByPluginKey($pluginKey);
    }

    public function setInvitationStatusByPluginKey( $pluginKey, $status )
    {
        $this->invitationDao->setInvitationStatusByPluginKey($pluginKey, $status);
    }
}