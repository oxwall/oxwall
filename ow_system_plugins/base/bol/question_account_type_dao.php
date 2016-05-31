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
class BOL_QuestionAccountTypeDao extends OW_BaseDao
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
     * @var BOL_QuestionAccountTypeDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_QuestionAccountTypeDao
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
        return 'BOL_QuestionAccountType';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_question_account_type';
    }

    public function getDefaultAccountType()
    {
        $sql = ' SELECT `account`.*  FROM  ' . $this->getTableName() . ' as `account`
                    ORDER BY `account`.`sortOrder` LIMIT 1';

        return $this->dbo->queryForObject($sql, $this->getDtoClassName());
    }
    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function findAllAccountTypesWithQuestionsCount()
    {
        $sql = ' SELECT `account`.*, COUNT( `questions`.`id` ) AS `questionCount` FROM  ' . $this->getTableName() . ' as `account`
                            INNER JOIN ' . BOL_QuestionToAccountTypeDao::getInstance()->getTableName() . ' as `qta` ON( `account`.`name` = `qta`.`accountType` )
                            INNER JOIN ' . BOL_QuestionDao::getInstance()->getTableName() . ' as `questions` ON( `qta`.`questionName` = `questions`.`name` )
                    GROUP BY `account`.`id`, `account`.`name`
                    ORDER BY `account`.`sortOrder` ';

        return $this->dbo->queryForList($sql);
    }

    public function findCountExlusiveQuestionForAccount($accountType)
    {
        $sql = ' SELECT COUNT( `questions`.`id` ) AS `questionCount`
            FROM ' . BOL_QuestionDao::getInstance()->getTableName() . ' as `questions`
                    INNER JOIN ' . BOL_QuestionToAccountTypeDao::getInstance()->getTableName() . ' as `qta` ON( `questions`.`name` = `qta`.`questionName` )
                    
            WHERE `qta`.`accountType` = :accountType ';

        return $this->dbo->queryForColumn($sql, array('accountType' => $accountType));
    }

    public function findAccountTypeByNameList( array $accountTypeNameList )
    {
        if ( $accountTypeNameList === null || !is_array($accountTypeNameList) || count($accountTypeNameList) === 0 )
        {
            return array();
        }

        $example = new OW_Example();
        $example->andFieldInArray('name', $accountTypeNameList);
        $example->setOrder('sortOrder');

        return $this->findListByExample($example);
    }

    public function findAllAccountTypes()
    {
        $example = new OW_Example();
        $example->setOrder('sortOrder');

        return $this->findListByExample($example);
    }

    public function findLastAccountTypeOrder()
    {
        $sql = " SELECT MAX( `sortOrder` ) FROM `" . $this->getTableName() . "` ";

        return $this->dbo->queryForColumn($sql);
    }

    public function batchReplace( $objects )
    {
        $this->dbo->batchInsertOrUpdateObjectList($this->getTableName(), $objects);
        return $this->dbo->getAffectedRows();
    }
    
    public function deleteRoleByAccountType( BOL_QuestionAccountType $accountType )
    {        
        if ( empty($accountType) )
        {
            return;
        }
        
        $sql = " DELETE r FROM `" . BOL_AuthorizationUserRoleDao::getInstance()->getTableName() . "` r "
                . " INNER JOIN  " . BOL_UserDao::getInstance()->getTableName() . " u ON ( u.id = r.userId ) "
                . " INNER JOIN " . BOL_QuestionAccountTypeDao::getInstance()->getTableName() . " `accountType` ON ( accountType.name = u.accountType ) "
                . " WHERE  u.accountType = :account AND r.roleId = :role ";
        
        $this->dbo->query($sql, array('account' => $accountType->name, 'role' => $accountType->roleId));
    }
    
    public function addRoleByAccountType( BOL_QuestionAccountType $accountType )
    {        
        if ( empty($accountType) )
        {
            return;
        }
        
        $sql = " REPLACE INTO `" . BOL_AuthorizationUserRoleDao::getInstance()->getTableName() . "` ( `userId`, `roleId` ) "
                . "SELECT u.id, :role FROM " . BOL_UserDao::getInstance()->getTableName() . " u "
                . " INNER JOIN " . BOL_QuestionAccountTypeDao::getInstance()->getTableName() . " `accountType` ON ( accountType.name = u.accountType ) "
                . " WHERE  u.accountType = :account ";
        
        $this->dbo->query( $sql, array( 'account' => $accountType->name, 'role' => $accountType->roleId ) );
    }

    public function findAccountTypeById( $id )
    {
        // get account name
        $example = new OW_Example();
        $example->andFieldEqual('id', $id);

        return $this->findObjectByExample($example);
    }
}
