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
 *  Comment Service.
 * 
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
final class BOL_LogService
{
    /**
     * @var BOL_LogDao
     */
    private $logDao;

    /**
     * Singleton instance.
     *
     * @var BOL_CommentDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_CommentDao
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
     */
    private function __construct()
    {
        $this->logDao = BOL_LogDao::getInstance();
    }

    /**
     * Adds entries.
     *
     * @param array $entries
     */
    public function addEntries( array $entries )
    {
        $objectList = array();

        if ( !empty($entries) )
        {
            foreach ( $entries as $entry )
            {
                $obj = new BOL_Log();
                $obj->setKey($entry[OW_Log::KEY]);
                $obj->setType($entry[OW_Log::TYPE]);
                $obj->setMessage($entry[OW_Log::MESSAGE]);
                $obj->setTimeStamp($entry[OW_Log::TIME_STAMP]);

                $objectList[] = $obj;
            }
        }

        $this->logDao->addEntries($objectList);
    }

    /**
     * Returns total number of saved log entries.
     *
     * @return int
     */
    public function countAll()
    {
        return (int) $this->logDao->countAll();
    }

    /**
     * Returns total number of saved log entries of the given `$type`.
     *
     * @param string $type
     * @return int
     */
    public function countByType( $type )
    {
        return (int) $this->logDao->countByType($type);
    }

    /**
     * Returns total number of saved log entries with the given `$key`.
     *
     * @param string $key
     * @return int
     */
    public function countByKey( $key )
    {
        return (int) $this->logDao->countByKey($key);
    }

    /**
     * Find log entry by its id.
     *
     * @param int $id
     * @return BOL_Log|null
     *
     * @noinspection PhpReturnDocTypeMismatchInspection
     */
    public function findById($id)
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->logDao->findById($id);
    }

    /**
     * Skips `$first` and returns at most `$count` entries.
     *
     * @param int $first Number of entries to skip
     * @param int $count Max. number of entries to return
     * @return array<BOL_Log>
     */
    public function findAll( $first, $count )
    {
        return $this->logDao->findAllPaginated($first, $count);
    }

    /**
     * Returns log entry entity objects representing entries of the given `$type` Skips `$first` and returns at most
     * `$count` entries.
     *
     * @param string $type Returned entries log type
     * @param int $first Number of entries to skip
     * @param int $count Max. number of entries to return
     * @return array<BOL_Log>
     */
    public function findByTypePaginated( $type, $first, $count )
    {
        return $this->logDao->findByTypePaginated($type, $first, $count);
    }

    /**
     * Returns log entry entity objects representing with the given `$key` Skips `$first` and returns at most `$count`
     * entries.
     *
     * @param string $key Returned entries key
     * @param int $first Number of entries to skip
     * @param int $count Max. number of entries to return
     * @return array<BOL_Log>
     */
    public function findByKeyPaginated( $key, $first, $count )
    {
        return $this->logDao->findByKeyPaginated($key, $first, $count);
    }

    /**
     * Returns log list for provided type.
     *
     * @param string $type
     * @return array<BOL_Log>
     */
    public function findByType( $type )
    {
        return $this->logDao->findByType($type);
    }

    /**
     * Returns log item for provided type and key.
     *
     * @param string $type
     * @param string $key
     * @return BOL_Log
     */
    public function findByTypeAndKey( $type, $key )
    {
        return $this->logDao->findByTypeAndKey($type, $key);
    }

    /**
     * Transform an array of log entry entity objects.
     *
     * @param array<BOL_Log> $entries
     * @return array
     */
    public function processEntries( $entries )
    {
        return array_map(function ( $entry ) {
            $filterByTypeQuery = http_build_query(array('type' => $entry->getType()), '', '&', PHP_QUERY_RFC3986);
            $filterByKeyQuery = http_build_query(array('key' => $entry->getKey()), '', '&', PHP_QUERY_RFC3986);

            $viewUrl = OW::getRouter()->urlForRoute('admin_developer_tools_logs_entry', array('id' => $entry->getId()));
            $filterByTypeUrl = OW::getRouter()->urlForRoute('admin_developer_tools_logs') . '?' . $filterByTypeQuery;
            $filterByKeyUrl = OW::getRouter()->urlForRoute('admin_developer_tools_logs') . '?' . $filterByKeyQuery;

            $sanitizedMessage = htmlspecialchars($entry->getMessage());

            $result = array(
                'id' => $entry->getId(),
                'type' => $entry->getType(),
                'key' => $entry->getKey(),
                'messageFull' => htmlspecialchars($sanitizedMessage),
                'timestamp' => $entry->getTimeStamp(),
                'timestampReadable' => UTIL_DateTime::formatDate($entry->getTimeStamp()),
                'timeString' => date('m/d/Y h:i:s', $entry->getTimeStamp()),
                'viewUrl' => $viewUrl,
                'filterByTypeUrl' => $filterByTypeUrl,
                'filterByKeyUrl' => $filterByKeyUrl
            );

            $processLogMessageEvent = new OW_Event(OW_EventManager::ON_PROCESS_LOG_ENTRY_MESSAGE, array(
                'entry' => $entry
            ));

            OW::getEventManager()->trigger($processLogMessageEvent);

            $processedLogMessage = $processLogMessageEvent->getData();

            $result['message'] = !empty($processedLogMessage) && is_string($processedLogMessage)
                ? $processedLogMessage
                : (
                    mb_strlen($sanitizedMessage) > 64
                        ? mb_substr($sanitizedMessage, 0, 61) . '...'
                        : $sanitizedMessage
                );

            return $result;
        }, $entries);
    }
}