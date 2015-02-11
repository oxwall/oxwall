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
 * @author Madumarov Sardar <madumarov@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
final class BOL_VoteService
{
    /**
     * @var BOL_VoteDao
     */
    private $voteDao;
    /**
     * @var BOL_VoteService
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_VoteService
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * Constructor.
     *
     */
    private function __construct()
    {
        $this->voteDao = BOL_VoteDao::getInstance();
    }

    /**
     * Saves and updates vote item.
     *
     * @param BOL_Vote $voteItem
     */
    public function saveVote( BOL_Vote $voteItem )
    {
        $this->voteDao->save($voteItem);
    }

    /**
     * Returns counted votes sum.
     *
     * @param integer $entityId
     * @param string $entityType
     * @return integer
     */
    public function findTotalVotesResult( $entityId, $entityType )
    {
        return $this->voteDao->findTotalVote($entityId, $entityType);
    }

    /**
     * Returns counted votes sum for items list.
     *
     * @param array $entityIdList
     * @param string $entityType
     * @return array<integer>
     */
    public function findTotalVotesResultForList( $entityIdList, $entityType )
    {
        if ( empty($entityIdList) )
        {
            return array();
        }

        $arr = $this->voteDao->findTotalVoteForList($entityIdList, $entityType);

        $resultArray = array();

        foreach ( $arr as $value )
        {
            $resultArray[$value['id']] = $value;
        }

        return $resultArray;
    }

    /**
     * Returns vote item for user.
     *
     * @param integer $entityId
     * @param string $entityType
     * @param integer $userId
     * @return BOL_Vote
     */
    public function findUserVote( $entityId, $entityType, $userId )
    {
        return $this->voteDao->findUserVote($entityId, $entityType, $userId);
    }

    /**
     * Returns vote item for user and items list.
     *
     * @param array $entityIds
     * @param string $entityType
     * @param integer $userId
     * @return array
     */
    public function findUserVoteForList( $entityIds, $entityType, $userId )
    {
        $list = $this->voteDao->findUserVoteForList($entityIds, $entityType, $userId);
        $res = array();
        foreach ( $list as $item )
        {
            if ( $item->vote > 0 )
            {
                $item->vote = "+1";
            }
            $res[$item->getEntityId()] = $item;
        }

        return $res;
    }

    /**
     * Deletes all votes for entity item.
     *
     * @param integer $entityId
     * @param string $entityType
     */
    public function deleteEntityItemVotes( $entityId, $entityType )
    {
        $this->voteDao->deleteEntityItemVotes($entityId, $entityType);
    }

    public function findMostVotedEntityList( $entityType, $first, $count )
    {
        $arr = $this->voteDao->findMostVotedEntityList($entityType, $first, $count);

        $resultArray = array();

        foreach ( $arr as $value )
        {
            $resultArray[$value['id']] = $value;
        }

        return $resultArray;
    }

    public function findMostVotedEntityCount( $entityType )
    {
        return $this->voteDao->findMostVotedEntityCount($entityType);
    }

    public function setEntityStatus( $entityType, $entityId, $status = true )
    {
        $status = $status ? 1 : 0;

        $this->voteDao->updateEntityStatus($entityType, $entityId, $status);
    }

    public function deleteUserVotes( $userId )
    {
        $this->voteDao->deleteUserVotes($userId);
    }

    public function delete( $vote )
    {
        $this->voteDao->delete($vote);
    }
    
    public function updateEntityItemStatus( $entityType, $entityId, $status = true )
    {
        $this->voteDao->updateEntityStatus($entityType, (int)$entityId, (int)$status);
    }

    /**
     * @param string $entityType
     */
    public function deleteByEntityType( $entityType )
    {
        $this->voteDao->deleteByEntityType($entityType);
    }
}