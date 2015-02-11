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
 * Singleton. 'Suspended User' Data Access Object
 *
 * @author Aybat Duyshokov <duyshokov@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_UserResetPasswordDao extends OW_BaseDao
{
    const USER_ID = 'userId';
    const CODE = 'code';
    const EXPIRATION_TS = 'expirationTimeStamp';
    const UPDATE_TS = 'updateTimeStamp';

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
     * @var UserSuspendDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return UserSuspendDao
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }
        return self::$classInstance;
    }

    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_user_reset_password';
    }

    public function getDtoClassName()
    {
        return 'BOL_UserResetPassword';
    }

    /**
     * @param integer $userId
     * @return BOL_UserResetPassword
     */
    public function findByUserId( $userId )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::USER_ID, (int)$userId);

        return $this->findObjectByExample($example);
    }

    /**
     * @param string $code
     * @return BOL_UserResetPassword
     */
    public function findByCode( $code )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::CODE, $code);

        return $this->findObjectByExample($example);
    }

    public function deleteExpiredEntities()
    {
        $example = new OW_Example();
        $example->andFieldLessOrEqual(self::EXPIRATION_TS, time());

        $this->deleteByExample($example);
    }
}