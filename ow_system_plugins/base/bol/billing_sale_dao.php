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
 * Data Access Object for `base_billing_sale` table.
 *
 * @author Egor Bulgakov <egor.bulgakov@gmail.com>
 * @package ow.ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_BillingSaleDao extends OW_BaseDao
{

    /**
     * Constructor.
     */
    protected function __construct()
    {
        parent::__construct();
    }
    const STATUS_INIT = 'init';
    const STATUS_PREPARED = 'prepared';
    const STATUS_VERIFIED = 'verified';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_PROCESSING = 'processing';
    const STATUS_ERROR = 'error';

    const INIT_SALES_EXPIRE_INTERVAL = 432000; // 5 days
    const PREPARED_SALES_EXPIRE_INTERVAL = 2592000; // 30 days
    /**
     * Singleton instance.
     *
     * @var BOL_BillingSaleDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class
     *
     * @return BOL_BillingSaleDao
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
        return 'BOL_BillingSale';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_billing_sale';
    }

    /**
     * Finds sale by hash
     * 
     * @param $hash
     * @return BOL_BillingSale
     */
    public function findByHash( $hash )
    {
        $example = new OW_Example();
        $example->andFieldEqual('hash', $hash);

        return $this->findObjectByExample($example);
    }

    /**
     * Finds sale by transaction Id
     * 
     * @param $transId
     * @param $gatewayId
     * @return mixed
     */
    public function findByGatewayTransactionId( $transId, $gatewayId = null )
    {
        $example = new OW_Example();
        $example->andFieldEqual('transactionUid', $transId);

        if ( !empty($gatewayId) )
        {
            $example->andFieldEqual('gatewayId', $gatewayId);
        }

        return $this->findObjectByExample($example);
    }

    /**
     * Expire sales with 'init' status
     * 
     * @return boolean
     */
    public function expireInitSales()
    {
        $example = new OW_Example();
        $example->andFieldEqual('status', self::STATUS_INIT);
        $example->andFieldLessThan('timeStamp', time() - self::INIT_SALES_EXPIRE_INTERVAL);

        $this->deleteByExample($example);
    }

    /**
     * Expire sales with 'prepared' status
     * 
     * @return boolean
     */
    public function expirePreparedSales()
    {
        $example = new OW_Example();
        $example->andFieldEqual('status', self::STATUS_PREPARED);
        $example->andFieldLessThan('timeStamp', time() - self::PREPARED_SALES_EXPIRE_INTERVAL);

        $this->deleteByExample($example);
    }
    
    public function getSaleList( $page, $onPage )
    {
        $first = ($page - 1 ) * $onPage;
        
        $gatewayDao = BOL_BillingGatewayDao::getInstance();
        $pluginDao = BOL_PluginDao::getInstance();
        
        $sql = "SELECT `s`.*, `gw`.`gatewayKey`, `p`.`title` AS `pluginTitle` 
            FROM `".$this->getTableName()."` AS `s`
            LEFT JOIN `".$gatewayDao->getTableName()."` AS `gw` ON (`s`.`gatewayId` = `gw`.`id`)
            LEFT JOIN `".$pluginDao->getTableName()."` AS `p` ON (`s`.`pluginKey` = `p`.`key`)
            WHERE `s`.`status` = 'delivered'
            ORDER BY `timeStamp` DESC
            LIMIT :first, :limit";
        
        return $this->dbo->queryForList($sql, array('first' => $first, 'limit' => $onPage));
    }
    
    public function getSalesCurrencies()
    {
        $sql = "SELECT DISTINCT(`currency`) 
            FROM `".$this->getTableName()."`";
        
        return $this->dbo->queryForList($sql);
    }
    
    public function getSalesSumByCurrency( $currency )
    {
        $sql = "SELECT SUM(`totalAmount`) 
            FROM `".$this->getTableName()."`
            WHERE `currency` = :curr
            AND `status` = 'delivered'";
        
        return $this->dbo->queryForColumn($sql, array('curr' => $currency));
    }
    
    public function countSales( )
    {
        $example = new OW_Example();
        $example->andFieldEqual('status', 'delivered');
        
        return $this->countByExample($example);
    }
}