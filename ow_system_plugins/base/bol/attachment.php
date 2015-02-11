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
 * Data Transfer Object for `base_attachment` table.
 * 
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_Attachment extends OW_Entity
{
    /**
     * @var integer
     */
    public $userId;
    /**
     * @var ineteger
     */
    public $addStamp;
    /**
     * @var int
     */
    public $status;
    /**
     * @var string
     */
    public $fileName;
    /**
     * @var string
     */
    public $origFileName;
    /**
     * @var int
     */
    public $size;
    /**
     * @var string
     */
    public $bundle;
    /**
     * @var string
     */
    public $pluginKey;

    public function getUserId()
    {
        return (int) $this->userId;
    }

    public function setUserId( $userId )
    {
        $this->userId = (int) $userId;
    }

    public function getAddStamp()
    {
        return (int) $this->addStamp;
    }

    public function setAddStamp( $addStamp )
    {
        $this->addStamp = (int) $addStamp;
    }

    public function getStatus()
    {
        return (int) $this->status;
    }

    public function setStatus( $status )
    {
        $this->status = (int) $status;
    }

    public function getFileName()
    {
        return $this->fileName;
    }

    public function setFileName( $fileName )
    {
        $this->fileName = trim($fileName);
    }

    public function getSize()
    {
        return (int) $this->size;
    }

    public function setSize( $size )
    {
        $this->size = (int) $size;
    }

    public function getBundle()
    {
        return $this->bundle;
    }

    public function setBundle( $bundle )
    {
        $this->bundle = trim($bundle);
    }

    public function getOrigFileName()
    {
        return $this->origFileName;
    }

    public function setOrigFileName( $origFileName )
    {
        $this->origFileName = trim($origFileName);
    }

    public function getPluginKey()
    {
        return $this->pluginKey;
    }

    public function setPluginKey( $pluginKey )
    {
        $this->pluginKey = $pluginKey;
    }
}

