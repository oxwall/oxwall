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
 * Data Access Object for `base_log` table.
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_LogDao extends OW_BaseDao
{
    const TYPE = 'type';
    const KEY = 'key';
    const TIME_STAMP = 'timeStamp';
    const MESSAGE = 'message';

    /**
     * Singleton instance.
     *
     * @var BOL_LogDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_LogDao
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
    protected function __construct()
    {
        parent::__construct();
    }

    /**
     * @see OW_BaseDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return 'BOL_Log';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_log';
    }

    /**
     * @param array $entries<BOL_Log>
     */
    public function addEntries( array $entries )
    {
        $this->dbo->batchInsertOrUpdateObjectList($this->getTableName(), $entries);
    }

    /**
     * @param string $type
     */
    public function deleteByType( $type )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::TYPE, trim($type));

        $this->deleteByExample($example);
    }

    /**
     * @param string $key
     */
    public function deleteByKey( $key )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::KEY, trim($key));

        $this->deleteByExample($example);
    }

    public function findAllPaginated( $first, $count )
    {
        $example = new OW_Example();
        $example->setOrder(self::TIME_STAMP . ' DESC');
        $example->setLimitClause($first, $count);

        return $this->findListByExample($example);
    }

    public function findByTypePaginated( $type, $first, $count )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::TYPE, $type);
        $example->setOrder(self::TIME_STAMP . ' DESC');
        $example->setLimitClause($first, $count);

        return $this->findListByExample($example);
    }

    public function findByKeyPaginated( $key, $first, $count )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::KEY, $key);
        $example->setOrder(self::TIME_STAMP . ' DESC');
        $example->setLimitClause($first, $count);

        return $this->findListByExample($example);
    }

    public function findByQueryPaginated($query, $first, $count )
    {
        $sql = "
            SELECT `id`, `message`, `type`, `key`, `timeStamp`
            FROM `" . $this->getTableName() . "`
            WHERE `message` LIKE :query OR `key` LIKE :query OR `type` LIKE :query
            ORDER BY `timeStamp` DESC
            LIMIT :first, :count
        ";

        $query = '%' . $query . '%';

        return $this->dbo->queryForObjectList($sql, BOL_Log::class, array(
            'query' => $query,
            'first' => $first,
            'count' => $count
        ));
    }

    public function findByTypeAndKey( $type, $key )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::TYPE, trim($type));
        $example->andFieldEqual(self::KEY, trim($key));

        return $this->findObjectByExample($example);
    }

    public function findByType( $type )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::TYPE, trim($type));

        return $this->findListByExample($example);
    }

    public function countBySearchQuery( $query )
    {
        $sql = "
            SELECT COUNT(`id`) AS `count`
            FROM `" . $this->getTableName() . "`
            WHERE `message` LIKE :query OR `key` LIKE :query OR `type` LIKE :query
        ";

        $query = '%' . $query . '%';

        $result = $this->dbo->queryForRow($sql, array(
            'query' => $query
        ));

        if ( !is_array($result) )
        {
            return 0;
        }

        return (int) $result['count'];
    }

    public function countByType( $type )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::TYPE, $type);

        return $this->countByExample($example);
    }

    public function countByKey( $key )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::KEY, $key);

        return $this->countByExample($example);
    }
}