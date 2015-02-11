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
 * @author Aybat Duyshokov <duyshokov@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_MediaPanelFile extends OW_Entity
{
    public $plugin,
    $type,
    $userId,
    $data,
    $stamp;

    public function getPlugin()
    {
        return $this->plugin;
    }

    /**
     * 
     * @return BOL_MediaPanelFile
     */
    public function setPlugin( $plugin )
    {
        $this->plugin = $plugin;

        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    /**
     * 
     * @return BOL_MediaPanelFile
     */
    public function setType( $type )
    {
        $this->type = $type;

        return $this;
    }

    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * 
     * @return BOL_MediaPanelFile
     */
    public function setUserId( $userId )
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * 
     * @return BOL_MediaPanelFile
     */
    public function setData( $data )
    {
        $this->data = self::getDataJson($data);

        return $this;
    }

    public function getData()
    {
        $o = json_decode($this->data);

        return $o;
    }

    private static function getDataJson( $data )
    {
        if ( !count($data) )
        {
            return '{}';
        }

        ksort($data, SORT_STRING);

        return json_encode($data);
    }

    public function getStamp()
    {
        return $this->stamp;
    }

    /**
     * 
     * @return BOL_MediaPanelFile
     */
    public function setStamp( $stamp )
    {
        $this->stamp = $stamp;

        return $this;
    }
}