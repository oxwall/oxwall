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
 * Search service.
 *
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_SearchService
{
    const USER_LIST_SIZE = 500;

    const SEARCH_RESULT_ID_VARIABLE = "OW_SEARCH_RESULT_ID";

    /**
     * @var BOL_SearchDao
     */
    private $searchDao;
    /**
     * @var BOL_SearchResultDao
     */
    private $searchResultDao;
    /**
     * Singleton instance.
     *
     * @var BOL_SearchService
     */
    private static $classInstance;

    /**
     * Constructor.
     *
     */
    private function __construct()
    {
        $this->searchDao = BOL_SearchDao::getInstance();
        $this->searchResultDao = BOL_SearchResultDao::getInstance();
    }

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_SearchService
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
            self::$classInstance = new self();

        return self::$classInstance;
    }

    /**
     * Save search Result. Returns search id.
     *
     * @param array $idList
     * @return int
     */
    public function saveSearchResult( array $idList )
    {
        $search = new BOL_Search();
        $search->timeStamp = time();

        $this->searchDao->save($search);

        $this->searchResultDao->saveSearchResult($search->id, $idList);

        $event = new OW_Event('base.after_save_search_result', array('searchDto' => $search, 'userIdList' => $idList), array());
        OW::getEventManager()->trigger($event);

        return $search->id;
    }

    /**
     * Return user id list
     *
     * @param int $listId
     * @param int $first
     * @param int $count
     * @return array
     */
    public function getUserIdList( $listId, $first, $count, $excludeList = array() )
    {
        return $this->searchResultDao->getUserIdList($listId, $first, $count, $excludeList);
    }

    public function countSearchResultItem( $listId )
    {
        return $this->searchResultDao->countSearchResultItem($listId);
    }

    public function deleteExpireSearchResult()
    {

        $list = $this->searchDao->findExpireSearchId();

        if ( !empty($list) )
        {
            $this->searchResultDao->deleteSearchResultItems($list);
            $this->searchDao->deleteByIdList($list);
        }
    }
}