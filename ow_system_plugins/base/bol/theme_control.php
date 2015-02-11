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
 * Data Transfer Object for `base_theme_control` table.
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_ThemeControl extends OW_Entity
{
    /**
     * @var string
     */
    public $attribute;
    /**
     * @var string
     */
    public $selector;
    /**
     * @var mixed
     */
    public $defaultValue;
    /**
     * @var string
     */
    public $type;
    /**
     * @var integer
     */
    public $themeId;
    /**
     * @var string
     */
    public $key;
    /**
     * @var string
     */
    public $section;
    /**
     * @var string
     */
    public $label;
    /**
     * @var string
     */
    public $description;
    /**
     * @var boolean
     */
    public $mobile;

    public function getAttribute()
    {
        return $this->attribute;
    }

    public function getSelector()
    {
        return $this->selector;
    }

    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getThemeId()
    {
        return $this->themeId;
    }

    public function getSection()
    {
        return $this->section;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription( $description )
    {
        $this->description = trim($description);
    }

    /**
     * @return string $label
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return string $key
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $label
     */
    public function setLabel( $label )
    {
        $this->label = $label;
    }

    /**
     * @param string $key
     */
    public function setKey( $key )
    {
        $this->key = $key;
    }

    /**
     * @param string $attribute
     * @return BOL_ThemeControl
     */
    public function setAttribute( $attribute )
    {
        $this->attribute = $attribute;
        return $this;
    }

    /**
     * @param string $selector
     * @return BOL_ThemeControl
     */
    public function setSelector( $selector )
    {
        $this->selector = $selector;
        return $this;
    }

    /**
     * @param string $defaultValue
     * @return BOL_ThemeControl
     */
    public function setDefaultValue( $defaultValue )
    {
        $this->defaultValue = $defaultValue;
        return $this;
    }

    /**
     *
     * @param string $type
     * @return BOL_ThemeControl
     */
    public function setType( $type )
    {
        $this->type = $type;
        return $this;
    }

    /**
     *
     * @param integer $themeId
     * @return BOL_ThemeControl
     */
    public function setThemeId( $themeId )
    {
        $this->themeId = $themeId;
        return $this;
    }

    /**
     * @param string $section
     * @return BOL_ThemeControl
     */
    public function setSection( $section )
    {
        $this->section = $section;
        return $this;
    }

    /**
     * @return bool
     */
    public function getMobile()
    {
        return (bool) $this->mobile;
    }

    /**
     * @param bool $mobile
     */
    public function setMobile( $mobile )
    {
        $this->mobile = (bool) $mobile;
    }
}