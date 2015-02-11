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
 * Data Transfer Object for `base_billing_sale` table.
 *
 * @author Egor Bulgakov <egor.bulgakov@gmail.com>
 * @package ow.ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_BillingSale extends OW_Entity
{
    /**
     * @var integer
     */
    public $id;
    /**
     * @var string
     */
    public $hash;
    /**
     * @var string
     */
    public $pluginKey;
    /**
     * @var string
     */
    public $entityKey;
    /**
     * @var int
     */
    public $entityId;
    /**
     * @var string 
     */
    public $entityDescription;
    /**
     * @var int
     */
    public $gatewayId;
    /**
     * @var int
     */
    public $userId;
    /**
     * @var string
     */
    public $transactionUid;
    /**
     * @var float
     */
    public $price;
    /**
     * @var int
     */
    public $period;
    /**
     * @var int
     */
    public $quantity = 1;
    /**
     * @var float
     */
    public $totalAmount;
    /**
     * @var string
     */
    public $currency;
    /**
     * @var boolean
     */
    public $recurring;
    /**
     * @var string
     */
    public $status;
    /**
     * @var int
     */
    public $timeStamp;
    /**
     * JSON encoded extra data
     * 
     * @var string
     */
    public $extraData;


    public function getExtraData()
    {
        return mb_strlen($this->extraData) ? json_decode($this->extraData) : null;
    }

    public function setExtraData( array $data )
    {
        $this->extraData = is_array($data) ? json_encode($data) : null;
    }
}