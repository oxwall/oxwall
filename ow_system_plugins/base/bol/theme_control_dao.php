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
 * Data Access Object for `base_theme_control` table.
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_ThemeControlDao extends OW_BaseDao
{
    const ATTRIBUTE = 'attribute';
    const SELECTOR = 'selector';
    const DEFAULT_VALUE = 'defaultValue';
    const TYPE = 'type';
    const THEME_ID = 'themeId';
    const KEY = 'key';
    const SECTION = 'section';
    const LABEL = 'label';
    const DESC = 'description';
    const MOBILE = 'mobile';

    const TYPE_VALUE_COLOR = 'color';
    const TYPE_VALUE_TEXT = 'text';
    const TYPE_VALUE_FONT = 'font';
    const TYPE_VALUE_IMAGE = 'image';

    /**
     * Singleton instance.
     *
     * @var BOL_ThemeControlDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_ThemeControlDao
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
        return 'BOL_ThemeControl';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_theme_control';
    }

    public function deleteThemeControls( $themeId )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::THEME_ID, $themeId);
        $this->deleteByExample($example);
    }

    public function findThemeControls( $themeId )
    {
        $query = "SELECT `c`.`id` AS `cid`, `c`.*, `cv`.* FROM `" . $this->getTableName() . "` AS `c`
            LEFT JOIN ( SELECT * FROM `" . BOL_ThemeControlValueDao::getInstance()->getTableName() . "` WHERE `themeId` = :themeId2 )
                AS `cv` ON (`c`.`key` = `cv`.`themeControlKey`)
            WHERE `c`.`themeId` = :themeId ORDER BY `" . self::LABEL . "`";

        return $this->dbo->queryForList($query, array('themeId' => $themeId, 'themeId2' => $themeId));
    }
}
