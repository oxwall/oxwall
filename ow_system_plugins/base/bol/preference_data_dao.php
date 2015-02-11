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
 * Data Access Object for `base_question_data` table.
 *
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_PreferenceDataDao extends OW_BaseDao
{
    const PREFERENCE_NAME = 'key';
    const USER_ID = 'userId';
    const VALUE = 'value';

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
     * @var BOL_PreferenceDataDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_PreferenceDataDao
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
        return 'BOL_PreferenceData';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_preference_data';
    }

    public function findByPreferenceNameList( array $preferenceNameList, $userId )
    {
        if ( $preferenceNameList === null || count($preferenceNameList) === 0 || empty($userId) )
        {
            return array();
        }

        $example = new OW_Example();
        $example->andFieldEqual(self::USER_ID, $userId);
        $example->andFieldInArray(self::PREFERENCE_NAME, $preferenceNameList);
        return $this->findListByExample($example);
    }

    public function deleteByPreferenceNamesList( array $preferenceNameList )
    {
        if ( $preferenceNameList === null || count($preferenceNameList) === 0 )
        {
            return;
        }

        $example = new OW_Example();
        $example->andFieldInArray(self::PREFERENCE_NAME, $preferenceNameList);
        $this->deleteByExample($example);
    }

    /**
     * Returns preference values
     *
     * @return array
     */
    public function findByPreferenceListForUserList( array $preferenceNameList, $userIdList )
    {
        if ( $preferenceNameList === null || count($preferenceNameList) === 0 )
        {
            return array();
        }

        if ( $userIdList === null || count($userIdList) === 0 )
        {
            return array();
        }

        $example = new OW_Example();
        $example->andFieldInArray(self::USER_ID, $userIdList);
        $example->andFieldInArray(self::PREFERENCE_NAME, $preferenceNameList);

        $data = $this->findListByExample($example);

        $result = array();
        foreach ( $data as $object )
        {
            $result[$object->userId][$object->key] = $object;
        }

        return $result;
    }

    public function batchReplace( array $objects )
    {
        $this->dbo->batchInsertOrUpdateObjectList($this->getTableName(), $objects);
        return $this->dbo->getAffectedRows();
    }

    public function deleteByUserId( $userId )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::USER_ID, (int) $userId);

        $this->deleteByExample($example);
    }
}