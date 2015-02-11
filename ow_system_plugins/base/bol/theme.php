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
 * Data Transfer Object for `theme` table.  
 * 
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_Theme extends OW_Entity
{
    /**
     * @var string
     */
    public $name;
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
     * @var boolean
     */
    public $isActive = 0;
    /**
     * @var string
     */
    public $customCss;
    /**
     * @var string
     */
    public $mobileCustomCss;
    /**
     * @var string
     */
    public $customCssFileName;
    /**
     * @var string
     */
    public $sidebarPosition;
    /**
     * @var integer
     */
    public $build = 0;
    /**
     * @var boolean
     */
    public $update = 0;
    /**
     * @var string
     */
    public $licenseKey;

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return (int) $this->id;
    }

    /**
     * @return boolean
     */
    public function getIsActive()
    {
        return (bool) $this->isActive;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getCustomCss()
    {
        return $this->customCss;
    }

    /**
     * @param string $description
     * @return BOL_Theme
     */
    public function setDescription( $description )
    {
        $this->description = trim($description);
        return $this;
    }

    /**
     * @param boolean $isActive
     * @return BOL_Theme
     */
    public function setIsActive( $isActive )
    {
        $this->isActive = (boolean) $isActive;
        return $this;
    }

    /**
     * @param string $name
     * @return BOL_Theme
     */
    public function setName( $name )
    {
        $this->name = trim($name);
        return $this;
    }

    /**
     * @param string $title
     * @return BOL_Theme
     */
    public function setTitle( $title )
    {
        $this->title = trim($title);
        return $this;
    }

    /**
     * @param string $css
     * @return BOL_Theme
     */
    public function setCustomCss( $css )
    {
        $this->customCss = trim($css);
        return $this;
    }

    public function getCustomCssFileName()
    {
        return $this->customCssFileName;
    }

    public function setCustomCssFileName( $customCssFileName )
    {
        $this->customCssFileName = $customCssFileName;
    }

    public function getSidebarPosition()
    {
        return $this->sidebarPosition;
    }

    public function setSidebarPosition( $sidebarPosition )
    {
        $this->sidebarPosition = $sidebarPosition;
    }

    public function getDeveloperKey()
    {
        return $this->developerKey;
    }

    public function setDeveloperKey( $developerKey )
    {
        $this->developerKey = $developerKey;
    }

    public function getBuild()
    {
        return $this->build;
    }

    public function setBuild( $build )
    {
        $this->build = $build;
    }

    public function getUpdate()
    {
        return $this->update;
    }

    public function setUpdate( $update )
    {
        $this->update = $update;
    }

    public function getLicenseKey()
    {
        return $this->licenseKey;
    }

    public function setLicenseKey( $licenseKey )
    {
        $this->licenseKey = $licenseKey;
    }

    public function getMobileCustomCss()
    {
        return $this->mobileCustomCss;
    }

    public function setMobileCustomCss( $mobileCustomCss )
    {
        $this->mobileCustomCss = $mobileCustomCss;
    }
}
