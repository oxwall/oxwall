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
 * Query building event
 * 
 * @author Sergey Kambalin <greyexpert@gmail.com>
 * @package ow_system_plugins.base.classes
 * @since 1.0
 */
class BASE_CLASS_QueryBuilderEvent extends OW_Event
{
    const FIELD_USER_ID = "userId";
    const FIELD_CONTENT_ID = "contentId";
    
    const TABLE_USER = "user";
    const TABLE_CONTENT = "content";
    
    const WHERE_AND = "where-and";
    const WHERE_OR = "where-or";
    
    const ORDER_ASC = "ASC";
    const ORDER_DESC = "DESC";
    
    const OPTION_TYPE = "type";
    const OPTION_METHOD = "method";
    
    public function __construct( $name, array $options = array() ) 
    {
        parent::__construct($name, $options);
        
        $this->data = array(
            "join" => array(),
            "where" => array(),
            "order" => array(),
            "params" => array()
        );
    }
    
    public function addJoin( $join )
    {
        $this->data["join"][] = $join;
    }
    
    public function getJoinList()
    {
        return $this->data["join"];
    }
    
    public function getJoin()
    {
        return implode(" ", $this->getJoinList());
    }
    
    public function addWhere( $condition )
    {
        $this->data["where"][] = $condition;
    }
    
    public function getWhereList()
    {
        return $this->data["where"];
    }
    
    public function getWhere( $type = self::WHERE_AND )
    {
        $whereList = $this->getWhereList();
        
        if ( empty($whereList) )
        {
            return "1";
        }
        
        return "(" . implode( $type == self::WHERE_AND ? ") AND (" : ") OR (", $this->getWhereList() ) . ")";
    }

    public function addOrder( $field, $order = self::ORDER_ASC )
    {
        $this->data["order"][$field] = $order;
    }

    public function getOrderList()
    {
        return $this->data["order"];
    }

    public function getOrder()
    {
        $orderList = $this->getOrderList();

        if ( empty($orderList) )
        {
            return "";
        }

        $sep = "";
        $orderStr = "";
        foreach ( $orderList as $field => $order )
        {
            $orderStr .= $sep . $field . " " . $order;
            $sep = ", ";
        }

        return $orderStr;
    }

    public function addQueryParam( $key, $value )
    {
        $this->data['params'][$key] = $value;
    }

    public function addBatchQueryParams( $params )
    {
        $this->data['params'] = array_merge($this->data['params'], $params);
    }

    public function getQueryParams()
    {
        return $this->data['params'];
    }
}