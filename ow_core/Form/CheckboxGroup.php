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

/**
 * Form element: CheckboxGroup.
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @since 1.8.3
 */
class CheckboxGroup extends FormElement
{
    /**
     * @var int
     */
    protected $columnsCount;

    /**
     * Input options.
     *
     * @var array
     */
    protected $options = array();

    /**
     * Constructor.
     *
     * @param string $name
     */
    public function __construct( $name )
    {
        parent::__construct($name);

        $this->setName($name);
        $this->addAttribute("type", "checkbox");
        $this->columnsCount = 1;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    public function getColumnsCount()
    {
        return (int) $this->columnsCount;
    }

//TODO rename getter or setter
    public function setColumnCount( $count )
    {
        $this->columnsCount = (int) $count;
    }

    /**
     * Sets field options.
     *
     * @param array $options
     * @return CheckboxGroup
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
     * Adds field option.
     *
     * @param string $key
     * @param string$value
     * @return CheckboxGroup
     */
    public function addOption( $key, $value )
    {
        $this->options[trim($key)] = trim($value);

        return $this;
    }

    /**
     * Adds options list.
     *
     * @param array $options
     * @return CheckboxGroup
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
     * @see FormElement::getName()
     *
     * @return string
     */
    public function getName()
    {
        return isset($this->attributes["name"]) ? mb_substr($this->attributes["name"], 0, -2) : null;
    }

    /**
     * @see FormElement::setName()
     *
     * @param string $name
     * @return CheckboxGroup
     */
    public function setName( $name )
    {
        if ( $name === null || strlen(trim($name)) == 0 )
        {
            throw new \InvalidArgumentException("CheckboxGroup invalid name!");
        }

        $this->attributes["name"] = trim($name) . "[]";

        return $this;
    }

    /**
     * @see FormElement::renderLabel()
     *
     * @return string
     */
    public function renderLabel()
    {
        return "<label>{$this->getLabel()}</label>";
    }

    /**
     *  @see FormElement::getElementJs()
     */
    public function getElementJs()
    {
        $js = "var formElement = new OwCheckboxGroup(" . json_encode($this->getId()) . ", " . json_encode($this->getName()) . ");";

        /** @var $value Validator  */
        foreach ( $this->validators as $value )
        {
            $js .= "formElement.addValidator(" . $value->getJsValidator() . ");";
        }

        return $js;
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

        if ( $this->options === null || empty($this->options) )
        {
            return "";
        }

        $columnWidth = floor(100 / $this->columnsCount);

        $renderedString = '<ul class="ow_checkbox_group clearfix">';

        foreach ( $this->options as $key => $value )
        {
            if ( $this->value !== null && is_array($this->value) && in_array($key, $this->value) )
            {
                $this->addAttribute(FormElement::ATTR_CHECKED, 'checked');
            }

            $this->setId(\Oxwall\Utilities\HtmlTag::generateAutoId('input'));

            $this->addAttribute('value', $key);

            $renderedString .= '<li style="width:' . $columnWidth . '%">' . \Oxwall\Utilities\HtmlTag::generateTag('input',
                    $this->attributes) . '&nbsp;<label for="' . $this->getId() . '">' . $value . '</label></li>';

            $this->removeAttribute(FormElement::ATTR_CHECKED);
        }

        return $renderedString . '</ul>';
    }
}
