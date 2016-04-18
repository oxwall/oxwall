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
 * Form element: Multiselect.
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @since 1.8.3
 */
class Multiselect extends FormElement
{
    /**
     * Input options
     *
     * @var array
     */
    private $options;
    private $size;

    public function __construct( $name )
    {
        parent::__construct($name);

        $this->options = array();
        $this->value = array();
        $this->size = 10;
    }

    public function getSize()
    {
        return $this->size;
    }

    public function setSize( $size )
    {
        $this->size = (int) $size;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $options
     */
    public function setOptions( $options )
    {
        if ( is_null($options) || !is_array($options) )
        {
            throw new \InvalidArgumentException();
        }

        $this->options = $options;
    }

    /**
     * @param string $value
     * @param string $label
     */
    public function addOption( $value, $label )
    {
        $this->options[$value] = $label;
    }

    public function renderInput( $params = null )
    {
        parent::renderInput($params);

        if ( $this->value === null || !is_array($this->value) )
        {
            $this->value = array();
        }

        $choicesArray = array_diff_key($this->options, array_flip($this->value));
        $selectedArray = array_intersect_key($this->options, array_flip($this->value));

        $optionsString = '';

        foreach ( $choicesArray as $key => $value )
        {
            $attrs = array('value' => $key);
            $optionsString .= HtmlTag::generateTag('option', $attrs, true, trim($value));
        }

        $attrs = array('class' => 'choicesSelect', 'multiple' => null, 'size' => $this->size);

        $chicesSelectMarkup = HtmlTag::generateTag('select', $attrs, true, $optionsString);

        $optionsString = '';

        foreach ( $selectedArray as $key => $value )
        {
            $attrs = array('value' => $key);
            $optionsString .= HtmlTag::generateTag('option', $attrs, true, trim($value));
        }

        $attrs = array('class' => 'selectedSelect', 'multiple' => null, 'size' => $this->size);

        $selectedSelectMarkup = HtmlTag::generateTag('select', $attrs, true, $optionsString);

        return
            '<table class="ow_multiselect" id="' . $this->getId() . '">
                <tr>
                    <td>' . $chicesSelectMarkup . '</td>
                    <td><input type="button" class="select" value="" /><br /><br /><br /><input type="button" class="deselect ow_ic_left_arrow" value="" /></td>
                    <td>' . $selectedSelectMarkup . '</td>
                </tr>
            </table>';
    }

    public function getElementJs()
    {
        // TODO remake js in general style
        \Oxwall\Core\OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'multiselect_field.js');

        $jsString = "var formElement1 = new OwFormElement('" . $this->getId() . "', '" . $this->getName() . "');
                MultiselectField.prototype = formElement1;
                var formElement = new MultiselectField('" . $this->getId() . "', '" . $this->getName() . "');
                formElement.setValue(" . json_encode($this->value) . ");
        ";

        /** @var $value Validator  */
        foreach ( $this->validators as $value )
        {
            $jsString .= "formElement.addValidator(" . $value->getJsValidator() . ");";
        }

        return $jsString;
    }
}
