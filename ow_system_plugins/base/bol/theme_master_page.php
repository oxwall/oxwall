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
 * Data Transfer Object for `theme_master_page` table.  
 * 
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_ThemeMasterPage extends OW_Entity
{
    /**
     * @var integer
     */
    public $themeId;
    /**
     * @var string
     */
    public $documentKey;
    /**
     * @var string
     */
    public $masterPage;

    /**
     * @return string
     */
    public function getDocumentKey()
    {
        return $this->documentKey;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return (int) $this->id;
    }

    /**
     * @return string
     */
    public function getMasterPage()
    {
        return $this->masterPage;
    }

    /**
     * @return integer
     */
    public function getThemeId()
    {
        return (int) $this->themeId;
    }

    /**
     * @param string $documentKey
     * @return BOL_ThemeMasterPage
     */
    public function setDocumentKey( $documentKey )
    {
        $this->documentKey = trim($documentKey);
        return $this;
    }

    /**
     * @param string $masterPage
     * @return BOL_ThemeMasterPage
     */
    public function setMasterPage( $masterPage )
    {
        $this->masterPage = trim($masterPage);
        return $this;
    }

    /**
     * @param integer $themeId
     * @return BOL_ThemeMasterPage
     */
    public function setThemeId( $themeId )
    {
        $this->themeId = (int) $themeId;
        return $this;
    }
}

