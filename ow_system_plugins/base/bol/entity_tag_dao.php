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
 * Data Access Object for `base_entity_tag` table.
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_EntityTagDao extends OW_BaseDao
{
    const ENTITY_ID = 'entityId';
    const ENTITY_TYPE = 'entityType';
    const TAG_ID = 'tagId';
    const ACTIVE = 'active';

    /**
     * Constructor.
     */
    protected function __construct()
    {
        parent::__construct();
    }
    /**
     * Singleton instance.
     *
     * @var BOL_EntityTagDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_EntityTagDao
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
     * @see OW_BaseDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return 'BOL_EntityTag';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_entity_tag';
    }

    /**
     * Deletes entity_tag items for provided params.
     *
     * @param integer $entityId
     * @param string $entityType
     */
    public function deleteItemsForEntityItem( $entityId, $entityType )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::ENTITY_ID, $entityId);
        $example->andFieldEqual(self::ENTITY_TYPE, $entityType);

        $this->deleteByExample($example);
    }

    /**
     * Deletes entity_tag items for provided params.
     *
     * @param integer $entityId
     * @param string $entityType
     * @param integer $tagId
     */
    public function deleteEntityTagItem( $entityId, $entityType, $tagId )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::ENTITY_ID, $entityId);
        $example->andFieldEqual(self::ENTITY_TYPE, $entityType);
        $example->andFieldEqual(self::TAG_ID, $tagId);

        $this->deleteByExample($example);
    }

    /**
     * Returns entity_tag items for provided params.
     *
     * @param integer $entityId
     * @param string $entityType
     * @return array
     */
    public function findEntityTagItems( $entityId, $entityType )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::ENTITY_ID, $entityId);
        $example->andFieldEqual(self::ENTITY_TYPE, $entityType);

        return $this->findListByExample($example);
    }

    public function updateEntityStatus( $entityType, $entityId, $status )
    {
        $query = "UPDATE `" . $this->getTableName() . "` SET `" . self::ACTIVE . "` = :status
                WHERE `" . self::ENTITY_TYPE . "` = :entityType AND `" . self::ENTITY_ID . "` = :entityId";

        $this->dbo->query($query, array('status' => $status, 'entityType' => $entityType, 'entityId' => $entityId));
    }

    public function findEntityListByTag( $entityType, $tag, $first, $count )
    {
        $query = "SELECT `et`.`" . self::ENTITY_ID . "` AS `id` from `" . BOL_TagDao::getInstance()->getTableName() . "` AS `t` INNER JOIN `" . $this->getTableName() . "` AS `et` ON(`et`.`" . self::TAG_ID . "`=`t`.`id`)
                WHERE `t`.`" . BOL_TagDao::LABEL . "` = :tag AND `et`.`" . self::ENTITY_TYPE . "` = :entityType AND `et`.`" . self::ACTIVE . "` = 1
                ORDER BY `et`.`entityId` DESC
                LIMIT :first, :count";

        return $this->dbo->queryForColumnList($query, array('tag' => $tag, 'entityType' => $entityType, 'first' => (int) $first, 'count' => (int) $count));
    }

    public function findEntityCountByTag( $entityType, $tag )
    {
        $query = "SELECT COUNT(*) from `" . BOL_TagDao::getInstance()->getTableName() . "` AS `t` INNER JOIN `" . $this->getTableName() . "` AS `et` ON(`et`.`" . self::TAG_ID . "`=`t`.`id`)
                where `t`.`" . BOL_TagDao::LABEL . "` = :tag AND `et`.`" . self::ENTITY_TYPE . "` = :entityType AND `et`.`" . self::ACTIVE . "` = 1";

        return (int) $this->dbo->queryForColumn($query, array('tag' => $tag, 'entityType' => $entityType));
    }

    public function deleteByEntityType( $entityType )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::ENTITY_TYPE, trim($entityType));

        $this->deleteByExample($example);
    }

    public function findEntityIdListByTagIdList( array $idList )
    {
        if ( empty($idList) )
        {
            return array();
        }

        $sql = 'SELECT `' . self::ENTITY_ID . '`
            FROM `' . $this->getTableName() . '`
            WHERE `' . self::TAG_ID . '` IN (' . $this->dbo->mergeInClause($idList) . ')';

        return $this->dbo->queryForColumnList($sql);
    }
}