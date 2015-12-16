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
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.8.1
 */
abstract class BOL_StoreItem extends OW_Entity
{
    /**
     * @var string
     */
    public $key;

    /**
     * @var string
     */
    public $developerKey;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $description;

    /**
     * @var string
     */
    public $licenseKey;

    /**
     * @var int
     */
    public $licenseCheckTimestamp;

    /**
     * @var integer
     */
    public $build = 0;

    /**
     * @var boolean
     */
    public $update = 0;

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return (int) $this->id;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $description
     * @return BOL_StoreItem
     */
    public function setDescription( $description )
    {
        $this->description = trim($description);

        return $this;
    }

    /**
     * @param string $key
     * @return BOL_StoreItem
     */
    public function setKey( $key )
    {
        $this->key = trim($key);

        return $this;
    }

    /**
     * @param string $title
     * @return BOL_StoreItem
     */
    public function setTitle( $title )
    {
        $this->title = trim($title);

        return $this;
    }

    /**
     * @return int
     */
    public function getBuild()
    {
        return $this->build;
    }

    /**
     * @param int $build
     * @return BOL_StoreItem
     */
    public function setBuild( $build )
    {
        $this->build = (int) $build;

        return $this;
    }

    /**
     * @return int
     */
    public function getUpdate()
    {
        return $this->update;
    }

    /**
     * @param int $update
     * @return BOL_StoreItem
     */
    public function setUpdate( $update )
    {
        $this->update = (int) $update;

        return $this;
    }

    /**
     * @return string
     */
    public function getLicenseKey()
    {
        return $this->licenseKey;
    }

    /**
     * @param string $licenseKey
     * @return BOL_StoreItem
     */
    public function setLicenseKey( $licenseKey )
    {
        $this->licenseKey = $licenseKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getDeveloperKey()
    {
        return $this->developerKey;
    }

    /**
     * @param string $developerKey
     * @return BOL_StoreItem
     */
    public function setDeveloperKey( $developerKey )
    {
        $this->developerKey = $developerKey;

        return $this;
    }

    /**
     * @return int
     */
    public function getLicenseCheckTimestamp()
    {
        return $this->licenseCheckTimestamp;
    }

    /**
     * @param int $licenseCheckTimestamp
     * @return BOL_StoreItem
     */
    public function setLicenseCheckTimestamp( $licenseCheckTimestamp )
    {
        $this->licenseCheckTimestamp = $licenseCheckTimestamp;

        return $this;
    }
}
