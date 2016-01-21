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
 * Data Access Object for `base_billing_gateway` table.
 *
 * @author Egor Bulgakov <egor.bulgakov@gmail.com>
 * @package ow.ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_BillingGatewayDao extends OW_BaseDao
{

    /**
     * Constructor.
     */
    protected function __construct()
    {
        parent::__construct();
    }
    /**
     * Singleton instance.
     *
     * @var BOL_BillingGatewayDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class
     *
     * @return BOL_BillingGatewayDao
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
     * @see OW_BaseDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return 'BOL_BillingGateway';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_billing_gateway';
    }

    public function findByKey( $key )
    {
        if ( !mb_strlen($key) )
        {
            return false;
        }

        $example = new OW_Example();
        $example->andFieldEqual('gatewayKey', $key);

        return $this->findObjectByExample($example);
    }

    public function deleteByKey( $key )
    {
        if ( !mb_strlen($key) )
        {
            return false;
        }

        $example = new OW_Example();
        $example->andFieldEqual('gatewayKey', $key);
        
        return $this->deleteByExample($example);
    }

    public function getActiveList($forMobile = false)
    {
        $example = new OW_Example();
        $example->andFieldEqual('active', 1);
        $example->andFieldEqual('hidden', 0);

        if( $forMobile )
        {
            $example->andFieldEqual('mobile', 1);
        }

        return $this->findListByExample($example);
    }
    
    public function getNotDynamicList()
    {
        $example = new OW_Example();
        $example->andFieldEqual('dynamic', 0);
        $example->andFieldEqual('hidden', 0);
        
        return $this->findListByExample($example);
    }
}