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
 * Data Access Object for `base_question_section` table.
 *
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_QuestionSectionDao extends OW_BaseDao
{
    const SORT_ORDER = 'sortOrder';

    /**
     * Constructor.
     *
     */
    protected function __construct()
    {
        parent::__construct();
    }
    /**
     * Singleton instance.
     *
     * @var BOL_QuestionSectionDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_QuestionSectionDao
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
            self::$classInstance = new self();

        return self::$classInstance;
    }

    /**
     * @see OW_BaseDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return 'BOL_QuestionSection';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_question_section';
    }

    public function findLastSectionOrder()
    {
        $sql = " SELECT MAX( `sortOrder`) FROM `" . $this->getTableName() . "` ";
        return $this->dbo->queryForColumn($sql);
    }

    public function findBySectionName( $sectionName )
    {
        if ( $sectionName === null || mb_strlen($sectionName) === 0 )
        {
            return null;
        }

        $example = new OW_Example();
        $example->andFieldEqual('name', $sectionName);
        return $this->findObjectByExample($example);
    }

    public function findBySectionNameList( array $sectionNameList )
    {
        if ( $sectionNameList === null || !is_array($sectionNameList) || count($sectionNameList) === 0 )
        {
            return array();
        }

        $example = new OW_Example();
        $example->andFieldInArray('name', $sectionNameList);

        return $this->findListByExample($example);
    }

    
    public function findVisibleNotDeletableSection()
    {
        $example = new OW_Example();
        $example->andFieldEqual('isHidden', 0);
        $example->andFieldEqual('isDeletable', 0);
        $example->andFieldNotEqual('name', 'about_my_match');
        $example->setOrder(' sortOrder ASC ');
        $example->setLimitClause(0, 1);

        return $this->findObjectByExample($example);
    }
    
    
    public function findPreviousSection( BOL_QuestionSection $section )
    {
        if ( $section === null )
        {
            return null;
        }

        $example = new OW_Example();
        $example->andFieldLessOrEqual('sortOrder', (int) $section->sortOrder);
        $example->andFieldEqual('isHidden', 0);
        $example->andFieldNotEqual('name', 'about_my_match');
        $example->andFieldNotEqual('name', $section->name);
        $example->setOrder(' sortOrder desc ');

        return $this->findObjectByExample($example);
    }

    public function findNextSection( BOL_QuestionSection $section )
    {
        if ( $section === null )
        {
            return null;
        }

        $example = new OW_Example();
        $example->andFieldGreaterThenOrEqual('sortOrder', $section->sortOrder);
        $example->andFieldEqual('isHidden', 0);
        $example->andFieldNotEqual('name', 'about_my_match');
        $example->andFieldNotEqual('name', $section->name);
        $example->setOrder(' sortOrder ');

        return $this->findObjectByExample($example);
    }

    public function batchReplace( array $objects )
    {
        $this->dbo->batchInsertOrUpdateObjectList($this->getTableName(), $objects);

        return $this->dbo->getAffectedRows();
    }

    public function findSortedSectionList()
    {
        $example = new OW_Example();
        $example->setOrder('sortOrder');

        return $this->findListByExample($example);
    }
    
    public function findHiddenSections()
    {
        $example = new OW_Example();
        $example->andFieldInArray('isHidden', 1);

        return $this->findListByExample($example);
    }

    public function findSectionById($id)
    {
        $example = new OW_Example();
        $example->andFieldEqual('id', $id);

        return $this->findObjectByExample($example);
    }
}
