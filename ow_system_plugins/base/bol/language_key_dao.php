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
 * Singleton. 'Language Key' Data Access Object
 *
 * @author Aybat Duyshokov <duyshokov@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_LanguageKeyDao extends OW_BaseDao
{

    /**
     * Class constructor
     *
     */
    protected function __construct()
    {
        parent::__construct();
    }
    /**
     * Class instance
     *
     * @var BOL_LanguageKeyDao
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return BOL_LanguageKeyDao
     */
    public static function getInstance()
    {
        if ( !isset(self::$classInstance) )
            self::$classInstance = new self();

        return self::$classInstance;
    }

    /**
     * @see OW_BaseDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return 'BOL_LanguageKey';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_language_key';
    }

    public function findAllWithValues( $langId )
    {
        $keyTable = $this->getTableName();
        $prefixTable = BOL_LanguagePrefixDao::getInstance()->getTableName();
        $valueTable = BOL_LanguageValueDao::getInstance()->getTableName();
        $sql = 'SELECT k.*, p.`prefix`, v.`value` FROM ' . $keyTable . ' AS k
                    INNER JOIN ' . $prefixTable . ' AS p ON k.prefixId = p.id
                    INNER JOIN ' . $valueTable . ' AS v ON k.id = v.keyId AND v.languageId = ?';

        return $this->dbo->queryForList($sql, array($langId));
    }

    public function countKeyByPrefix( $prefixId )
    {
        $ex = new OW_Example();

        $ex->andFieldEqual('prefixId', $prefixId);

        return $this->countByExample($ex);
    }

    public function findKeyId( $prefixId, $key )
    {

        $query = "SELECT `id` FROM `{$this->getTableName()}` WHERE `prefixId` = ? AND `key` = ? LIMIT 1";

        return $this->dbo->queryForColumn($query, array($prefixId, $key));
    }

    public function findMissingKeys( $languageId, $first, $count )
    {
        $query = "
                SELECT k.`key`,
                       `p`.`label`, `p`.`prefix`
                FROM `" . BOL_LanguageKeyDao::getInstance()->getTableName() . "` as k
                LEFT JOIN `" . BOL_LanguageValueDao::getInstance()->getTableName() . "` as v
                     ON( k.id = v.keyId  and v.`languageId` = ? )
                INNER JOIN `" . BOL_LanguagePrefixDao::getInstance()->getTableName() . "` as p
                      ON(k.`prefixId` = p.id)
                WHERE v.keyId IS NULL OR (`v`.`value` IS NOT NULL AND LENGTH(`v`.`value`) = 0 )  
                LIMIT ?, ?
			";

        return $this->dbo->queryForList($query, array($languageId, $first, $count));
    }

    public function findMissingKeyCount( $languageId )
    {
        $query = "
                SELECT COUNT(*)
                FROM `" . BOL_LanguageKeyDao::getInstance()->getTableName() . "` as k
                LEFT JOIN `" . BOL_LanguageValueDao::getInstance()->getTableName() . "` as v
                     ON( k.id = v.keyId  and v.`languageId` = ? )
                INNER JOIN `" . BOL_LanguagePrefixDao::getInstance()->getTableName() . "` as p
                      ON(k.`prefixId` = p.id)
                WHERE v.keyId IS NULL OR (`v`.`value` IS NOT NULL AND LENGTH(`v`.`value`) = 0 )
			";

        return $this->dbo->queryForColumn($query, array($languageId));
    }

    public function findAllPrefixKeys( $prefixId )
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('prefixId', $prefixId);

        return $this->findListByExample($ex);
    }

    public function countAllPrefixKeys( $prefixId )
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('prefixId', $prefixId);

        return $this->countByExample($ex);
    }
}