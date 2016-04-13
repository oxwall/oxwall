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
 * Base form element class.
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @since 1.8.3
 */
abstract class FormElement
{
    const ATTR_DISABLED = "disabled";
    const ATTR_CLASS = "class";
    const ATTR_MAXLENGTH = "maxlength";
    const ATTR_CHECKED = "checked";
    const ATTR_READONLY = "readonly";
    const ATTR_SIZE = "size";
    const ATTR_SELECTED = "selected";

    /**
     * Added validators.
     *
     * @var array
     */
    protected $validators = array();

    /**
     * Required attribute flag.
     *
     * @var boolean
     */
    protected $required = false;

    /**
     * Form element value.
     *
     * @var mixed
     */
    protected $value;

    /**
     * Validator errors.
     *
     * @var array
     */
    protected $errors = array();

    /**
     * Form element label.
     *
     * @var string
     */
    protected $label;

    /**
     * Form element description.
     *
     * @var string
     */
    protected $description;

    /**
     * Form element attributes.
     *
     * @var array
     */
    protected $attributes = array();

    /**
     * Constructor.
     *
     * @param string $name
     * @throws \InvalidArgumentException
     */
    public function __construct( $name )
    {
        if ( $name === null || !$name || strlen(trim($name)) === 0 )
        {
            throw new \InvalidArgumentException("Invalid form element name!");
        }

        $this->setName($name);

        $this->setId(\Oxwall\Utilities\HtmlTag::generateAutoId("input"));
    }

    /**
     * Returns form element ID.
     *
     * @return string
     */
    public function getId()
    {
        return isset($this->attributes["id"]) ? $this->attributes["id"] : null;
    }

    /**
     * @param string $id
     * @return FormElement
     * @throws InvalidArgumentException
     */
    public function setId( $id )
    {
        if ( $id === null || strlen(trim($id)) === 0 )
        {
            throw new \InvalidArgumentException("Invalid form element id!");
        }

        $this->attributes["id"] = trim($id);
        return $this;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     * @return FormElement
     */
    public function setLabel( $label )
    {
        if ( $label === null )
        {
            throw new \InvalidArgumentException("Invalid label was provided!");
        }

        $this->label = trim($label);

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return FormElement
     * @throws \InvalidArgumentException
     */
    public function setDescription( $description )
    {
        if ( $description === null )
        {
            throw new \InvalidArgumentException("Invalid form element description!");
        }

        $this->description = trim($description);
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return isset($this->attributes["name"]) ? $this->attributes["name"] : null;
    }

    /**
     * @param string $name
     * @return FormElement
     */
    public function setName( $name )
    {
        if ( $name === null || strlen(trim($name)) === 0 )
        {
            throw new \InvalidArgumentException("Form element invalid name!");
        }

        $this->attributes["name"] = trim($name);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Sets additional form element attributes.
     *
     * @param string $attrName
     * @param string $attrValue
     * @return FormElement
     */
    public function addAttribute( $attrName, $attrValue = null )
    {
        $attrName = trim($attrName);

        if ( $attrName == "class" && isset($this->attributes["class"]) )
        {
            $this->attributes["class"] = $this->attributes["class"] . " " . $attrValue;
            return $this;
        }

        if ( $attrValue === null )
        {
            $this->attributes[$attrName] = trim($attrName);
        }
        else
        {
            $this->attributes[$attrName] = trim($attrValue);
        }

        return $this;
    }

    /**
     * @param string $attrName
     * @return mixed
     */
    public function getAttribute( $attrName )
    {
        $attrName = trim($attrName);

        if ( isset($this->attributes[$attrName]) )
        {
            return $this->attributes[$attrName];
        }

        return null;
    }

    /**
     * Adds form element attributes.
     *
     * @param array $attributes
     * @return FormElement
     * @throws InvalidArgumentException
     */
    public function addAttributes( $attributes )
    {
        if ( !is_array($attributes) )
        {
            throw new InvalidArgumentException("Array is expected!");
        }

        foreach ( $attributes as $name => $value )
        {
            $this->addAttribute($name, $value);
        }

        return $this;
    }

    /**
     * Removes form element attribute.
     *
     * @param string $attrName
     * @return FormElement
     */
    public function removeAttribute( $attrName )
    {
        if ( isset($this->attributes[trim($attrName)]) )
        {
            unset($this->attributes[trim($attrName)]);
        }

        return $this;
    }

    /**
     * Adds validator to form element
     *
     * @param mixed
     * @return FormElement
     * @throws InvalidArgumentException
     */
    public function addValidator( $validator )
    {
        if ( !$validator instanceof Validator )
        {
            throw new \InvalidArgumentException("Provided object is not instance of Validator class!");
        }

        $this->validators[] = $validator;

        return $this;
    }

    /**
     * Adds list of validators.
     *
     * @param array $validators
     * @return FormElement
     * @throws InvalidArgumentException
     */
    public function addValidators( $validators )
    {
        if ( !is_array($validators) )
        {
            throw new \InvalidArgumentException("Array is expected!");
        }

        foreach ( $validators as $value )
        {
            $this->addValidator($value);
        }

        return $this;
    }

    /**
     * Makes form element required.
     *
     * @param boolean $value
     * @return FormElement
     */
    public function setRequired( $value = true )
    {
        if ( $value )
        {
            $this->addValidator(new RequiredValidator());
        }
        else
        {
            foreach ( $this->validators as $key => $validator )
            {
                if ( $validator instanceof RequiredValidator )
                {
                    unset($this->validators[$key]);
                    break;
                }
            }
        }

        return $this;
    }

    /**
     * Sets form element value.
     *
     * @param mixed $value
     * @return FormElement
     */
    public function setValue( $value )
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Returns input label.
     *
     * @return string
     */
    public function renderLabel()
    {
        return '<label for="' . $this->getId() . '">' . $this->getLabel() . '</label>';
    }

    /**
     * Validates form element.
     *
     * @return boolean
     */
    public function isValid()
    {
        /* @var $value Validator  */
        foreach ( $this->validators as $value )
        {
            if ( $value->isValid($this->getValue()) )
            {
                continue;
            }

            $this->errors[] = $value->getError();
        }

        return empty($this->errors);
    }

    /**
     * Returns errors string.
     *
     * @return string
     */
    public function renderErrors()
    {
        $errors = "";

        foreach ( $this->errors as $error )
        {
            $errors .= $error;
        }

        return $errors;
    }

    /**
     * Returns errors array.
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Adds error message to form element.
     *
     * @param string $error
     * @return FormElement
     */
    public function addError( $error )
    {
        $this->errors[] = $error;
        return $this;
    }

    /**
     * Returns form element JS.
     *
     * @return string
     */
    public function getElementJs()
    {
        $jsString = "var formElement = new OwFormElement(" . json_encode($this->getId()) . ", " . json_encode($this->getName()) . ");";

        /** @var $value Validator  */
        foreach ( $this->validators as $value )
        {
            $jsString .= "formElement.addValidator({$value->getJsValidator()});";
        }

        return $jsString;
    }

    /**
     * Returns generated input html tag.
     *
     * @param array $params
     * @return string
     */
    protected function renderInput( $params = null )
    {
        if ( $params !== null )
        {
            $this->addAttributes($params);
        }
    }
}
