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
 * Data Transfer Object for `base_question` table.
 *
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_QuestionDao extends OW_BaseDao
{
    const NAME = 'name';
    const SECTION_NAME = 'sectionName';
    const TYPE = 'type';
    const PRESENTATION = 'presentation';
    const SORT_ORDER = 'sortOrder';
    const REQUIRED = 'required';
    const ON_JOIN = 'onJoin';
    const ON_EDIT = 'onEdit';
    const ON_SEARCH = 'onSearch';
    const ON_VIEW = 'onView';

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
     * @var BOL_QuestionDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_QuestionDao
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
        return 'BOL_Question';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_question';
    }

    public function findQuestionByName( $questionName )
    {
        if ( $questionName === null )
        {
            return null;
        }

        $name = trim($questionName);

        $example = new OW_Example();
        $example->andFieldEqual('name', $name);
        return $this->findObjectByExample($example);
    }

    public function findQuestionByNameList( $questionNameList )
    {
        if ( empty($questionNameList) )
        {
            return array();
        }

        $example = new OW_Example();
        $example->andFieldInArray('name', $questionNameList);
        $example->setOrder('sortOrder');
        $dtoList = $this->findListByExample($example);

        $result = array();

        foreach ( $dtoList as $dto )
        {
            $result[$dto->name] = $dto;
        }

        return $result;
    }

    public function findQuestionsByPresentationList( $presentation )
    {
        if ( $presentation === null || !is_array($presentation) )
        {
            return array();
        }

        $example = new OW_Example();
        $example->andFieldInArray('presentation', $presentation);
        $example->setOrder('sortOrder');

        return $this->findListByExample($example);
    }

    public function findQuestionsByQuestionNameList( array $questionName )
    {
        if ( $questionName === null || !is_array($questionName) || count($questionName) === 0 )
        {
            return array();
        }

        $example = new OW_Example();
        $example->andFieldInArray('name', $questionName);
        return $this->findListByExample($example);
    }

    public function findAllQuestionsForAccountType( $accountType )
    {
        if ( $accountType === null )
        {
            return array();
        }

        $accountTypeName = trim($accountType);

        $sql = " SELECT DISTINCT `question`.* FROM `" . $this->getTableName() . "` as `question`

                    LEFT JOIN  `" . BOL_QuestionSectionDao::getInstance()->getTableName() . "` as `section`
                            ON ( `question`.`sectionName` = `section`.`name` AND `question`.`sectionName` != '' AND `question`.`sectionName` IS NOT NULL )

                    INNER JOIN " . BOL_QuestionToAccountTypeDao::getInstance()->getTableName() . " as `qta` ON( `question`.`name` = `qta`.`questionName` )

                    WHERE ( `qta`.`accountType` = :accountTypeName OR :accountTypeName = 'all' ) AND ( `section`.isHidden IS NULL OR `section`.isHidden = 0 )
                    
                    ORDER BY IF( `section`.`name` IS NULL, 0, 1 ),  `section`.`sortOrder`, `question`.`sortOrder` ";

        return $this->dbo->queryForList($sql, array('accountTypeName' => $accountTypeName));
    }

    public function findQuestionsForAccountType( $accountType )
    {
        if ( $accountType === null )
        {
            return array();
        }

        $accountTypeName = trim($accountType);

        $sql = " SELECT DISTINCT `question`.* FROM `" . $this->getTableName() . "` as `question`

                    LEFT JOIN  `" . BOL_QuestionSectionDao::getInstance()->getTableName() . "` as `section`
                            ON ( `question`.`sectionName` = `section`.`name` AND `question`.`sectionName` != '' AND `question`.`sectionName` IS NOT NULL )

                    INNER JOIN " . BOL_QuestionToAccountTypeDao::getInstance()->getTableName() . " as `qta` ON( `question`.`name` = `qta`.`questionName` )

                    WHERE ( `qta`.`accountType` = :accountTypeName )  AND ( `section`.isHidden IS NULL OR `section`.isHidden = 0 )
                    ORDER BY IF( `section`.`name` IS NULL, 0, 1 ),  `section`.`sortOrder`, `question`.`sortOrder` ";
        
        return $this->dbo->queryForList($sql, array('accountTypeName' => $accountTypeName));
    }

    public function findSearchQuestionsForAccountType( $accountType )
    {
        $accountTypeName = trim($accountType);

        $sql = " SELECT DISTINCT `question`.* FROM `" . $this->getTableName() . "` as `question`

                    LEFT JOIN  `" . BOL_QuestionSectionDao::getInstance()->getTableName() . "` as `section`
                            ON ( `question`.`sectionName` = `section`.`name` AND `question`.`sectionName` != '' AND `question`.`sectionName` IS NOT NULL )

                    INNER JOIN " . BOL_QuestionToAccountTypeDao::getInstance()->getTableName() . " as `qta` ON( `question`.`name` = `qta`.`questionName` )

                    WHERE ( `qta`.`accountType` = :accountTypeName OR :accountTypeName = 'all' ) AND `question`.`onSearch` = 1  AND ( `section`.isHidden IS NULL OR `section`.isHidden = 0 )
                    ORDER BY IF( `section`.`name` IS NULL, 0, 1 ),  `section`.`sortOrder`, `question`.`sortOrder` ";

        return $this->dbo->queryForList($sql, array('accountTypeName' => $accountTypeName));
    }

    public function findAllQuestionsWithSectionForAccountType( $accountType )
    {
        if ( $accountType === null )
        {
            return array();
        }

        $accountTypeName = trim($accountType);

        $sql = " SELECT DISTINCT `question`.`id`, `question`.`name`, `section`.`name` as `sectionName`, `question`.`accountTypeName`,
                            `question`.`type`, `question`.`presentation`, `question`.`required`, `question`.`onJoin`,
                            `question`.`onEdit`, `question`.`onSearch`, `question`.`onView`, `question`.`base`,
                            `question`.`removable`, `question`.`columnCount`, `question`.`sortOrder`,
                            `section`.`sortOrder` as `sectionOrder`, `question`.`parent` as `parent`
                FROM `" . $this->getTableName() . "` as `question`

                LEFT JOIN  `" . BOL_QuestionSectionDao::getInstance()->getTableName() . "` as `section`
                        ON ( `question`.`sectionName` = `section`.`name` AND `question`.`sectionName` != '' AND `question`.`sectionName` IS NOT NULL )

                LEFT JOIN " . BOL_QuestionToAccountTypeDao::getInstance()->getTableName() . " as `qta` ON( `question`.`name` = `qta`.`questionName` )

                WHERE ( `qta`.`accountType` = :accountTypeName OR :accountTypeName = 'all' )  AND ( `section`.isHidden IS NULL OR `section`.isHidden = 0 )

                ORDER BY IF( `section`.`name` IS NULL, 0, 1 )  ASC,  `section`.`sortOrder`, `question`.`sortOrder` ";
                
        return $this->dbo->queryForList($sql, array('accountTypeName' => $accountTypeName));
    }

    public function findSignUpQuestionsForAccountType( $accountType, $baseOnly = false )
    {
        if ( $accountType === null )
        {
            return array();
        }

        $accountTypeName = trim($accountType);

        $questionAdds = "";

        if ( $baseOnly === true )
        {
            $questionAdds = ' AND `question`.`base` = 1 ';
        }

        $sql = " SELECT DISTINCT `question`.* FROM `" . $this->getTableName() . "` as `question`

                    LEFT JOIN  `" . BOL_QuestionSectionDao::getInstance()->getTableName() . "` as `section`
                            ON ( `question`.`sectionName` = `section`.`name` AND `question`.`sectionName` != '' AND `question`.`sectionName` IS NOT NULL )

                    INNER JOIN " . BOL_QuestionToAccountTypeDao::getInstance()->getTableName() . " as `qta` ON( `question`.`name` = `qta`.`questionName` )

                    WHERE ( `qta`.`accountType` = :accountTypeName OR :accountTypeName = 'all'  )  AND ( `section`.isHidden IS NULL OR `section`.isHidden = 0 )
                                  AND `question`.`onJoin` = '1' " . $questionAdds . "
                    ORDER BY IF( `section`.`name` IS NULL, 0, 1 ),  `section`.`sortOrder`, `question`.`sortOrder` ";

        return $this->dbo->queryForList($sql, array('accountTypeName' => $accountTypeName));
    }

    public function findEditQuestionsForAccountType( $accountType )
    {
        if ( $accountType === null )
        {
            return array();
        }

        $accountTypeName = trim($accountType);

        $sql = " SELECT DISTINCT `question`.* FROM `" . $this->getTableName() . "` as `question`

                    LEFT JOIN  `" . BOL_QuestionSectionDao::getInstance()->getTableName() . "` as `section`
                            ON ( `question`.`sectionName` = `section`.`name` AND `question`.`sectionName` != '' AND `question`.`sectionName` IS NOT NULL )

                    INNER JOIN " . BOL_QuestionToAccountTypeDao::getInstance()->getTableName() . " as `qta` ON( `question`.`name` = `qta`.`questionName` )

                    WHERE ( `qta`.`accountType` = :accountTypeName OR :accountTypeName = 'all' )  AND ( `section`.isHidden IS NULL OR `section`.isHidden = 0 )
                                  AND `question`.`onEdit` = '1'
                    ORDER BY IF( `section`.`name` IS NULL, 0, 1 ),  `section`.`sortOrder`, `question`.`sortOrder` ";

        return $this->dbo->queryForList($sql, array('accountTypeName' => $accountTypeName));
    }

    public function findRequiredQuestionsForAccountType( $accountType )
    {
        if ( $accountType === null )
        {
            return array();
        }

        $accountTypeName = trim($accountType);

        $sql = " SELECT DISTINCT `question`.* FROM `" . $this->getTableName() . "` as `question`

                    LEFT JOIN  `" . BOL_QuestionSectionDao::getInstance()->getTableName() . "` as `section`
                            ON ( `question`.`sectionName` = `section`.`name` AND `question`.`sectionName` != '' AND `question`.`sectionName` IS NOT NULL )

                    INNER JOIN " . BOL_QuestionToAccountTypeDao::getInstance()->getTableName() . " as `qta` ON( `question`.`name` = `qta`.`questionName` )

                    WHERE `qta`.`accountType` = :accountTypeName AND `question`.`required` = '1' AND ( `section`.isHidden IS NULL OR `section`.isHidden = 0 )
                    ORDER BY IF( `section`.`name` IS NULL, 0, 1 ),  `section`.`sortOrder`, `question`.`sortOrder` ";

        return $this->dbo->queryForList($sql, array('accountTypeName' => $accountTypeName));
    }

    public function findViewQuestionsForAccountType( $accountType )
    {
        if ( $accountType === null )
        {
            return array();
        }

        $accountTypeName = trim($accountType);

        $sql = " SELECT DISTINCT `question`.* FROM `" . $this->getTableName() . "` as `question`

                    LEFT JOIN  `" . BOL_QuestionSectionDao::getInstance()->getTableName() . "` as `section`
                            ON ( `question`.`sectionName` = `section`.`name` AND `question`.`sectionName` != '' AND `question`.`sectionName` IS NOT NULL )

                    INNER JOIN " . BOL_QuestionToAccountTypeDao::getInstance()->getTableName() . " as `qta` ON( `question`.`name` = `qta`.`questionName` )

                    WHERE ( `qta`.`accountType` = :accountTypeName OR :accountTypeName = 'all' ) AND ( `section`.isHidden IS NULL OR `section`.isHidden = 0 )
                                  AND `question`.`onView` = '1' 
                    ORDER BY IF( `section`.`name` IS NULL, 0, 1 ),  `section`.`sortOrder`, `question`.`sortOrder` ";

        return $this->dbo->queryForList($sql, array('accountTypeName' => $accountTypeName));
    }

    public function findBaseSignUpQuestions()
    {
        $sql = " SELECT `question`.* FROM `" . $this->getTableName() . "` as `question` 

                    LEFT JOIN  `" . BOL_QuestionSectionDao::getInstance()->getTableName() . "` as `section`
                            ON ( `question`.`sectionName` = `section`.`name` AND `question`.`sectionName` != '' AND `question`.`sectionName` IS NOT NULL )

                    WHERE `question`.`base` = 1 AND `question`.`onJoin` = '1' AND ( `section`.isHidden IS NULL OR `section`.isHidden = 0 )
                    ORDER BY IF( `section`.`name` IS NULL, 0, 1 ),  `section`.`sortOrder`, `question`.`sortOrder` ";

        return $this->dbo->queryForList($sql);
    }

    public function findLastQuestionOrder( $sectionName )
    {
        $sql = " SELECT MAX( `sortOrder`) FROM `" . $this->getTableName() . "` ";

        $result = null;
        if ( isset($sectionName) && count(trim($sectionName)) > 0 )
        {
            $sql .= ' WHERE `sectionName`= :sectionName ';
            $result = $this->dbo->queryForColumn($sql, array('sectionName' => trim($sectionName)));
        }
        else
        {
            $result = $this->dbo->queryForColumn($sql);
        }

        return $result;
    }

    public function findQuestionsBySectionNameList( array $sectionNameList )
    {
        if ( $sectionNameList === null || !is_array($sectionNameList) || count($sectionNameList) === 0 )
        {
            return array();
        }

        $example = new OW_Example();
        $example->andFieldInArray('sectionName', $sectionNameList);

        return $this->findListByExample($example);
    }

    public function batchReplace( $objects )
    {
        $this->dbo->batchInsertOrUpdateObjectList($this->getTableName(), $objects);
        return $this->dbo->getAffectedRows();
    }

    public function findQuestionChildren( $parentQuestionName )
    {
        if ( empty($parentQuestionName) )
        {
            return array();
        }

        $example = new OW_Example();
        $example->andFieldEqual('parent', $parentQuestionName);
        return $this->findListByExample($example);
    }
}