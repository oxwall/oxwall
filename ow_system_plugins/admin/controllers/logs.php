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
 * Logs controller.
 */
class ADMIN_CTRL_Logs extends ADMIN_CTRL_Abstract
{
    const ENTRIES_PER_PAGE = 50;

    /**
     * @var BOL_LogService
     */
    protected $service;

    public function __construct()
    {
        parent::__construct();

        $this->service = BOL_LogService::getInstance();
    }

    /**
     * Display list of all log entries.
     */
    public function index()
    {
        $pageParam = (int) ($_GET['page'] ?? null);
        $typeParam = $_GET['type'] ?? null;
        $keyParam = $_GET['key'] ?? null;
        $searchParam = $_GET['search'] ?? null;

        $page = $pageParam > 0 ? $pageParam - 1 : 0;

        $first = self::ENTRIES_PER_PAGE * $page;

        if ( !empty($typeParam) )
        {
            $entries = $this->service->findByTypePaginated($typeParam, $first, self::ENTRIES_PER_PAGE);
            $totalEntries = $this->service->countByType($typeParam);
        }
        elseif ( !empty($keyParam) )
        {
            $entries = $this->service->findByKeyPaginated($keyParam, $first, self::ENTRIES_PER_PAGE);
            $totalEntries = $this->service->countByKey($keyParam);
        }
        elseif ( !empty($searchParam) )
        {
            $entries = $this->service->findByQueryPaginated($searchParam, $first, self::ENTRIES_PER_PAGE);
            $totalEntries = $this->service->countByQuery($searchParam);
        }
        else
        {
            $entries = $this->service->findAll($first, self::ENTRIES_PER_PAGE);
            $totalEntries = $this->service->countAll();
        }

        $totalPages = floor($totalEntries / self::ENTRIES_PER_PAGE);

        if ( empty($entries) )
        {
            $this->assign('isEmpty', true);
            return;
        }

        $processedEntries = $this->service->processEntries($entries);

        if ( $typeParam || $keyParam || $searchParam )
        {
            $this->assign('isFiltersApplied', true);
            $this->assign('dropFiltersUrl', OW::getRouter()->urlForRoute('admin_developer_tools_logs'));
        }

        $paging = new BASE_CMP_Paging($page + 1, $totalPages, 10);
        $this->assign('paging', $paging->render());

        $this->assign('totalEntriesText', OW::getLanguage()->text(
            'base',
            $totalEntries > 1 ? 'admin_logs_total_entries_mul' : 'admin_logs_total_entries_sing',
            array(
                'number' => $totalEntries
            )
        ));

        $this->assign('entriesPerPageText', OW::getLanguage()->text(
            'base',
            self::ENTRIES_PER_PAGE > 1 ? 'admin_logs_total_entries_mul' : 'admin_logs_total_entries_sing',
            array(
                'number' => self::ENTRIES_PER_PAGE
            )
        ));

        $this->assign('currentSearchValue', $searchParam ?? '');
        $this->assign('entries', $processedEntries);
    }

    /**
     * Display full information about a particular entry.
     *
     * @param array $params
     */
    public function entry( $params )
    {
        $entryId = (int) $params['id'];

        if ( $entryId <= 0 )
        {
            $this->redirect(OW::getRouter()->urlForRoute('admin_developer_tools_logs'));
            return;
        }

        $entry = $this->service->findById($entryId);

        if ( !$entry )
        {
            $this->redirect(OW::getRouter()->urlForRoute('admin_developer_tools_logs'));
            return;
        }

        // Try to render the log entry using a custom view.
        $logEntryCustomViewEvent = new OW_Event(OW_EventManager::ON_LOG_ENTRY_CUSTOM_VIEW, array(
            'entry' => $entry
        ));

        OW::getEventManager()->trigger($logEntryCustomViewEvent);

        $customView = $logEntryCustomViewEvent->getData();

        if ( !empty($customView) && is_string($customView) )
        {
            // If there is a custom view for this entry, render it.
            $this->assign('customView', $customView);
        }
        else
        {
            // Otherwise render message using the standard view.
            list($entryProcessed) = $this->service->processEntries([ $entry ]);
            $this->assign('entry', $entryProcessed);
        }
    }
}
