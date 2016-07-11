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
 * Data Access Object for `base_comment` table.
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_CommentDao extends OW_BaseDao
{
    const USER_ID = 'userId';
    const COMMENT_ENTITY_ID = 'commentEntityId';
    const MESSAGE = 'message';
    const CREATE_STAMP = 'createStamp';

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
        return 'BOL_Comment';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_comment';
    }

    /**
     * Finds comment list for provided entity type and entity id.
     *
     * @param string $entityType
     * @param integer $entityId
     * @param integer $first
     * @param integer $count
     * @return array
     */
    public function findCommentList( $entityType, $entityId, $first, $count )
    {
        $query = "SELECT `c`.* FROM `" . $this->getTableName() . "` AS `c`
			LEFT JOIN `" . BOL_CommentEntityDao::getInstance()->getTableName() . "` AS `ce` ON ( `c`.`" . self::COMMENT_ENTITY_ID . "` = `ce`.`id` )
			WHERE `ce`.`" . BOL_CommentEntityDao::ENTITY_TYPE . "` = :entityType AND `ce`.`" . BOL_CommentEntityDao::ENTITY_ID . "` = :entityId
			ORDER BY `" . self::CREATE_STAMP . "` DESC
			LIMIT :first, :count";

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array('entityType' => $entityType, 'entityId' => $entityId, 'first' => $first, 'count' => $count));
    }

    /**
     * Finds full comment list for provided entity type and entity id.
     *
     * @param string $entityType
     * @param integer $entityId
     * @return array<BOL_Comment>
     */
    public function findFullCommentList( $entityType, $entityId )
    {
        $query = "SELECT `c`.* FROM `" . $this->getTableName() . "` AS `c`
			LEFT JOIN `" . BOL_CommentEntityDao::getInstance()->getTableName() . "` AS `ce` ON ( `c`.`" . self::COMMENT_ENTITY_ID . "` = `ce`.`id` )
			WHERE `ce`.`" . BOL_CommentEntityDao::ENTITY_TYPE . "` = :entityType AND `ce`.`" . BOL_CommentEntityDao::ENTITY_ID . "` = :entityId
			ORDER BY `" . self::CREATE_STAMP . "`";

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array('entityType' => $entityType, 'entityId' => $entityId));
    }

    /**
     * Returns comments count for provided entity type and entity id.
     *
     * @param string $entityType
     * @param integer $entityId
     * @return integer
     */
    public function findCommentCount( $entityType, $entityId )
    {
        $query = "SELECT COUNT(*) FROM `" . $this->getTableName() . "` AS `c`
			LEFT JOIN `" . BOL_CommentEntityDao::getInstance()->getTableName() . "` AS `ce` ON ( `c`.`" . self::COMMENT_ENTITY_ID . "` = `ce`.`id` )
			WHERE `ce`.`" . BOL_CommentEntityDao::ENTITY_TYPE . "` = :entityType AND `ce`.`" . BOL_CommentEntityDao::ENTITY_ID . "` = :entityId
			";

        return (int) $this->dbo->queryForColumn($query, array('entityType' => $entityType, 'entityId' => $entityId));
    }

    public function findMostCommentedEntityList( $entityType, $first, $count )
    {
        $queryParts = BOL_ContentService::getInstance()->getQueryFilter(array(
            BASE_CLASS_QueryBuilderEvent::TABLE_USER => 'c',
            BASE_CLASS_QueryBuilderEvent::TABLE_CONTENT => 'c',
            'comment_entity' => 'ce'
        ), array(
            BASE_CLASS_QueryBuilderEvent::FIELD_USER_ID => 'userId',
            BASE_CLASS_QueryBuilderEvent::FIELD_CONTENT_ID => 'id'
        ), array(
            BASE_CLASS_QueryBuilderEvent::OPTION_METHOD => __METHOD__,
            BASE_CLASS_QueryBuilderEvent::OPTION_TYPE => $entityType
        ));

        $query = 'SELECT `ce`.`entityId` AS `id`, COUNT(*) AS `commentCount`
            FROM `' . $this->getTableName() . '` AS `c`
			    LEFT JOIN `' . BOL_CommentEntityDao::getInstance()->getTableName() . '` AS `ce` ON (`c`.`' . self::COMMENT_ENTITY_ID . '` = `ce`.`id`)
			    ' . $queryParts['join'] . '
			WHERE `ce`.`' . BOL_CommentEntityDao::ENTITY_TYPE . '` = :entityType AND `ce`.`' . BOL_CommentEntityDao::ACTIVE . '` = 1 AND ' . $queryParts['where'] . '
			GROUP BY `ce`.`' . BOL_CommentEntityDao::ENTITY_ID . '`
			ORDER BY `commentCount` DESC, `id` DESC
			LIMIT :first, :count';
        $boundParams = array_merge(array('entityType' => $entityType, 'first' => $first, 'count' => $count), $queryParts['params']);
        return $this->dbo->queryForList($query, $boundParams);
    }

    public function findCommentCountForEntityList( $entityType, $idList )
    {
        if ( empty($idList) )
        {
            return array();
        }

        $query = "SELECT `ce`.`entityId` AS `id`, COUNT(*) AS `commentCount` FROM `" . $this->getTableName() . "` AS `c`
			INNER JOIN `" . BOL_CommentEntityDao::getInstance()->getTableName() . "` AS `ce`
				ON ( `c`.`" . self::COMMENT_ENTITY_ID . "` = `ce`.`id` )
			WHERE `ce`.`" . BOL_CommentEntityDao::ENTITY_TYPE . "` = :entityType AND `ce`.`" . BOL_CommentEntityDao::ENTITY_ID . "` IN  ( " . $this->dbo->mergeInClause($idList) . " )
			GROUP BY `" . BOL_CommentEntityDao::ENTITY_ID . "`";

        return $this->dbo->queryForList($query, array('entityType' => $entityType));
    }

    public function deleteByCommentEntityId( $id )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::COMMENT_ENTITY_ID, $id);

        $this->deleteByExample($example);
    }

    public function deleteByUserId( $userId )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::USER_ID, (int) $userId);

        $this->deleteByExample($example);
    }

    public function deleteEntityTypeComments( $entityType )
    {
        $query = "DELETE `c` FROM `" . $this->getTableName() . "` AS `c`
            LEFT JOIN `" . BOL_CommentEntityDao::getInstance()->getTableName() . "` AS `e` ON( `c`.`" . self::COMMENT_ENTITY_ID . "` = `e`.`id` )
            WHERE `e`.`" . BOL_CommentEntityDao::ENTITY_TYPE . "` = :entityType";

        $this->dbo->query($query, array('entityType' => trim($entityType)));
    }

    public function deleteByPluginKey( $pluginKey )
    {
        $query = "DELETE `c` FROM `" . $this->getTableName() . "` AS `c`
            LEFT JOIN `" . BOL_CommentEntityDao::getInstance()->getTableName() . "` AS `e` ON( `c`.`" . self::COMMENT_ENTITY_ID . "` = `e`.`id` )
            WHERE `e`.`" . BOL_CommentEntityDao::PLUGIN_KEY . "` = :pluginKey";

        $this->dbo->query($query, array('pluginKey' => trim($pluginKey)));
    }

    public function findBatchCommentsCount( array $entities )
    {
        $queryStr = '';
        $params = array();
        foreach ( $entities as $entity )
        {
            $queryStr .= " (`ce`.`" . BOL_CommentEntityDao::ENTITY_TYPE . "` = ? AND `ce`.`" . BOL_CommentEntityDao::ENTITY_ID . "` = ? ) OR";
            $params[] = $entity['entityType'];
            $params[] = $entity['entityId'];
        }
        $queryStr = substr($queryStr, 0, -2);

        $query = "SELECT `ce`.`entityType`, `ce`.`entityId`, COUNT(*) AS `count` FROM `" . $this->getTableName() . "` AS `c`
			LEFT JOIN `" . BOL_CommentEntityDao::getInstance()->getTableName() . "` AS `ce` ON ( `c`.`" . self::COMMENT_ENTITY_ID . "` = `ce`.`id` )
			WHERE " . $queryStr . " GROUP BY `ce`.`id`";

        return $this->dbo->queryForList($query, $params);
    }

    public function findBatchCommentsList( $entities )
    {
        if ( empty($entities) )
        {
            return array();
        }

        $queryParts = array();
        $queryParams = array();
        $genId = 1;
        foreach ( $entities as $entity )
        {
            $queryParts[] = " SELECT * FROM ( SELECT `c`.*, `ce`.`entityType`, `ce`.`entityId` FROM `" . $this->getTableName() . "` AS `c`
			LEFT JOIN `" . BOL_CommentEntityDao::getInstance()->getTableName() . "` AS `ce` ON ( `c`.`" . self::COMMENT_ENTITY_ID . "` = `ce`.`id` )
			WHERE `ce`.`" . BOL_CommentEntityDao::ENTITY_TYPE . "` = ? AND `ce`.`" . BOL_CommentEntityDao::ENTITY_ID . "` = ?
			ORDER BY `" . self::CREATE_STAMP . "` DESC
			LIMIT 0, ? ) AS `al" . $genId++ . "` ".PHP_EOL;
            $queryParams[] = $entity['entityType'];
            $queryParams[] = $entity['entityId'];
            $queryParams[] = (int)$entity['countOnPage'];
        }

        $query = implode(" UNION ALL ", $queryParts);

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), $queryParams);
    }
}
