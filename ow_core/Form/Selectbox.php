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

namespace Oxwall\Core\Form;

use Oxwall\Utilities\HtmlTag;

/**
 * Form element: Selectbox.
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @since 1.8.3
 */
class Selectbox extends InvitationFormElement
{
    /**
     * Input options.
     *
     * @var array
     */
    private $options = array();

    /**
     * Constructor.
     *
     * @param string $name
     */
    public function __construct( $name )
    {
        parent::__construct($name);

        $this->setInvitation(\Oxwall\Core\OW::getLanguage()->text("base", "form_element_select_field_invitation_label"));
        $this->setHasInvitation(true);
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Sets field options.
     *
     * @param array $options
     * @return Selectbox
     * @throws InvalidArgumentException
     */
    public function setOptions( $options )
    {
        if ( $options === null || !is_array($options) )
        {
            throw new \InvalidArgumentException("Array is expected!");
        }

        $this->options = $options;

        return $this;
    }

    /**
     * Adds input option.
     *
     * @param string $value
     * @param string $label
     * @return Selectbox
     */
    public function addOption( $key, $value )
    {
        $this->options[trim($key)] = trim($value);

        return $this;
    }

    /**
     * Adds input options list.
     *
     * @param array $options
     * @return Selectbox
     */
    public function addOptions( $options )
    {
        if ( $options === null || !is_array($options) )
        {
            throw new \InvalidArgumentException("Array is expected!");
        }

        foreach ( $options as $key => $value )
        {
            $this->addOption($key, $value);
        }

        return $this;
    }

    /**
     * @see FormElement::renderInput()
     *
     * @param array $params
     * @return string
     */
    public function renderInput( $params = null )
    {
        parent::renderInput($params);

        $optionsString = "";

        if ( $this->hasInvitation )
        {
            $optionsString .= HtmlTag::generateTag("option", array("value" => ""), true, $this->invitation);
        }

        foreach ( $this->options as $key => $value )
        {
            $attrs = ($this->value !== null && (string) $key === (string) $this->value) ? array("selected" => "selected") : array();

            $attrs["value"] = $key;

            $optionsString .= HtmlTag::generateTag("option", $attrs, true, trim($value));
        }

        return HtmlTag::generateTag("select", $this->attributes, true, $optionsString);
    }
}
