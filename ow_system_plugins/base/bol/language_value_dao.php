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
 * Singleton. 'Language Value' Data Access Object
 *
 * @author Aybat Duyshokov <duyshokov@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_LanguageValueDao extends OW_BaseDao
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
     * @var BOL_LanguageValueDao
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return BOL_LanguageValueDao
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
        return 'BOL_LanguageValue';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_language_value';
    }

    public function findLastKeyList( $first, $count, $prefix = null )
    {
        if ( $prefix !== null )
        {
            $prefixId = BOL_LanguagePrefixDao::getInstance()->findPrefixId($prefix);

            if ( !$prefixId )
            {
                throw new Exception('There is no such prefix..');
            }
        }

        $query_part = array();

        $query_part['optional-prefix_criteria'] = ( $prefix !== null && $prefixId > 0 ) ? "`p`.`id` = {$prefixId}" : '1';

        $query_part['dev-mode-order'] = !$this->isDevMode() && $prefix == null ? "IF(`p`.`prefix` = 'ow_custom', 1, 0) DESC, " : '';

        $keyTable = BOL_LanguageKeyDao::getInstance()->getTableName();
        $prefixTable = BOL_LanguagePrefixDao::getInstance()->getTableName();

        $query = "
		SELECT `key`,
		       `p`.`label`, `p`.`prefix`
		FROM `" . $keyTable . "` as `k`
		INNER JOIN `" . $prefixTable . "` AS `p`
		     ON ( `k`.`prefixId` = `p`.`id` )
	    WHERE {$query_part['optional-prefix_criteria']} /*optional-prefix_criteria*/ 
		ORDER BY {$query_part['dev-mode-order']} `p`.`label`,
		         `k`.`id` desc
		LIMIT ?, ?
		";

        return $this->dbo->queryForList($query, array($first, $count));
    }

    public function findSearchResultKeyList( $languageId, $first, $count, $search )
    {
        $search = $this->dbo->escapeString($search);

        $_query =
            "
			 SELECT `k`.`key`,
			        `p`.`label`, `p`.`prefix`
			 FROM `" . BOL_LanguageValueDao::getInstance()->getTableName() . "` as `v`
			 INNER JOIN `" . BOL_LanguageKeyDao::getInstance()->getTableName() . "` as `k`
			      ON( `v`.`keyId` = `k`.`id` )
			 INNER JOIN `" . BOL_LanguagePrefixDao::getInstance()->getTableName() . "` as `p`
			      ON( `k`.`prefixId` = `p`.`id` )
			 WHERE `v`.`value` LIKE ? AND `v`.`languageId` = ?
			 ORDER BY `p`.`label`,
			        `k`.`id` desc
			 LIMIT ?, ?
			";

        return $this->dbo->queryForList($_query, array("%{$search}%", $languageId, $first, $count));
    }

    public function findKeySearchResultKeyList( $languageId, $first, $count, $search )
    {
        $search = $this->dbo->escapeString($search);

        $_query =
            "
			 SELECT `k`.`key`,
			        `p`.`label`, `p`.`prefix`
			 FROM `" . BOL_LanguageKeyDao::getInstance()->getTableName() . "` as `k`
			 INNER JOIN `" . BOL_LanguagePrefixDao::getInstance()->getTableName() . "` as `p`
			    ON( `k`.`prefixId` = `p`.`id` ) 
			 WHERE `k`.`key` LIKE :keySearch
			 LIMIT :first, :count
			";

        return $this->dbo->queryForList($_query, array('keySearch'=>"%{$search}%", 'first'=>$first, 'count'=>$count));
    }




    public function countSearchResultKeys( $languageId, $search )
    {
        $search = $this->dbo->escapeString($search);

        $_query =
            "
			 SELECT COUNT(*)
			 FROM `" . BOL_LanguageValueDao::getInstance()->getTableName() . "` as `v`
			 INNER JOIN `" . BOL_LanguageKeyDao::getInstance()->getTableName() . "` as `k`
			      ON( `v`.`keyId` = `k`.`id` )
			 INNER JOIN `" . BOL_LanguagePrefixDao::getInstance()->getTableName() . "` as `p`
			      ON( `k`.`prefixId` = `p`.`id` )
			 WHERE `v`.`value` LIKE ? AND `v`.`languageId` = ? 
			";

        return $this->dbo->queryForColumn($_query, array("%{$search}%", $languageId));
    }

    public function countKeySearchResultKeys( $languageId, $search )
    {
        $search = $this->dbo->escapeString($search);

        $_query =
            "
			 SELECT COUNT(*)
			 FROM `" . BOL_LanguageKeyDao::getInstance()->getTableName() . "` as `k`
			 INNER JOIN `" . BOL_LanguagePrefixDao::getInstance()->getTableName() . "` as `p`
			    ON( `k`.`prefixId` = `p`.`id` )
			 WHERE `k`.`key` LIKE :keySearch
			";

        return $this->dbo->queryForColumn($_query, array('keySearch'=>"%{$search}%"));
    }

    public function findValue( $languageId, $keyId )
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('languageId', $languageId)->andFieldEqual('keyId', $keyId);

        return $this->findObjectByExample($ex);
    }

    public function deleteValues( $languageId )
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('languageId', $languageId);

        $this->deleteByExample($ex);
    }

    private function isDevMode()
    {
        if ( !empty($_GET) )
        {
            $arr = explode('?', OW::getRequest()->getRequestUri());

            return $arr[0] == OW::getRouter()->uriForRoute('admin_developer_tools_language');
        }

        return OW::getRequest()->getRequestUri() == OW::getRouter()->uriForRoute('admin_developer_tools_language');
    }

    public function deleteByKeyId( $id )
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('keyId', $id);

        $this->deleteByExample($ex);
    }
}