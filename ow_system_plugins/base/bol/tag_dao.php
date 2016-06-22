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
 * Data Access Object for `tag` table.  
 * 
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_TagDao extends OW_BaseDao
{
    // table field names
    const LABEL = 'label';

    /**
     * Singleton instance.
     *
     * @var BOL_TagDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_TagDao
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
        return 'BOL_Tag';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_tag';
    }

    /**
     * Returns dto list for provided tag labels.
     *
     * @param array<string>$labels
     * @return array
     */
    public function findTagsByLabel( $labels )
    {
        $example = new OW_Example();
        $example->andFieldInArray(self::LABEL, $labels);

        return $this->findListByExample($example);
    }

    public function findTagListByEntityIdList( $entityType, array $idList )
    {
        $query = "SELECT `t`.`label`, `et`.*  FROM " . $this->getTableName() . " AS `t`
			INNER JOIN `" . BOL_EntityTagDao::getInstance()->getTableName() . "` AS `et` ON ( `et`.`tagId` = `t`.`id` )
			WHERE `et`.`entityId` IN (" . $this->dbo->mergeInClause($idList) . ") AND `et`.`entityType` = :entityType";

        return $this->dbo->queryForList($query, array('entityType' => $entityType));
    }

    /**
     * Returns most popular tags for entity type.
     * 
     * @param string $entityType
     * @param integer $limit
     * @param integer $offset
     * @return array
     */
    public function findMostPopularTags( $entityType, $limit, $offset  = 0)
    {
        $query = "SELECT * FROM
            (
                SELECT `et`.*, COUNT(*) AS `count`, `t`.`label` AS `label` FROM `" . BOL_EntityTagDao::getInstance()->getTableName() . "` AS `et`
                LEFT JOIN `" . $this->getTableName() . "` AS `t` ON ( `et`.`tagId` = `t`.`id`	)
                WHERE `et`.`entityType` = :entityType AND `et`.`active` = 1
                GROUP BY `tagId`
                                    ORDER BY `count` DESC
                                    LIMIT :offset, :limit
            ) AS `t`
            ORDER BY `t`.`label`";

        return $this->dbo->queryForList($query, array('offset' => (int) $offset, 'limit' => (int) $limit, 'entityType' => $entityType));
    }

    /**
     * Returns tag list with popularity for provided entity item.
     * 
     * @param integer $entityId
     * @param string $entityType
     * @return array
     */
    public function findEntityTagsWithPopularity( $entityId, $entityType )
    {
        $query = "SELECT * FROM
	    		(
	    			SELECT `et`.*, COUNT(*) AS `count`, `t`.`label` AS `label` FROM `" . BOL_EntityTagDao::getInstance()->getTableName() . "` AS `et`
					INNER JOIN `" . $this->getTableName() . "` AS `t`
					ON ( `et`.`tagId` = `t`.`id`)
					WHERE `et`.`entityId` = :entityId AND `et`.`entityType` = :entityType
					GROUP BY `tagId` ORDER BY `count` DESC
				) AS `t` 
				ORDER BY `t`.`label`";

        return $this->dbo->queryForList($query, array('entityId' => $entityId, 'entityType' => $entityType));
    }
}