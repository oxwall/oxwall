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
 * Base form element class.
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_core
 * @since 1.0
 */
abstract class FormElement
{
    const ATTR_DISABLED = 'disabled';
    const ATTR_CLASS = 'class';
    const ATTR_MAXLENGTH = 'maxlength';
    const ATTR_CHECKED = 'checked';
    const ATTR_READONLY = 'readonly';
    const ATTR_SIZE = 'size';
    const ATTR_SELECTED = 'selected';

    /**
     * Added validators.
     *
     * @var array
     */
    protected $validators = array();

    /**
     * Added filters
     * 
     * @var type 
     */
    protected $filters = array();

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
     * @throws InvalidArgumentException
     */
    public function __construct( $name )
    {
        if ( $name === null || !$name || strlen(trim($name)) === 0 )
        {
            throw new InvalidArgumentException('Invalid form element name!');
        }

        $this->setName($name);

        $this->setId(UTIL_HtmlTag::generateAutoId('input'));
    }

    /**
     * Returns form element ID.
     *
     * @return string
     */
    public function getId()
    {
        return isset($this->attributes['id']) ? $this->attributes['id'] : null;
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
            throw new InvalidArgumentException('Invalid form element id!');
        }

        $this->attributes['id'] = trim($id);
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
            throw new InvalidArgumentException('Invalid label was provided!');
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
     * @throws InvalidArgumentException
     */
    public function setDescription( $description )
    {
        if ( $description === null )
        {
            throw new InvalidArgumentException('Invalid form element description!');
        }

        $this->description = trim($description);
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return isset($this->attributes['name']) ? $this->attributes['name'] : null;
    }

    /**
     * @param string $name
     * @return FormElement
     */
    public function setName( $name )
    {
        if ( $name === null || strlen(trim($name)) === 0 )
        {
            throw new InvalidArgumentException('Form element invalid name!');
        }

        $this->attributes['name'] = trim($name);
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

        if ( $attrName == 'class' && isset($this->attributes['class']) )
        {
            $this->attributes['class'] = $this->attributes['class'] . ' ' . $attrValue;
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
            throw new InvalidArgumentException('Array is expected!');
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

    public function addFilter( OW_IFilter $filter )
    {
        $this->filters[] = $filter;
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
        if ( !$validator instanceof OW_Validator )
        {
            throw new InvalidArgumentException('Provided object is not instance of Validator class!');
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
            throw new InvalidArgumentException('Array is expected!');
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
        /* @var $filter OW_IFilter  */
        foreach ( $this->filters as $filter )
        {
            $value = $filter->filter($value);
        }

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
        $errors = '';

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

        return $jsString . $this->generateValidatorAndFilterJsCode("formElement");
    }

    /**
     * @param string $varName
     * @return string
     */
    protected function generateValidatorAndFilterJsCode( $varName )
    {
        $jsString = "";

        /** @var $value OW_Validator  */
        foreach ( $this->validators as $value )
        {
            $jsString .= "{$varName}.addValidator(" . $value->getJsValidator() . ");";
        }

        /** @var $filter OW_IFilter  */
        foreach ( $this->filters as $filter )
        {
            $jsString .= "{$varName}.addFilter(" . $filter->getJsFilter() . ");";
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

/**
 * Base invitation form element class.
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_core
 * @since 1.0
 */
abstract class InvitationFormElement extends FormElement
{
    /**
     * Invitation label.
     *
     * @var string
     */
    protected $invitation;

    /**
     * Invitation flag.
     *
     * @var boolean
     */
    protected $hasInvitation;

    /**
     * Constructor.
     */
    public function __construct( $name )
    {
        parent::__construct($name);
        $this->setHasInvitation(false);
        $this->setInvitation(OW::getLanguage()->text('base', 'form_element_common_invitation_text'));
    }

    /**
     * @return string
     */
    public function getInvitation()
    {
        return $this->invitation;
    }

    /**
     * @param string $invitation
     * @return Selectbox
     */
    public function setInvitation( $invitation )
    {
        $this->invitation = trim($invitation);

        return $this;
    }

    /**
     * @param boolean $hasInvitation
     */
    public function setHasInvitation( $hasInvitation )
    {
        $this->hasInvitation = (bool) $hasInvitation;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getHasInvitation()
    {
        return $this->hasInvitation;
    }

    public function renderInput( $params = null )
    {
        parent::renderInput($params);
    }
}

/**
 * Form element: TextField.
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_core
 * @since 1.0
 */
class TextField extends InvitationFormElement
{

    /**
     * Constructor.
     *
     * @param string $name
     */
    public function __construct( $name )
    {
        parent::__construct($name);

        $this->addAttribute('type', 'text');
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

        if ( $this->getValue() !== null )
        {
            $this->addAttribute('value', $this->value);
        }

        if ( $this->getHasInvitation() )
        {
            $this->addAttribute("placeholder", $this->getInvitation());
        }

        return UTIL_HtmlTag::generateTag('input', $this->attributes);
    }

    public function getElementJs()
    {
        $jsString = "var formElement = new OwTextField(" . json_encode($this->getId()) . ", " . json_encode($this->getName()) . ");";

        return $jsString.$this->generateValidatorAndFilterJsCode("formElement");
    }
}

/**
 * Form element: TextField.
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_core
 * @since 1.0
 */
class DateField extends FormElement
{
    const MIN_YEAR = 1900;

    protected $maxYear;
    protected $minYear;
    protected $defaultDate = array();
    protected $dateFormat = '';

    /**
     * Constructor.
     *
     * @param string $name
     */
    public function __construct( $name )
    {
        parent::__construct($name);

        $this->maxYear = ( (int) date("Y") - 18);

        $this->minYear = self::MIN_YEAR;

        $this->dateFormat = UTIL_DateTime::DEFAULT_DATE_FORMAT;

        $this->addAttribute('type', 'text');
    }

    /**
     * Sets form element value.
     *
     * @param mixed $value
     * @return FormElement
     */
    public function setValue( $value )
    {
        $date = UTIL_DateTime::parseDate($value, $this->dateFormat);

        if ( isset($date) )
        {
            $this->setDefaultDate($date['year'], $date['month'], $date['day']);
            $this->value = $value;
        }

        return $this;
    }

    public function getMinYear()
    {
        return $this->minYear;
    }

    public function getMaxYear()
    {
        return $this->maxYear;
    }

    public function getDefaultDate()
    {
        return $this->defaultDate;
    }

    public function getDateFormat()
    {
        return $this->dateFormat;
    }

    public function setMaxYear( $year )
    {
        $this->maxYear = (int) $year;
    }

    private function setDefaultDate( $year, $month, $day )
    {
        if ( UTIL_Validator::isDateValid((int) $month, (int) $day, (int) $year) )
        {
            $this->defaultDate['year'] = (int) $year;
            $this->defaultDate['month'] = (int) $month;
            $this->defaultDate['day'] = (int) $day;
        }
        else
        {
            throw new InvalidArgumentException('Invalid date!');
        }
    }

    public function setMinYear( $year )
    {
        $this->minYear = (int) $year;
    }

    public function setDateFormat( $format )
    {
        if ( empty($format) )
        {
            throw new InvalidArgumentException('Invalid argument `$format`!');
        }

        $this->dateFormat = $format;
    }

    public function renderInput( $params = null )
    {
        parent::renderInput($params);

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin("base")->getStaticJsUrl() . 'date_field.js');

        OW::getDocument()->addOnloadScript(" if( window.date_field == undefined ) { window.date_field = {}; } window.date_field['" . $this->getId() . "'] = new DateField( '" . ( $this->getName() ) . "' ); ");

        $language = OW::getLanguage();

        $yearOptionsString = UTIL_HtmlTag::generateTag('option', array('value' => ''), true, $language->text('base', 'year'));
        $mounthOptionsString = UTIL_HtmlTag::generateTag('option', array('value' => ''), true, $language->text('base', 'month'));
        $dayOptionsString = UTIL_HtmlTag::generateTag('option', array('value' => ''), true, $language->text('base', 'day'));

        for ( $i = $this->maxYear; $i >= $this->minYear; $i-- )
        {
            $attrs = (isset($this->defaultDate['year']) && (string) $i === (string) $this->defaultDate['year']) ? array(
                'selected' => 'selected') : array();

            $attrs['value'] = $i;

            $yearOptionsString .= UTIL_HtmlTag::generateTag('option', $attrs, true, trim($i));
        }

        for ( $i = 1; $i <= 12; $i++ )
        {
            $attrs = (isset($this->defaultDate['month']) && (string) $i === (string) $this->defaultDate['month']) ? array(
                'selected' => 'selected') : array();

            $attrs['value'] = $i;

            $mounthOptionsString .= UTIL_HtmlTag::generateTag('option', $attrs, true, $language->text('base', 'date_time_month_short_' . $i));
        }

        $lastDay = 31;

        if ( isset($this->defaultDate['month']) && isset($this->defaultDate['year']) )
        {
            $time = mktime(0, 0, 0, $this->defaultDate['month'], 1, $this->defaultDate['year']);
            $lastDay = date('d', strtotime('+1 month last day', $time));
        }

        for ( $i = 1; $i <= $lastDay; $i++ )
        {
            $attrs = (isset($this->defaultDate['day']) && (string) $i === (string) $this->defaultDate['day']) ? array('selected' => 'selected') : array();

            $attrs['value'] = $i;

            $dayOptionsString .= UTIL_HtmlTag::generateTag('option', $attrs, true, trim($i));
        }

        $attributes = array();
        $attributes['name'] = $this->attributes['name'];
        $attributes['id'] = $this->attributes['id'];
        $attributes['type'] = 'hidden';

        if ( !empty($this->defaultDate) )
        {
            $attributes['value'] = $this->defaultDate['year'] . '/' . $this->defaultDate['month'] . '/' . $this->defaultDate['day'];
        }

        $dayAttributes = $this->attributes;
        $dayAttributes['name'] = 'day_' . $this->getAttribute('name');

        if ( isset($dayAttributes['id']) )
        {
            unset($dayAttributes['id']);
        }

        $monthAttributes = $this->attributes;
        $monthAttributes['name'] = 'month_' . $this->getAttribute('name');

        if ( isset($monthAttributes['id']) )
        {
            unset($monthAttributes['id']);
        }

        $yearAttributes = $this->attributes;
        $yearAttributes['name'] = 'year_' . $this->getAttribute('name');

        if ( isset($yearAttributes['id']) )
        {
            unset($yearAttributes['id']);
        }

        $config = OW::getConfig()->getValue('base', 'date_field_format');

        $result = "";

        if ( $config === 'dmy' )
        {
            $result = '<div class="' . $this->getAttribute('name') . '">
                            ' . UTIL_HtmlTag::generateTag('input', $attributes) . '
                            <div class="ow_inline owm_inline">' . UTIL_HtmlTag::generateTag('select', $dayAttributes, true, $dayOptionsString) . '</div>
                            <div class="ow_inline owm_inline">' . UTIL_HtmlTag::generateTag('select', $monthAttributes, true, $mounthOptionsString) . '</div>
                            <div class="ow_inline owm_inline">' . UTIL_HtmlTag::generateTag('select', $yearAttributes, true, $yearOptionsString) . '</div>
                        </div>';
        }
        else
        {
            $result = '<div class="' . $this->getAttribute('name') . '">
                            ' . UTIL_HtmlTag::generateTag('input', $attributes) . '
                            <div class="ow_inline owm_inline">' . UTIL_HtmlTag::generateTag('select', $monthAttributes, true, $mounthOptionsString) . '</div>
                            <div class="ow_inline owm_inline">' . UTIL_HtmlTag::generateTag('select', $dayAttributes, true, $dayOptionsString) . '</div>
                            <div class="ow_inline owm_inline">' . UTIL_HtmlTag::generateTag('select', $yearAttributes, true, $yearOptionsString) . '</div>
                        </div>';
        }

        return $result;
    }
}

/**
 * Form element: Textarea.
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_core
 * @since 1.0
 */
class Textarea extends InvitationFormElement
{

    /**
     * Constructor.
     *
     * @param string $name
     */
    public function __construct( $name )
    {
        parent::__construct($name);
    }

    public function getElementJs()
    {
        $jsString = "var formElement = new OwTextArea(" . json_encode($this->getId()) . ", " . json_encode($this->getName()) . ");";

        return $jsString.$this->generateValidatorAndFilterJsCode("formElement");
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

        if ( $this->getValue() !== null )
        {
            $this->addAttribute('value', $this->getValue());
        }

        if ( $this->getHasInvitation() )
        {
            $this->addAttribute('placeholder', $this->getInvitation());
        }

        $content = $this->getAttribute('value');
        $this->removeAttribute('value');

        $markup = UTIL_HtmlTag::generateTag('textarea', $this->attributes, true, $content);

        return $markup;
    }
}

/**
 * Form element: Hidden.
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_core
 * @since 1.0
 */
class HiddenField extends FormElement
{

    /**
     * Constructor.
     *
     * @param string $name
     */
    public function __construct( $name )
    {
        parent::__construct($name);

        $this->addAttribute('type', 'hidden');
    }

    /**
     * @see FormElement::renderInput()
     *
     */
    public function renderInput( $params = null )
    {
        parent::renderInput($params);

        if ( $this->value !== null )
        {
            $this->addAttribute('value', $this->value);
        }

        return UTIL_HtmlTag::generateTag('input', $this->attributes);
    }
}

/**
 * Form element: Submit.
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_core
 * @since 1.0
 */
class Submit extends FormElement
{
    private $decorator;

    /**
     * Constructor.
     *
     * @param string $name
     */
    public function __construct( $name, $decorator = 'button' )
    {
        parent::__construct($name);

        $this->addAttribute('type', 'submit');

        $this->setValue(OW::getLanguage()->text('base', 'form_element_submit_default_value'));
        $this->decorator = $decorator;
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

        $this->addAttribute('value', $this->getValue());

        if ( $params === null )
        {
            $params = array();
        }

        $params = array_merge($params, $this->attributes);
        $params['label'] = $params['value'];

        $extraString = '';

        foreach ( $this->attributes as $attr => $val )
        {
            if ( !in_array($attr, array('value', 'class', 'id', 'buttonName', 'langLabel', 'label', 'type')) )
            {
                $extraString .= $attr . '="' . $val . '" ';
            }
        }

        $params['extraString'] = $extraString;

        if ( $this->decorator !== false )
        {
            $finalMarkup = OW::getThemeManager()->processDecorator($this->decorator, $params);
        }
        else
        {
            $finalMarkup = UTIL_HtmlTag::generateTag('input', $params);
        }

        return $finalMarkup;
    }
}

/**
 * Form element: Button.
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_core
 * @since 1.0
 */
class Button extends Submit
{

    public function __construct( $name )
    {
        parent::__construct($name);
        $this->addAttribute('type', 'button');
    }
}

/**
 * Form element: PasswordField.
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_core
 * @since 1.0
 */
class PasswordField extends TextField
{

    /**
     * Constructor.
     *
     * @param string $name
     */
    public function __construct( $name )
    {
        parent::__construct($name);

        $this->addAttribute('type', 'password');
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

        return UTIL_HtmlTag::generateTag('input', $this->attributes);
    }
}

/**
 * Form element: RadioField.
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_core
 * @since 1.0
 */
class RadioField extends FormElement
{
    /**
     * @var integer
     */
    protected $columnCount;

    /**
     * Input options.
     *
     * @var array
     */
    protected $options;

    /**
     * Constructor.
     *
     * @param string $name
     */
    public function __construct( $name )
    {
        parent::__construct($name);

        $this->addAttribute('type', 'radio');
        $this->columnCount = 1;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    public function getColumnCount()
    {
        return (int) $this->columnCount;
    }

    public function setColumnCount( $count )
    {
        $this->columnCount = (int) $count;
    }

    /**
     * Sets field options.
     *
     * @param array $options
     * @return RadioField
     * @throws InvalidArgumentException
     */
    public function setOptions( $options )
    {
        if ( $options === null || !is_array($options) )
        {
            throw new InvalidArgumentException('Array is expected!');
        }

        $this->options = $options;

        return $this;
    }

    /**
     * Adds field option.
     *
     * @param string $key
     * @param string$value
     * @return RadioField
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
     * @return RadioField
     */
    public function addOptions( $options )
    {
        if ( $options === null || !is_array($options) )
        {
            throw new InvalidArgumentException('Array is expected!');
        }

        foreach ( $options as $key => $value )
        {
            $this->addOption($key, $value);
        }

        return $this;
    }

    /**
     * @see FormElement::renderLabel()
     *
     * @return string
     */
    public function renderLabel()
    {
        return '<label>' . $this->getLabel() . '</label>';
    }

    /**
     * @see FormElement::getElementJs()
     */
    public function getElementJs()
    {
        $js = "var formElement = new OwRadioField(" . json_encode($this->getId()) . ", " . json_encode($this->getName()) . ");";

        return $js.$this->generateValidatorAndFilterJsCode("formElement");
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
            return '';
        }

        $renderedString = '<ul class="ow_radio_group clearfix">';

        $columnWidth = floor(100 / ($this->columnCount === 0 ? 1 : (int) $this->columnCount));

        foreach ( $this->options as $key => $value )
        {
            if ( $this->value !== null && (string) $key === (string) $this->value )
            {
                $this->addAttribute(FormElement::ATTR_CHECKED, 'checked');
            }

            $this->setId(UTIL_HtmlTag::generateAutoId('input'));

            $this->addAttribute('value', $key);

            $renderedString .= '<li style="width:' . $columnWidth . '%">' . UTIL_HtmlTag::generateTag('input', $this->attributes) . '&nbsp;<label for="' . $this->getId() . '">' . $value . '</label></li>';

            $this->removeAttribute(FormElement::ATTR_CHECKED);
        }

        return $renderedString . '</ul>';
    }
}

/**
 * Form element: CheckboxGroup.
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_core
 * @since 1.0
 */
class CheckboxGroup extends FormElement
{
    /**
     * @var unknown_type
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
        $this->addAttribute('type', 'checkbox');
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
            throw new InvalidArgumentException('Array is expected!');
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
            throw new InvalidArgumentException('Array is expected!');
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
        return isset($this->attributes['name']) ? mb_substr($this->attributes['name'], 0, -2) : null;
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
            throw new InvalidArgumentException('CheckboxGroup invalid name!');
        }

        $this->attributes['name'] = trim($name) . '[]';

        return $this;
    }

    /**
     * @see FormElement::renderLabel()
     *
     * @return string
     */
    public function renderLabel()
    {
        return '<label>' . $this->getLabel() . '</label>';
    }

    /**
     *  @see FormElement::getElementJs()
     */
    public function getElementJs()
    {
        $js = "var formElement = new OwCheckboxGroup(" . json_encode($this->getId()) . ", " . json_encode($this->getName()) . ");";

        return $js.$this->generateValidatorAndFilterJsCode("formElement");
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
            return '';
        }

        $columnWidth = floor(100 / $this->columnsCount);

        $renderedString = '<ul class="ow_checkbox_group clearfix">';

        foreach ( $this->options as $key => $value )
        {
            if ( $this->value !== null && is_array($this->value) && in_array($key, $this->value) )
            {
                $this->addAttribute(FormElement::ATTR_CHECKED, 'checked');
            }

            $this->setId(UTIL_HtmlTag::generateAutoId('input'));

            $this->addAttribute('value', $key);

            $renderedString .= '<li style="width:' . $columnWidth . '%">' . UTIL_HtmlTag::generateTag('input', $this->attributes) . '&nbsp;<label for="' . $this->getId() . '">' . $value . '</label></li>';

            $this->removeAttribute(FormElement::ATTR_CHECKED);
        }

        return $renderedString . '</ul>';
    }
}

/**
 * Form element: Selectbox.
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_core
 * @since 1.0
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

        $this->setInvitation(OW::getLanguage()->text('base', 'form_element_select_field_invitation_label'));
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
            throw new InvalidArgumentException('Array is expected!');
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
            throw new InvalidArgumentException('Array is expected!');
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

        $optionsString = '';

        if ( $this->hasInvitation )
        {
            $optionsString .= UTIL_HtmlTag::generateTag('option', array('value' => ''), true, $this->invitation);
        }

        foreach ( $this->options as $key => $value )
        {
            $attrs = ($this->value !== null && (string) $key === (string) $this->value) ? array('selected' => 'selected') : array();

            $attrs['value'] = $key;

            $optionsString .= UTIL_HtmlTag::generateTag('option', $attrs, true, trim($value));
        }

        return UTIL_HtmlTag::generateTag('select', $this->attributes, true, $optionsString);
    }
}

/**
 * Form element: CheckboxField.
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_core
 * @since 1.0
 */
class CheckboxField extends FormElement
{

    /**
     * Constructor.
     *
     * @param string $name
     */
    public function __construct( $name )
    {
        parent::__construct($name);

        $this->addAttribute('type', 'checkbox');
    }

    /**
     * @see FormElement::setValue()
     *
     * @param mixed $value
     * @return CheckboxField
     */
    public function setValue( $value )
    {
        if ( (bool) $value )
        {
            $this->value = true;
        }
        else
        {
            $this->value = null;
        }

        return $this;
    }

    /**
     *
     * @return string
     */
    public function getElementJs()
    {
        $jsString = "var formElement = new OwCheckboxField(" . json_encode($this->getId()) . ", " . json_encode($this->getName()) . ");";

        return $jsString.$this->generateValidatorAndFilterJsCode("formElement");
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

        if ( $this->value && $this->value === true )
        {
            $this->addAttribute(self::ATTR_CHECKED);
        }

        return UTIL_HtmlTag::generateTag('input', $this->attributes);
    }
}

/**
 * Form element: Multiselect.
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_core
 * @since 1.0
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
            throw new InvalidArgumentException();
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
            $optionsString .= UTIL_HtmlTag::generateTag('option', $attrs, true, trim($value));
        }

        $attrs = array('class' => 'choicesSelect', 'multiple' => null, 'size' => $this->size);

        $chicesSelectMarkup = UTIL_HtmlTag::generateTag('select', $attrs, true, $optionsString);

        $optionsString = '';

        foreach ( $selectedArray as $key => $value )
        {
            $attrs = array('value' => $key);
            $optionsString .= UTIL_HtmlTag::generateTag('option', $attrs, true, trim($value));
        }

        $attrs = array('class' => 'selectedSelect', 'multiple' => null, 'size' => $this->size);

        $selectedSelectMarkup = UTIL_HtmlTag::generateTag('select', $attrs, true, $optionsString);

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
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'multiselect_field.js');

        $jsString = "var formElement1 = new OwFormElement('" . $this->getId() . "', '" . $this->getName() . "');
                MultiselectField.prototype = formElement1;
                var formElement = new MultiselectField('" . $this->getId() . "', '" . $this->getName() . "');
                formElement.setValue(" . json_encode($this->value) . ");
        ";

        return $jsString.$this->generateValidatorAndFilterJsCode("formElement");
    }
}

class FileField extends FormElement
{

    /**
     * Constructor.
     *
     * @param string $name
     */
    public function __construct( $name )
    {
        parent::__construct($name);

        $this->addAttribute('type', 'file');
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

        return UTIL_HtmlTag::generateTag('input', $this->attributes);
    }
}

class SuggestField extends FormElement
{
    private $responderUrl;
    private $initialValue;
    private $initialLabel;
    private $minChars = 2;

    public function __construct( $name )
    {
        parent::__construct($name);
    }

    public function setResponderUrl( $responderUrl )
    {
        $this->responderUrl = json_encode($responderUrl);

        return $this;
    }

    public function setInitialLabel( $label )
    {
        $this->initialLabel = $label;

        return $this;
    }

    public function setInitialValue( $value )
    {
        $this->initialValue = $value;

        return $this;
    }

    public function setMinChars( $value )
    {
        $this->minChars = (int) $value;

        return $this;
    }

    public function renderInput( $params = null )
    {
        parent::renderInput($params);

        if ( $this->value !== null )
        {
            $this->addAttribute('value', $this->value);
            $this->addAttribute('class', 'ow_inputready');
        }

        $this->addAttribute('type', 'text');

        $scriptUrl = OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'jquery-suggest.js';
        OW::getDocument()->addScript($scriptUrl);

        $js = '
            var $input = $("#' . $this->getId() . '");
            var initialLabel = $input.val();
            $input.parent().find(".ow_suggest_invitation").click(function(){ $input.focus(); });
            $input.suggest(' . $this->responderUrl . ', {autoSuggest: true, minchars: ' . $this->minChars . ', onFocus: function(first){
                    $(this).removeClass("ow_inputready");
                    if ($(this).val() == initialLabel) {
                        $(this).val("");
                    }
                }, onBlur: function(){
                    var v = $(this).val();
                    
                    if ( !$.trim(v) ) {
                        $(this).val(initialLabel);
                        $(this).addClass("ow_inputready");
                        return 0;
                    }
                }, onAutoSuggest: function(v){
                    if (v) {
                        return false;
                    }
                }});';

        OW::getDocument()->addOnloadScript($js);

        return '<div class="ow_suggest_field">' . UTIL_HtmlTag::generateTag('input', $this->attributes) . '<div class="ow_suggest_invitation"></div></div>';
    }
}

class MultiFileField extends FormElement
{
    private $inputs;
    private $labels;

    /**
     * Constructor.
     *
     * @param string $name
     * @param int $inputs
     */
    public function __construct( $name, $inputs = 5, $labels = null )
    {
        parent::__construct($name);

        $this->inputs = $inputs;
        $this->labels = $labels;

        $this->addAttribute('type', 'file');
    }

    public function getValue()
    {
        return isset($_FILES[$this->getName()]) ? $_FILES[$this->getName()] : null;
    }

    public function getElementJs()
    {
        $js = "var formElement = new OwFormElement('" . $this->getId() . "', '" . $this->getName() . "');";

        $js .= $this->generateValidatorAndFilterJsCode("formElement");

        $js .= "
			formElement.getValue = function(){
			    
				var \$inputs = $(this.input.form[this.name + '[]']);

		        var values = [];

		        $.each( \$inputs,
		            function(index, data){
		                if( $(this).val() != '' )
		                {
		                    values.push($(this).val());
		                }
		            }
		        );
		        return values;
			};

			formElement.resetValue = function(){

		        var \$inputs = $(this.input.form[this.name + '[]']);

		        $.each( \$inputs,
		            function(index, data){
		                $(this).val('');
		            }
		        );
			};

			formElement.setValue = function(value){	};
		";

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

        $markup = '';

        for ( $i = 0; $i < $this->inputs; $i++ )
        {
            $label = isset($this->labels[$i]) ? $this->labels[$i] . ' ' : '';
            $this->setId(UTIL_HtmlTag::generateAutoId('input'));

            $markup .= $label . '<input type="file" id="' . $this->getId() . '" name="' . $this->getName() . '[]" /><br />';
        }

        return $markup;
    }
}

class TagsField extends FormElement
{
    private $tags;

    public function __construct( $name, $tags = array() )
    {
        parent::__construct($name);
        $this->tags = $tags;
        $this->setValue(implode(',', $this->tags) . '|sep|');
    }

    public static function getTags( $raw )
    {
        $arr = explode(',', str_replace('|sep|', '', $raw));
        $arr = is_array($arr) ? $arr : array();

        foreach ( $arr as $key => $value )
        {
            if ( strlen(trim($value)) > 0 )
                continue;
            unset($arr[$key]);
        }

        return $arr;
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

        if ( $this->value !== null )
        {
            $this->addAttribute('value', $this->value);
        }

        $markup = '';

        $tagTpl = '<div class="tags-field-tag-cont" style="margin: 2px 2px 0pt 0pt; padding: 2px 5px; background: rgb(204, 204, 204) none repeat scroll 0% 0%; float: left;"><span class="tags-field-tag">${tag}</span> <a class="ow_lbutton ow_red tags-field-del-tag">x</a></div>';

        $tagsMarkup = '';

        foreach ( $this->tags as $tag )
        {
            $tagsMarkup .= str_replace('${tag}', $tag, $tagTpl);
        }

        $id = $this->getId() . '-cont';

        $tpl = '<span id=${id}>' . '${hidden}' . '<input class="ow_text" type="text">' . '${tags}' . '</span>';

        $js = "if(window.tagsFields === undefined) window.tagsFields = {}; window.tagsFields['{$id}'] = new TagsField('{$id}');";

        $plugin = OW::getPluginManager()->getPlugin('base');

        OW::getDocument()->addScript($plugin->getStaticJsUrl() . 'tags_field.js');

        OW::getDocument()->addOnloadScript($js);

        $markup .= str_replace('${tags}', $tagsMarkup, $tpl);

        $markup = str_replace('${id}', $id, $markup);

        $this->addAttribute('type', 'hidden');

        $markup = str_replace('${hidden}', UTIL_HtmlTag::generateTag('input', $this->attributes), $markup);

        return $markup;
    }

    /**
     * @see FormElement::getElementJs()
     *
     * @return string
     */
    public function getElementJs()
    {
        $js = "var formElement = new OwFormElement('" . $this->getId() . "', '" . $this->getName() . "');";

        $js .= $this->generateValidatorAndFilterJsCode("formElement");

        $js .= "
			formElement.getValue = function(){

				var value = [];
				var i = 0;
			
			    $.each(  $('input:hidden[name='+ this.name +']', $(this.input.form) ).attr('value').split('|sep|'), function(){
			    	var val = $.trim(this);
			    	if(val.lentgth == 0) return;
			    	
			    	$.each( val.split(','), function(){
			    		var val_d2 = $.trim( this );
			    		if( val_d2.length == 0 ) return;
			
			    		value[i++] = val_d2;
			    	});
				});
				

			    return ( value == undefined ? '' : value );
			};

			formElement.resetValue = function(){
		        window.tagsFields['{$this->getId()}-cont'].reset();
		    };		
		";

        return $js;
    }
}

/**
 * Form element: TextField.
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow.core
 * @since 1.0
 */
class CaptchaField extends FormElement
{
    const CAPTCHA_PREFIX = 'ow_captcha_';

    public $jsObjectName = null;

    /**
     * Constructor.
     *
     * @param string $name
     */
    public function __construct( $name )
    {
        parent::__construct($name);

        $this->addAttribute('type', 'text');
        $this->jsObjectName = self::CAPTCHA_PREFIX . preg_replace('/[^\d^\w]/', '_', $this->getId());
        $this->setRequired();
        $this->addAttribute('style', 'width:100px;');
        $this->addValidator(new CaptchaValidator());
    }

    public function addValidator( $validator )
    {
        if ( $validator instanceof CaptchaValidator )
        {
            $validator->setJsObjectName($this->jsObjectName);
        }

        return parent::addValidator($validator);
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

        if ( $this->value !== null )
        {
            $this->addAttribute('value', str_replace('"', '&quot;', $this->value));
        }

        $captchaUrl = OW_URL_HOME . 'captcha.php';
        $captchaResponderUrl = OW::getRouter()->urlFor('BASE_CTRL_Captcha', 'ajaxResponder');
        $captchaClass = $this->getName() . '_' . $this->getId();
        $uniqueId = md5(time());

        $jsDir = OW::getPluginManager()->getPlugin("base")->getStaticJsUrl();
        OW::getDocument()->addScript($jsDir . "captcha.js");

        $string = ' window.' . $this->jsObjectName . ' = new OW_Captcha( ' . json_encode(array('captchaUrl' => $captchaUrl,
                'captchaClass' => $captchaClass,
                'captchaId' => $this->getId(),
                'responderUrl' => $captchaResponderUrl
            )) . ');';

        OW::getDocument()->addOnloadScript($string);

        return '<div class="' . $captchaClass . '">
                    <div class="ow_automargin clearfix" style="width: 230px;">
                            <div class="ow_left"><img src="' . $captchaUrl . '" id="siimage"></div>
                            <div class="ow_right" style="padding-top: 21px;"><span class="ic_refresh ow_automargin" id="siimage_refresh" style="cursor:pointer;"></span></div>
                    </div>
                    <div style="padding-top: 10px;">' . UTIL_HtmlTag::generateTag('input', $this->attributes) . '</div>
               </div>';
    }
}

class AgeRange extends FormElement implements DateRangeInterface
{
    const MIN_YEAR = 1900;

    protected $minAge;
    protected $maxAge;

    /**
     * Constructor.
     *
     * @param string $name
     */
    public function __construct( $name )
    {
        parent::__construct($name);

        $this->minAge = 18;

        $this->maxAge = (int) date("Y") - self::MIN_YEAR;
        $this->value = array();
    }

    /**
     * Sets form element value.
     *
     * @param array $value

     * @return FormElement
     */
    public function setValue( $value )
    {
        if ( !is_array($value) )
        {
            return;
        }

        if ( (int) $value['from'] >= $this->minAge && (int) $value['from'] <= $this->maxAge &&
            (int) $value['to'] >= $this->minAge && (int) $value['to'] <= $this->maxAge &&
            (int) $value['from'] <= (int) $value['to'] )
        {
            $this->value['from'] = (int) $value['from'];
            $this->value['to'] = (int) $value['to'];
        }
    }

    public function getMinAge()
    {
        return $this->minAge;
    }

    public function getMaxAge()
    {
        return $this->maxAge;
    }

    public function setMaxAge( $age )
    {
        $this->maxAge = (int) $age;

        return $this;
    }

    public function setMinAge( $age )
    {
        $this->minAge = (int) $age;

        return $this;
    }

    public function setMaxYear( $year )
    {
        $this->minAge = (int) date("Y") - (int) $year;

        return $this;
    }

    public function setMinYear( $year )
    {
        $this->maxAge = (int) date("Y") - (int) $year;

        return $this;
    }

    public function getMaxYear()
    {
        return (int) date("Y") - (int) $this->minAge;
    }

    public function getMinYear()
    {
        return (int) date("Y") - (int) $this->maxAge;
    }

    public function renderInput( $params = null )
    {
        parent::renderInput($params);

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin("base")->getStaticJsUrl() . 'age_range_field.js');
        OW::getDocument()->addOnloadScript(" window." . $this->getName() . " = new AgeRangeField( " . json_encode($this->getName()) . ", " . json_encode($this->minAge) . ", " . ( $this->maxAge ) . " ); ");

        $fromAgeOptionsString = ""; //UTIL_HtmlTag::generateTag('option', array('value' => ''), true);
        $toAgeOptionsString = ""; //UTIL_HtmlTag::generateTag('option', array('value' => ''), true);

        $defaultAgeFrom = isset($this->value['from']) ? (int) $this->value['from'] : $this->minAge;
        $defaultAgeTo = isset($this->value['to']) ? (int) $this->value['to'] : $this->maxAge;

        for ( $i = $this->minAge; $i <= $this->maxAge; $i++ )
        {
            $fromAgeAttrs = ((string) $i === (string) $defaultAgeFrom) ? array('selected' => 'selected') : array();
            $toAgeAttrs = ((string) $i === (string) $defaultAgeTo) ? array('selected' => 'selected') : array();

            $fromAgeAttrs['value'] = $i;
            $toAgeAttrs['value'] = $i;

            $fromAgeOptionsString .= UTIL_HtmlTag::generateTag('option', $fromAgeAttrs, true, trim($i));
            $toAgeOptionsString .= UTIL_HtmlTag::generateTag('option', $toAgeAttrs, true, trim($i));
        }

        $fromAgeAttrs = $this->attributes;
        $fromAgeAttrs['name'] = $this->getAttribute('name') . '[from]';

        if ( isset($fromAgeAttrs['id']) )
        {
            unset($fromAgeAttrs['id']);
        }

        $toAgeAttrs = $this->attributes;
        $toAgeAttrs['name'] = $this->getAttribute('name') . '[to]';

        if ( isset($toAgeAttrs['id']) )
        {
            unset($toAgeAttrs['id']);
        }

        $language = OW::getLanguage();

        $result = '<div id="' . $this->getAttribute('id') . '"class="' . $this->getAttribute('name') . '">
                        <div class="ow_range_from ow_inline ">' . $language->text('base', 'form_element_from') . '</div>
                        <div class="ow_inline">' . UTIL_HtmlTag::generateTag('select', $fromAgeAttrs, true, $fromAgeOptionsString) . '</div>
                        <div class="ow_range_to ow_inline">' . $language->text('base', 'form_element_to') . '</div>
                        <div class="ow_inline">' . UTIL_HtmlTag::generateTag('select', $toAgeAttrs, true, $toAgeOptionsString) . '</div>
                        <div class="ow_range_label ow_inline">' . $language->text('base', 'form_element_age_range') . '</div>
                    </div>';

        return $result;
    }

    /**
     * @see FormElement::getElementJs()
     *
     * @return string
     */
    public function getElementJs()
    {
        $js = "var formElement = new OwFormElement('" . $this->getId() . "', '" . $this->getName() . "');";

        $js .= $this->generateValidatorAndFilterJsCode("formElement");
        
        $js .= "
			formElement.getValue = function(){
				var value = {};
				value.from = $(this.input).find(\"select[name='\" + this.name + \"[from]']\").val();
				value.to = $(this.input).find(\"select[name='\" + this.name + \"[to]']\").val();

                return value;
			};
		";

        return $js;
    }
}

class MatchAgeRange extends AgeRange
{

    /**
     * Sets form element value.
     *
     * @param array $value

     * @return FormElement
     */
    public function setValue( $value )
    {
        if ( empty($value) )
        {
            $this->value = null;
        }

        if ( is_array($value) )
        {
            if ( empty($value) )
            {
                $this->value = '';
            }
            else
            {
                $this->value = $value['from'] . '-' . $value['to'];
            }
        }
        else
        {
            if ( empty($value) )
            {
                $this->value = array();
            }
            else
            {
                $valueArray = explode('-', $value);
                $value = array(
                    'from' => $valueArray[0],
                    'to' => $valueArray[1]
                );

                if ( (int) $value['from'] >= $this->minAge && (int) $value['from'] <= $this->maxAge &&
                    (int) $value['to'] >= $this->minAge && (int) $value['to'] <= $this->maxAge &&
                    (int) $value['from'] <= (int) $value['to'] )
                {
                    $this->value['from'] = (int) $value['from'];
                    $this->value['to'] = (int) $value['to'];
                }
            }
        }
    }

    public function getElementJs()
    {
        $js = "var formElement = new OwRange(" . json_encode($this->getId()) . ", " . json_encode($this->getName()) . ");";

        return $js.$this->generateValidatorAndFilterJsCode("formElement");
    }
}

class Range extends FormElement
{
    protected $minValue;
    protected $maxValue;

    /**
     * Constructor.
     *
     * @param string $name
     */
    public function __construct( $name )
    {
        parent::__construct($name);

        $this->minValue = 18;

        $this->maxValue = 100;
        $this->value = array();
    }
//    public function getValue()
//    {
//        return $this->minValue.'-'.$this->maxValue;
//    }

    /**
     * Sets form element value.
     *
     * @param array $value

     * @return FormElement
     */
    public function setValue( $value )
    {
        if ( empty($value) )
        {
            $this->value = null;
        }

        if ( is_array($value) )
        {
            if ( empty($value) )
            {
                $this->value = '';
            }
            else
            {
                $this->value = $value['from'] . '-' . $value['to'];
            }
        }
        else
        {
            if ( empty($value) )
            {
                $this->value = array();
            }
            else
            {
                $valueArray = explode('-', $value);
                $value = array(
                    'from' => $valueArray[0],
                    'to' => $valueArray[1]
                );

                if ( (int) $value['from'] >= $this->minValue && (int) $value['from'] <= $this->maxValue &&
                    (int) $value['to'] >= $this->minValue && (int) $value['to'] <= $this->maxValue &&
                    (int) $value['from'] <= (int) $value['to'] )
                {
                    $this->value['from'] = (int) $value['from'];
                    $this->value['to'] = (int) $value['to'];
                }
            }
        }
    }

    public function getMinValue()
    {
        return $this->maxValue;
    }

    public function getMaxValue()
    {
        return $this->minValue;
    }

    public function setMaxValue( $value )
    {
        $this->maxValue = (int) $value;

        return $this;
    }

    public function setMinValue( $value )
    {
        $this->minValue = (int) $value;

        return $this;
    }

    public function renderInput( $params = null )
    {
        parent::renderInput($params);

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin("base")->getStaticJsUrl() . 'range_field.js');
        OW::getDocument()->addOnloadScript(" window." . $this->getName() . " = new RangeField( '" . ( $this->getName() ) . "', " . ( $this->minValue ) . ", " . ( $this->maxValue ) . " ); ");

        $fromValueOptionsString = ""; //UTIL_HtmlTag::generateTag('option', array('value' => ''), true);
        $toValueOptionsString = ""; //UTIL_HtmlTag::generateTag('option', array('value' => ''), true);

        $defaultValueFrom = isset($this->value['from']) ? (int) $this->value['from'] : $this->minValue;
        $defaultValueTo = isset($this->value['to']) ? (int) $this->value['to'] : $this->maxValue;

        for ( $i = $this->minValue; $i <= $this->maxValue; $i++ )
        {
            $fromValueAttrs = ((string) $i === (string) $defaultValueFrom) ? array('selected' => 'selected') : array();
            $toValueAttrs = ((string) $i === (string) $defaultValueTo) ? array('selected' => 'selected') : array();

            $fromValueAttrs['value'] = $i;
            $toValueAttrs['value'] = $i;

            $fromValueOptionsString .= UTIL_HtmlTag::generateTag('option', $fromValueAttrs, true, trim($i));
            $toValueOptionsString .= UTIL_HtmlTag::generateTag('option', $toValueAttrs, true, trim($i));
        }

        $fromValueAttrs = $this->attributes;
        $fromValueAttrs['name'] = $this->getAttribute('name') . '[from]';

        if ( isset($fromValueAttrs['id']) )
        {
            unset($fromValueAttrs['id']);
        }

        $toValueAttrs = $this->attributes;
        $toValueAttrs['name'] = $this->getAttribute('name') . '[to]';

        if ( isset($toValueAttrs['id']) )
        {
            unset($toValueAttrs['id']);
        }

        $language = OW::getLanguage();

        $result = '<div id="' . $this->getAttribute('id') . '"class="' . $this->getAttribute('name') . '">
                        <div style="display:inline;padding-left:5px;padding-right:5px;">' . $language->text('base', 'form_element_from') . '</div>
                        <div style="display:inline;">' . UTIL_HtmlTag::generateTag('select', $fromValueAttrs, true, $fromValueOptionsString) . '</div>
                        <div style="display:inline;padding-left:5px;padding-right:5px;">' . $language->text('base', 'form_element_to') . '</div>
                        <div style="display:inline;">' . UTIL_HtmlTag::generateTag('select', $toValueAttrs, true, $toValueOptionsString) . '</div>
                        <div style="display:inline;padding-left:5px;">&nbsp;</div>
                    </div>';

        return $result;
    }

    public function getElementJs()
    {
        $js = "var formElement = new OwRange(" . json_encode($this->getId()) . ", " . json_encode($this->getName()) . ");";

        return $js.$this->generateValidatorAndFilterJsCode("formElement");
    }
}

class DateRange extends FormElement implements DateRangeInterface
{
    protected $minDate;
    protected $maxDate;
    protected $defaultRange = array();

    /**
     * Constructor.
     *
     * @param string $name
     */
    public function __construct( $name )
    {
        parent::__construct($name);

        $this->minDate = new DateField($name . '[from]');
        $this->maxDate = new DateField($name . '[to]');

        $this->minDate->setMaxYear(date("Y"));
        //$this->minDate->setValue($this->minDate->getMinYear().'/1/1');

        $this->maxDate->setMaxYear(date("Y"));
        //$this->maxDate->setValue(date("Y").'/12/31');
        /* @var $this->minDate = DateField */
    }

    /**
     * Sets form element value.
     *
     * @param array $value

     * @return FormElement
     */
    public function setValue( $value )
    {
        if ( isset($value['from']) && isset($value['to']) )
        {
            $this->minDate->setValue($value['from']);
            $this->maxDate->setValue($value['to']);
        }

        return $this;
    }

    public function getValue()
    {
        $value = array(
            'from' => $this->minDate->getValue(),
            'to' => $this->maxDate->getValue()
        );

        return $value;
    }

    public function getMinYear()
    {
        return $this->minDate->getMinYear();
    }

    public function getMaxYear()
    {
        return $this->minDate->getMaxYear();
    }

    public function setMaxYear( $year )
    {
        $this->minDate->setMaxYear($year);
        $this->maxDate->setMaxYear($year);

        return $this;
    }

    public function setMinYear( $year )
    {
        $this->minDate->setMinYear($year);
        $this->maxDate->setMinYear($year);

        return $this;
    }

    public function renderInput( $params = null )
    {
        parent::renderInput($params);

        $language = OW::getLanguage();

        $result = '<div id="' . $this->getAttribute('id') . '" class="' . $this->getAttribute('name') . '">
                       ' . $language->text('base', 'form_element_from') . '  <div class="ow_inline">' . ( $this->minDate->renderInput() ) . '</div>
                       ' . $language->text('base', 'form_element_to') . '
                       <div class="ow_inline">' . ( $this->maxDate->renderInput() ) . '</div>
                    </div>';

        return $result;
    }
}

class BillingGatewaySelectionField extends FormElement
{

    /**
     * Constructor.
     *
     * @param string $name
     */
    public function __construct( $name )
    {
        parent::__construct($name);
    }

    /**
     * @see FormElement::getElementJs()
     */
    public function getElementJs()
    {
        $js = "var formElement = new OwFormElement('" . $this->getId() . "', '" . $this->getName() . "');";

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

        $name = $this->getName();

        $gateways = $this->getActiveGatewaysList();

        if ( $gateways )
        {
            $paymentOptions = $this->getAdapterData($gateways);

            $gatewaysNumber = count($paymentOptions);

            $id = UTIL_HtmlTag::generateAutoId('input');

            $urlFieldAttrs = array(
                'type' => 'hidden',
                'id' => 'url-' . $id,
                'value' => '',
                'name' => $name . '[url]'
            );

            $renderedString = UTIL_HtmlTag::generateTag('input', $urlFieldAttrs);

            $cont_id = $id . '-cont';
            $renderedString .= '<ul class="ow_billing_gateways clearfix" id="' . $cont_id . '">';

            $i = 0;
            foreach ( $paymentOptions as $option )
            {
                $this->addAttributes(array(
                    'type' => 'radio',
                    'rel' => $option['orderUrl'],
                    'value' => $option['dto']->gatewayKey,
                    'name' => $name . '[key]'
                ));

                if ( $i == 0 )
                {
                    $url = $option['orderUrl'];
                    $this->addAttribute(self::ATTR_CHECKED, 'checked');
                }

                if ( $gatewaysNumber == 1 )
                {
                    $renderedString .= '<li style="display: inline-block; padding-right: 20px;">' . OW::getLanguage()->text('base', 'billing_pay_with') . '</li>';
                    $field = UTIL_HtmlTag::generateTag('input', array(
                            'type' => 'hidden',
                            'id' => 'url-' . $id,
                            'value' => $option['dto']->gatewayKey,
                            'name' => $name . '[key]'
                            )
                    );
                }
                else
                {
                    $field = UTIL_HtmlTag::generateTag('input', $this->attributes);
                }


                $renderedString .= $this->getItemMarkUp($option, $field);
                $i++;
                $this->removeAttribute(self::ATTR_CHECKED);
            }

            $renderedString .= '</ul>';

            $js = 'var $url_field = $("#url-' . $id . '");
                $url_field.val("' . $url . '");
                $("ul#' . $cont_id . ' input").change(function(){
                    $url_field.val($(this).attr("rel"));
                });';

            OW::getDocument()->addOnloadScript($js);
        }
        else
        {
            $renderedString = OW::getLanguage()->text('base', 'billing_no_gateways');
        }

        return $renderedString;
    }

    protected function getItemMarkUp( $option, $field )
    {
        return '<li style="display: inline-block;">
                    <label>' . $field . '<img src="' . $option['logoUrl'] . '" alt="' . $option['dto']->gatewayKey . '" /></label>
                </li>';
    }

    protected function getActiveGatewaysList()
    {
        return BOL_BillingService::getInstance()->getActiveGatewaysList();
    }

    protected function getAdapterData( $gateways )
    {
        $paymentOptions = array();

        foreach ( $gateways as $gateway )
        {
            /* @var $adapter OW_BillingAdapter */
            if ( $adapter = OW::getClassInstance($gateway->adapterClassName) )
            {
                $paymentOptions[$gateway->gatewayKey]['dto'] = $gateway;
                $paymentOptions[$gateway->gatewayKey]['orderUrl'] = $adapter->getOrderFormUrl();
                $paymentOptions[$gateway->gatewayKey]['logoUrl'] = $adapter->getLogoUrl();
            }
        }

        return $paymentOptions;
    }
}

class MobileBillingGatewaySelectionField extends BillingGatewaySelectionField
{

    protected function getItemMarkUp( $option, $field )
    {
        $name = str_replace('billing', '', $option['dto']->gatewayKey);

        return'<div class="owm_payment_provider_item owm_std_margin_bottom">
                <label class="owm_border owm_' . $name . ' active">' . $field . '</label>
        </div>';
    }

    protected function getActiveGatewaysList()
    {
        return BOL_BillingService::getInstance()->getActiveGatewaysList(true);
    }

    protected function getAdapterData( $gateways )
    {
        $paymentOptions = array();

        foreach ( $gateways as $gateway )
        {
            /* @var $adapter OW_BillingAdapter */
            if ( $adapter = OW::getClassInstance($gateway->adapterClassName) )
            {
                $paymentOptions[$gateway->gatewayKey]['dto'] = $gateway;
                $paymentOptions[$gateway->gatewayKey]['orderUrl'] = $adapter->getOrderFormUrl(true);
                $paymentOptions[$gateway->gatewayKey]['logoUrl'] = $adapter->getLogoUrl(true);
            }
        }

        return $paymentOptions;
    }
}

class YearRange extends FormElement implements DateRangeInterface
{
    const MIN_YEAR = 1800;
    const MAX_YEAR = 2100;

    protected $minYear;
    protected $maxYear;

    /**
     * Constructor.
     *
     * @param string $name
     */
    public function __construct( $name )
    {
        parent::__construct($name);

        $this->minYear = self::MIN_YEAR;
        $this->maxYear = self::MAX_YEAR;
        $this->value['from'] = 1930;
        $this->value['to'] = (int) date("Y") - 18;
    }

    /**
     * Sets form element value.
     *
     * @param array $value

     * @return FormElement
     */
    public function setValue( $value )
    {
        if ( empty($value) || empty($value['from']) || empty($value['to']) )
        {
            return;
        }

        if ( (int) $value['from'] >= $this->minYear && (int) $value['from'] <= $this->maxYear &&
            (int) $value['to'] >= $this->minYear && (int) $value['to'] <= $this->maxYear &&
            (int) $value['from'] <= (int) $value['to'] )
        {
            $this->value['from'] = (int) $value['from'];
            $this->value['to'] = (int) $value['to'];
        }
    }

    public function getElementJs()
    {
        $jsString = " var formElement = new AgeRangeFormElement(" . json_encode($this->getId()) . ", " . json_encode($this->getName()) . "); ";

        return $jsString.$this->generateValidatorAndFilterJsCode("formElement");
    }

    public function getMinAge()
    {
        return $this->maxYear;
    }

    public function getMaxAge()
    {
        return $this->minYear;
    }

    public function setMaxYear( $year )
    {
        $this->minYear = (int) $year;
    }

    public function setMinYear( $year )
    {
        $this->maxYear = (int) $year;
    }

    public function getMaxYear()
    {
        return $this->maxYear;
    }

    public function getMinYear()
    {
        return $this->minYear;
    }

    public function renderInput( $params = null )
    {
        parent::renderInput($params);

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin("base")->getStaticJsUrl() . 'age_range_field.js');
        OW::getDocument()->addOnloadScript(" window." . $this->getName() . " = new AgeRangeField( " . json_encode($this->getName()) . ", " . json_encode($this->minYear) . ", " . json_encode($this->maxYear) . " ); ");

        $fromAgeOptionsString = ""; //UTIL_HtmlTag::generateTag('option', array('value' => ''), true);
        $toAgeOptionsString = ""; //UTIL_HtmlTag::generateTag('option', array('value' => ''), true);

        $defaultYearFrom = isset($this->value['from']) ? (int) $this->value['from'] : $this->minYear;
        $defaultYearTo = isset($this->value['to']) ? (int) $this->value['to'] : $this->maxYear;

        for ( $i = $this->minYear; $i <= $this->maxYear; $i++ )
        {
            $fromYearAttrs = ((string) $i === (string) $defaultYearFrom) ? array('selected' => 'selected') : array();
            $toYearAttrs = ((string) $i === (string) $defaultYearTo) ? array('selected' => 'selected') : array();

            $attrs['value'] = $i;

            $fromAgeOptionsString .= UTIL_HtmlTag::generateTag('option', $fromYearAttrs, true, trim($i));
            $toAgeOptionsString .= UTIL_HtmlTag::generateTag('option', $toYearAttrs, true, trim($i));
        }

        $fromYearAttrs = $this->attributes;
        $fromYearAttrs['name'] = $this->getAttribute('name') . '[from]';

        if ( isset($fromYearAttrs['id']) )
        {
            unset($fromYearAttrs['id']);
        }

        $toYearAttrs = $this->attributes;
        $toYearAttrs['name'] = $this->getAttribute('name') . '[to]';

        if ( isset($toYearAttrs['id']) )
        {
            unset($toYearAttrs['id']);
        }

        $language = OW::getLanguage();

        $result = '<div id="' . $this->getAttribute('id') . '"class="' . $this->getAttribute('name') . '">
                        <div class="ow_range_from ow_inline">' . $language->text('base', 'form_element_from') . '</div>
                        <div class="ow_inline">' . UTIL_HtmlTag::generateTag('select', $fromYearAttrs, true, $fromAgeOptionsString) . '</div>
                        <div class="ow_range_to ow_inline">' . $language->text('base', 'form_element_to') . '</div>
                        <div class="ow_inline">' . UTIL_HtmlTag::generateTag('select', $toYearAttrs, true, $toAgeOptionsString) . '</div>
                        <div class="ow_range_label ow_inline">' . $language->text('base', 'form_element_year_range') . '</div>
                    </div>';

        return $result;
    }
}

/**
 * Form element: Textarea.
 *
 * @author Alex Ermashev <alexermashev@gmail.com>
 * @package ow_core
 * @since 1.0
 */
class MobileWysiwygTextarea extends Textarea
{
    /**
     * Plugin key
     * @var string
     */
    protected $pluginKey;

    /**
     * Text format service
     * @var BOL_TextFormatService
     */
    protected $textFormatService;

    /**
     * Buttons list
     *
     * @var array
     */
    private $buttons = array(
        BOL_TextFormatService::WS_BTN_BOLD,
        BOL_TextFormatService::WS_BTN_ITALIC,
        BOL_TextFormatService::WS_BTN_UNDERLINE,
        BOL_TextFormatService::WS_BTN_LINK,
        BOL_TextFormatService::WS_BTN_IMAGE,
        BOL_TextFormatService::WS_BTN_VIDEO
    );

    /**
     * Constructor.
     *
     * @param string $name
     * @param string $pluginKey
     * @param array $buttons
     */
    public function __construct( $name, $pluginKey = 'blog', array $buttons = array() )
    {
        parent::__construct($name);

        $this->pluginKey = $pluginKey;
        $this->textFormatService = BOL_TextFormatService::getInstance();

        // init list of buttons
        if ( !empty($buttons) )
        {
            $this->buttons = $buttons;
        }

        // remove image and video buttons
        if ( !$this->textFormatService->isRichMediaAllowed() )
        {
            $imageIndex = array_search(BOL_TextFormatService::WS_BTN_IMAGE, $this->buttons);

            if ( $imageIndex !== false )
            {
                unset($this->buttons[$imageIndex]);
            }

            $videoIndex = array_search(BOL_TextFormatService::WS_BTN_VIDEO, $this->buttons);

            if ( $videoIndex !== false )
            {
                unset($this->buttons[$videoIndex]);
            }
        }

        $stringValidator = new StringValidator(0, 50000);
        $stringValidator->setErrorMessage(OW::getLanguage()->text('base', 'text_is_too_long', array('max_symbols_count' => 50000)));

        $this->addValidator($stringValidator);
    }

    /**
     * @see FormElement::renderInput()
     *
     * @param array $params
     * @return string
     */
    public function renderInput( $params = null )
    {

        if ( OW::getRegistry()->get('baseWsInit') === null )
        {
            if ( in_array(BOL_TextFormatService::WS_BTN_IMAGE, $this->buttons) )
            {
                OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'jquery.html5_upload.js');
            }

            OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'suitup.jquery.js');
            OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('base')->getStaticCssUrl() . 'suitup.css');

            // register js langs
            OW::getLanguage()->addKeyForJs('base', 'ws_button_label_link');
            OW::getLanguage()->addKeyForJs('base', 'ws_button_label_video');
            OW::getLanguage()->addKeyForJs('base', 'ws_error_video');

            OW::getRegistry()->set('baseWsInit', true);
        }

        $this->addAttribute('class', 'owm_suitup_wyswyg');
        $js = UTIL_JsGenerator::newInstance();

        $js->addScript('$("#" + {$uniqId}).suitUp({$buttons}, {$imageUploadUrl}, {$embedUrl}).show();', array(
            'buttons' => $this->buttons,
            'uniqId' => $this->getId(),
            'imageUploadUrl' => OW::getRouter()->urlFor('BASE_CTRL_MediaPanel', 'ajaxUpload', array(
                'pluginKey' => $this->pluginKey
            )),
            'embedUrl' => OW::getRouter()->urlFor('BASE_MCTRL_Oembed', 'getAjaxEmbedCode')
        ));

        OW::getDocument()->addOnloadScript($js);

        return parent::renderInput($params);
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
            $this->addValidator(new WyswygRequiredValidator());
        }
        else
        {
            foreach ( $this->validators as $key => $validator )
            {
                if ( $validator instanceof WyswygRequiredValidator )
                {
                    unset($this->validators[$key]);
                    break;
                }
            }
        }

        return $this;
    }
}

/**
 * Form element: Textarea.
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_core
 * @since 1.0
 */
class WysiwygTextarea extends InvitationFormElement
{
    const SIZE_S = 100;
    const SIZE_M = 170;
    const SIZE_L = 300;

    /**
     * @var type
     */
    private $init;

    /**
     * @var array
     */
    private $buttons;

    /**
     * @var BOL_TextFormatService
     */
    private $service;

    /**
     *
     * @var string
     */
    private $size;

    /**
     * @var Textarea
     */
    private $textarea;

    /**
     * @var string
     */
    private $customBodyClass;

    /**
     * Constructor.
     *
     * @param string $name
     */
    public function __construct( $name, array $buttons = null, $init = true )
    {
        parent::__construct($name);

        $this->service = BOL_TextFormatService::getInstance();
        $this->init = (bool) $init;

        if ( !empty($buttons) )
        {
            $buttons = array_unique(array_merge($buttons, array(
                BOL_TextFormatService::WS_BTN_BOLD,
                BOL_TextFormatService::WS_BTN_ITALIC,
                BOL_TextFormatService::WS_BTN_UNDERLINE,
                BOL_TextFormatService::WS_BTN_LINK,
                BOL_TextFormatService::WS_BTN_ORDERED_LIST,
                BOL_TextFormatService::WS_BTN_UNORDERED_LIST,
            )));
        }
        else
        {
            $buttons = array(
                BOL_TextFormatService::WS_BTN_BOLD,
                BOL_TextFormatService::WS_BTN_ITALIC,
                BOL_TextFormatService::WS_BTN_UNDERLINE,
                BOL_TextFormatService::WS_BTN_LINK,
                BOL_TextFormatService::WS_BTN_ORDERED_LIST,
                BOL_TextFormatService::WS_BTN_UNORDERED_LIST,
            );
        }

        $this->buttons = $this->processButtons($buttons);
        $this->size = self::SIZE_M;

        if ( OW::getRequest()->isMobileUserAgent() )
        {
            $this->textarea = new Textarea($name);
        }

        $stringValidator = new StringValidator(0, 50000);
        $stringValidator->setErrorMessage(OW::getLanguage()->text('base', 'text_is_too_long', array('max_symbols_count' => 50000)));

        $this->addValidator($stringValidator);
    }

    /**
     * Returns current buttons set.
     *
     * @return array
     */
    public function getButtons()
    {
        return $this->buttons;
    }

    /**
     * @param integer $size
     */
    public function setSize( $size )
    {
        $this->size = $size;
    }

    /**
     * Adds custom buttons set.
     *
     * @param array $buttons
     */
    public function setButtons( array $buttons )
    {
        $this->buttons = $this->processButtons($buttons);
    }

    public function getElementJs()
    {
        if ( $this->textarea !== null )
        {
            return $this->textarea->getElementJs();
        }

        $invitation = $this->getHasInvitation() ? $this->getInvitation() : false;

        $jsString = "var formElement = new OwWysiwyg(" . json_encode($this->getId()) . ", " . json_encode($this->getName()) . ", " . json_encode($invitation) . ");            
        ";

        /** @var $value Validator  */
        foreach ( $this->validators as $value )
        {
            $jsString .= "formElement.addValidator(" . $value->getJsValidator() . ");";
        }

        return $jsString;
    }

    public function forceAddButtons( array $buttons = array() )
    {
        $this->buttons = array_merge($this->buttons, $buttons);
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

        if ( $this->textarea !== null )
        {
            return $this->textarea->renderInput();
        }

        if ( OW::getRegistry()->get('baseWsInit') === null )
        {
            $language = OW::getLanguage();
            $languageDto = BOL_LanguageService::getInstance()->getCurrent();

            $array = array(
                'editorCss' => OW::getPluginManager()->getPlugin('base')->getStaticCssUrl() . 'htmlarea_editor.css',
                'themeImagesUrl' => OW::getThemeManager()->getCurrentTheme()->getStaticImagesUrl(),
                'imagesUrl' => OW::getRouter()->urlFor('BASE_CTRL_MediaPanel', 'index', array('pluginKey' => 'blog', 'id' => '__id__')),
                'labels' => array(
                    'buttons' => array(
                        'bold' => $language->text('base', 'ws_button_label_bold'),
                        'italic' => $language->text('base', 'ws_button_label_italic'),
                        'underline' => $language->text('base', 'ws_button_label_underline'),
                        'orderedlist' => $language->text('base', 'ws_button_label_orderedlist'),
                        'unorderedlist' => $language->text('base', 'ws_button_label_unorderedlist'),
                        'link' => $language->text('base', 'ws_button_label_link'),
                        'image' => $language->text('base', 'ws_button_label_image'),
                        'video' => $language->text('base', 'ws_button_label_video'),
                        'html' => $language->text('base', 'ws_button_label_html'),
                        'more' => $language->text('base', 'ws_button_label_more'),
                        'switchHtml' => $language->text('base', 'ws_button_label_switch_html'),
                    ),
                    'common' => array(
                        'buttonAdd' => $language->text('base', 'ws_add_label'),
                        'buttonInsert' => $language->text('base', 'ws_insert_label'),
                        'videoHeadLabel' => $language->text('base', 'ws_video_head_label'),
                        'htmlHeadLabel' => $language->text('base', 'ws_html_head_label'),
                        'htmlTextareaLabel' => $language->text('base', 'ws_html_textarea_label'),
                        'videoTextareaLabel' => $language->text('base', 'ws_video_textarea_label'),
                        'linkTextLabel' => $language->text('base', 'ws_link_text_label'),
                        'linkUrlLabel' => $language->text('base', 'ws_link_url_label'),
                        'linkNewWindowLabel' => $language->text('base', 'ws_link_new_window_label'),
                    ),
                    'messages' => array(
                        'imagesEmptyFields' => $language->text('base', 'ws_image_empty_fields'),
                        'linkEmptyFields' => $language->text('base', 'ws_link_empty_fields'),
                        'videoEmptyField' => $language->text('base', 'ws_video_empty_field')
                    )
                ),
                'buttonCode' => OW::getThemeManager()->processDecorator('button', array('label' => '#label#', 'class' => 'ow_ic_add mn_submit')),
                'rtl' => ( ( $languageDto !== null && (bool) $languageDto->getRtl() ) ? true : false )
            );



            $script = "window.htmlAreaData = " . json_encode($array);
            OW::getDocument()->addScriptDeclarationBeforeIncludes($script);
            OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'htmlarea.js');
            OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('base')->getStaticCssUrl() . 'htmlarea.css');
            OW::getRegistry()->set('baseWsInit', true);
        }

        $params = array('toolbar' => $this->buttons, 'size' => $this->size);

        if ( !empty($this->customBodyClass) )
        {
            $params["customClass"] = $this->customBodyClass;
        }

        OW::getDocument()->addOnloadScript("
            $('#{$this->getId()}').get(0).htmlarea = function(){ $(this).htmlarea( " . json_encode($params) . " );};
            $('#{$this->getId()}').get(0).htmlareaFocus = function(){this.jhtmlareaObject.iframe[0].contentWindow.focus();};
            $('#{$this->getId()}').get(0).htmlareaRefresh = function(){if(this.jhtmlareaObject){this.jhtmlareaObject.dispose();$(this).htmlarea( " . json_encode($params) . " );}};
        ");

        if ( $this->value === null && $this->getHasInvitation() )
        {
            $this->addAttribute('value', $this->getInvitation());
            $this->addAttribute('class', 'invitation');
        }

        if ( $this->init )
        {
            OW::getDocument()->addOnloadScript("$('#{$this->getId()}').htmlarea( " . json_encode($params) . " );");
        }

        $this->removeAttribute('value');

        if ( $this->value === null && $this->getHasInvitation() )
        {
            $markup = UTIL_HtmlTag::generateTag('textarea', $this->attributes, true, $this->getInvitation());
        }
        else
        {
            $markup = UTIL_HtmlTag::generateTag('textarea', $this->attributes, true, htmlspecialchars(BOL_TextFormatService::getInstance()->processWsForInput($this->value, array('buttons' => $this->buttons))));
        }


        return $markup;
    }

    public function getValue()
    {
        if ( $this->textarea !== null )
        {
            return nl2br(htmlspecialchars($this->textarea->getValue()));
        }

        return BOL_TextFormatService::getInstance()->processWsForOutput($this->value, array('buttons' => $this->buttons));
    }

    public function setValue( $value )
    {
        if ( $this->textarea !== null )
        {
            return $this->textarea->setValue($value);
        }

        $this->value = $value;
    }

    private function processButtons( $buttons )
    {
        $keysToUnset = array();

        if ( in_array(BOL_TextFormatService::WS_BTN_HTML, $buttons) && !$this->service->isCustomHtmlAllowed() )
        {
            $keysToUnset[] = array_search(BOL_TextFormatService::WS_BTN_HTML, $buttons);
        }

        if ( !$this->service->isRichMediaAllowed() )
        {
            if ( in_array(BOL_TextFormatService::WS_BTN_VIDEO, $buttons) )
            {
                $keysToUnset[] = array_search(BOL_TextFormatService::WS_BTN_VIDEO, $buttons);
            }

            if ( in_array(BOL_TextFormatService::WS_BTN_IMAGE, $buttons) )
            {
                $keysToUnset[] = array_search(BOL_TextFormatService::WS_BTN_IMAGE, $buttons);
            }
        }

        foreach ( $keysToUnset as $key )
        {
            if ( !empty($buttons[$key]) )
            {
                unset($buttons[$key]);
            }
        }

        return array_values($buttons);
    }

    public function getCustomBodyClass()
    {
        return $this->customBodyClass;
    }

    public function setCustomBodyClass( $customBodyClass )
    {
        $this->customBodyClass = $customBodyClass;
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
            $this->addValidator(new WyswygRequiredValidator());
        }
        else
        {
            foreach ( $this->validators as $key => $validator )
            {
                if ( $validator instanceof WyswygRequiredValidator )
                {
                    unset($this->validators[$key]);
                    break;
                }
            }
        }

        return $this;
    }
}

class TagsInputField extends FormElement
{
    private $invLabel;
    private $delimiterChars;
    private $jsRegexp;
    private $minChars = 3;
    private $maxChars = 0;
    private $phpRegexp;

    public function __construct( $name )
    {
        parent::__construct($name);
        $this->value = array();
        $this->invLabel = OW::getLanguage()->text('base', 'tags_input_field_invitation');
        $this->delimiterChars = array('.');
    }

    public function setMinChars( $value )
    {
        $this->minChars = (int) $value;
    }

    public function setMaxChars( $value )
    {
        $this->minChars = (int) $value;
    }

    public function setInvitation( $label )
    {
        $this->invLabel = $label;
    }

    public function setDelimiterChars( array $chars )
    {
        $this->delimiterChars = $chars;
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

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'jquery.tagsinput.js');

        $this->addAttribute('value', $this->value ? implode(',', $this->value) : '');

        $markup = UTIL_HtmlTag::generateTag('input', $this->attributes);

        return $markup;
    }

    /**
     * @see FormElement::getElementJs()
     *
     * @return string
     */
    public function getElementJs()
    {
        $js = "
$('#" . $this->getId() . "').tagsInput({" . ( $this->jsRegexp ? "'regexp':" . $this->jsRegexp . "," : '' ) . "'pseudoDelimiter':" . json_encode($this->delimiterChars) . ", 'height':'auto', 'width':'auto', 'interactive':true, 'defaultText':'" . $this->invLabel . "', 'removeWithBackspace':true, 'minChars':" . $this->minChars . ", 'maxChars':" . $this->maxChars . ", 'placeholderColor':'#666666'});
var formElement = new OwFormElement('" . $this->getId() . "', '" . $this->getName() . "');";

        $js .= $this->generateValidatorAndFilterJsCode("formElement");

        $js .= "
            formElement.getValue = function(){
                if( !$(this.input).val() ){
                    return [];
                }
                return $(this.input).val().split(',');
            };

            formElement.setValue = function( vals ){
                this.resetValue();
                if( vals ){
                    $(this.input).importTags(vals.join(','));
                }
            };

            formElement.resetValue = function(){
                $(this.input).importTags('');
            };
		";

        if ( $this->value )
        {
            $js .= "$('#" . $this->getId() . "').importTags('" . str_replace("'", "", implode(',', $this->value)) . "');";
        }

        return $js;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue( $value )
    {
        if ( is_string($value) )
        {
            $this->value = explode(',', $value);
        }

        if ( is_array($value) )
        {
            $this->value = $value;
        }
    }

    public function setJsRegexp( $jsRegexp )
    {
        $this->jsRegexp = $jsRegexp;
    }

    public function setPhpRegexp( $phpRegexp )
    {
        $this->phpRegexp = $phpRegexp;
    }
}

interface DateRangeInterface
{

    public function getMinYear();

    public function getMaxYear();

    public function setMaxYear( $year );

    public function setMinYear( $year );
}

class CsrfHiddenField extends HiddenField
{
    /**
     * Returns form element JS.
     *
     * @return string
     */
    public function getElementJs()
    {
        $jsString = "
            var formElement = new OwFormElement(" . json_encode($this->getId()) . ", " . json_encode($this->getName()) . ");
            formElement.resetValue = function(){};
        ";

        return $jsString . $this->generateValidatorAndFilterJsCode("formElement");
    }
}