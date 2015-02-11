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
 * Data Access Object for `base_geolocation_country` table.
 *
 * @author OW Team
 * @package ow_core
 * @since 1.0
 */
class OW_Entity
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var array
     */
    protected $_fieldsHash;

    /**
     * @return int
     */
    public function getId()
    {
        return (int) $this->id;
    }

    /**
     * @param int $id
     */
    public function setId( $id )
    {
        $this->id = (int) $id;

        return $this;
    }

    public function generateFieldsHash()
    {
        $this->_fieldsHash = array();
        $vars = get_object_vars($this);

        foreach ( $vars as $varName => $varValue )
        {
            if ( $varName != 'id' && !strstr($varName, '_fieldsHash') )
            {
                $this->_fieldsHash[$varName] = crc32($varValue);
            }
        }
    }

    public function getEntinyUpdatedFields()
    {
        $updatedFields = array();
        $vars = get_object_vars($this);
        
        foreach ( $vars as $varName => $varValue )
        {
            if ( !in_array($varName, array('_fieldsHash', 'id')) && (!isset($this->_fieldsHash[$varName]) || $this->_fieldsHash[$varName] !== crc32($varValue) ) )
            {
                $updatedFields[] = $varName;
            }
        }
        
        return $updatedFields;
    }
}
