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
 * Data Access Object for `base_question_value` table.  
 *
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_QuestionValueDao extends OW_BaseDao
{
    const QUESTION_NAME = 'questionName';
    const VALUE = 'value';
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
     * @var BOL_QuestionValueDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_QuestionValueDao
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
        return 'BOL_QuestionValue';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_question_value';
    }

    public function findQuestionsValuesByQuestionNameList( array $questionNameList )
    {
        if ( isset($questionNameList) && count($questionNameList) > 0 )
        {
            $list = array();

            $questionList = BOL_QuestionDao::getInstance()->findQuestionByNameList($questionNameList);
            $parentList = array();

            foreach ( $questionList as $question )
            {
                $parentList[$question->parent] = $question->parent;
                $list[$question->name] = $question->name;
            }

            $parentQuestionList = BOL_QuestionDao::getInstance()->findQuestionByNameList($parentList);
            $parentQuestions = array();
            
            foreach ( $parentQuestionList as $question )
            {
                $parentQuestions[$question->name] = $question->name;
            }

            foreach ( $parentList as $key => $value )
            {
                if ( !empty($parentQuestions[$value]) )
                {
                    $list[$value] = $value;
                }
            }

            $example = new OW_Example();
            $example->andFieldInArray('questionName', $list);
            $example->setOrder('questionName, sortOrder');
            $values = $this->findListByExample($example);

            $result = array();
            $questionName = '';
            $count = 0;

            foreach ( $values as $key => $value )
            {
                if ( $questionName !== $value->questionName )
                {
                    if ( !empty($questionName) )
                    {
                        $result[$questionName]['count'] = $count;
                        $count = 0;
                    }

                    $questionName = $value->questionName;
                }

                $result[$value->questionName]['values'][] = $value;
                $count++;
            }

            foreach ( $questionList as $question )
            {
                if ( !empty($question->parent) && !empty( $parentQuestions[$question->parent] ) )
                {
                    $result[$question->name]['values'] = empty($result[$question->parent]['values']) ? array() : $result[$question->parent]['values'];
                }
            }

            if ( !empty($questionName) )
            {
                $result[$questionName]['count'] = $count;
            }

            return $result;
        }

        return array();
    }

    public function findQuestionValues( $questionName )
    {
        if ( $questionName === null )
        {
            return array();
        }

        $result = $this->findQuestionsValuesByQuestionNameList(array($questionName));
        
        if ( !empty($result[$questionName]['values']) )
        {
            return $result[$questionName]['values'];
        }
        
        return array();
    }

    public function findRealQuestionValues( $questionName )
    {
        if ( $questionName === null )
        {
            return array();
        }

        $name = trim($questionName);

        $example = new OW_Example();
        $example->andFieldEqual('questionName', $name);
        $example->setOrder('sortOrder');
        $result = $this->findListByExample($example);
        
        if ( !empty($result) )
        {
            return $result;
        }
        
        return array();
    }

    public function findQuestionValueById($id)
    {
        $example = new OW_Example();
        $example->andFieldEqual('id', $id);

        return $this->findObjectByExample($example);
    }

    public function findQuestionValue( $questionName, $value )
    {
        if ( $questionName === null )
        {
            return array();
        }

        $name = trim($questionName);
        $valueId = (int) $value;

        $example = new OW_Example();
        $example->andFieldEqual('questionName', $name);
        $example->andFieldEqual('value', $valueId);
        return $this->findObjectByExample($example);
    }

    public function deleteQuestionValue( $questionName, $value )
    {
        if ( $questionName === null )
        {
            return;
        }

        $name = trim($questionName);
        $valueId = (int) $value;

        $example = new OW_Example();
        $example->andFieldEqual('questionName', $name);
        $example->andFieldEqual('value', $valueId);
        $this->deleteByExample($example);

        return $this->dbo->getAffectedRows();
    }
}