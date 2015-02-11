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
 * Data Transfer Object for `question_account_type` table.
 *
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_QuestionToAccountTypeDao extends OW_BaseDao
{
    const NAME = 'name';

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
     * @var BOL_QuestionToAccountTypeDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_QuestionToAccountTypeDao
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
        return 'BOL_QuestionToAccountType';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_question_to_account_type';
    }

    public function findQuestionsForAccountType( $accountType )
    {
        $sql = ' SELECT `question`.*  FROM  ' . BOL_QuestionDao::getInstance()->getTableName() . ' as `question`
                    INNER JOIN ' . $this->getTableName() . ' as `atq` ON ( `atq`.`questionName` = `question`.`name` )
                    INNER JOIN ' . BOL_QuestionAccountTypeDao::getInstance()->getTableName() . ' as `account` ON ( `account`.`name` = `atq`.`accountType` )
                WHERE  `atq`.`accountType` = :accountType ';

        return $this->dbo->queryForObjectList($sql, BOL_QuestionDao::getInstance()->getDtoClassName(), array('accountType' => $accountType));
    }
    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    /* public function findAccountTypesByQuestionName( $questionName )
    {
        $sql = ' SELECT `account`.*, COUNT( `questions`.`id` ) AS `questionCount` FROM  ' . BOL_QuestionAccountType::getInstance()->getDtoClassName() . ' as `account`
                            INNER JOIN ' . $this->getTableName() . ' as `atq` ON ( `atq`.`accountType` = `account`.`name` )
                            INNER JOIN ' . $this->getTableName() . ' as `atq` ON ( `atq`.`questionName` = `question`.`name` )
                    WHERE  `atq`.`questionName` = :questionName ';

        return $this->dbo->queryForList($sql, BOL_QuestionAccountType::getInstance()->getDtoClassName(), array('questionName' => $questionName));
    } */

    public function findByAccountType($accountType)
    {
        if ( empty($accountType) )
        {
            return null;
        }

        $ex = new OW_Example();
        $ex->andFieldEqual('accountTYpe', $accountType);

        return $this->findListByExample($ex);
    }

    public function findByQuestionName($questionName)
    {
        if ( empty($questionName) )
        {
            return array();
        }

        $ex = new OW_Example();
        $ex->andFieldEqual('questionName', $questionName);

        return $this->findListByExample($ex);
    }

    public function deleteByQuestionName($questionName)
    {
        if ( empty($questionName) )
        {
            return;
        }

        $ex = new OW_Example();
        $ex->andFieldEqual('questionName', $questionName);

        return $this->deleteByExample($ex);
    }

    public function deleteByAccountType($accountType)
    {
        if ( empty($accountType) )
        {
            return null;
        }

        $ex = new OW_Example();
        $ex->andFieldEqual('accountType', $accountType);

        return $this->deleteByExample($ex);
    }
    
    public function findByAccountTypeAndQuestionNameList( $accountType, array $questionNameList )
    {
        if ( empty($accountType) || empty($questionNameList) || !is_array($questionNameList) )
        {
            return array();
        }

        $example = new OW_Example();
        $example->andFieldEqual('accountType', $accountType);
        $example->andFieldInArray('questionName', $questionNameList);

        return $this->findListByExample($example);
    }

    public function deleteByQuestionNameAndAccountTypeList( $questionName, array $accountTypeList )
    {
        if ( empty($questionName) || empty($accountTypeList) || !is_array($accountTypeList) )
        {
            return;
        }

        $example = new OW_Example();
        $example->andFieldEqual('questionName', $questionName);
        $example->andFieldInArray('accountType', $accountTypeList);

        $this->deleteByExample($example);
    }
    
    public function batchReplace( $objects )
    {
        $this->dbo->batchInsertOrUpdateObjectList($this->getTableName(), $objects);
        return $this->dbo->getAffectedRows();
    }
}
