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
 * @since 1.0
 */
class BOL_Theme extends BOL_StoreItem
{
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
     * @return string
     */
    public function getCustomCss()
    {
        return $this->customCss;
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

    /**
     * @return string
     */
    public function getCustomCssFileName()
    {
        return $this->customCssFileName;
    }

    /**
     * @param string $customCssFileName
     * @return BOL_Theme
     */
    public function setCustomCssFileName( $customCssFileName )
    {
        $this->customCssFileName = $customCssFileName;

        return $this;
    }

    /**
     * @return string
     */
    public function getSidebarPosition()
    {
        return $this->sidebarPosition;
    }

    /**
     * @param string $sidebarPosition
     * @return BOL_Theme
     */
    public function setSidebarPosition( $sidebarPosition )
    {
        $this->sidebarPosition = $sidebarPosition;

        return $this;
    }

    /**
     * @return string
     */
    public function getMobileCustomCss()
    {
        return $this->mobileCustomCss;
    }

    /**
     * @param string $mobileCustomCss
     * @return BOL_Theme
     */
    public function setMobileCustomCss( $mobileCustomCss )
    {
        $this->mobileCustomCss = $mobileCustomCss;

        return $this;
    }

    /**
     * @deprecated since version 1.8.1
     * @return string
     */
    public function getName()
    {
        return $this->key;
    }

    /**
     * @deprecated since version 1.8.1
     * 
     * @param string $name
     * @return BOL_Theme
     */
    public function setName( $name )
    {
        $this->key = trim($name);
        return $this;
    }
}
