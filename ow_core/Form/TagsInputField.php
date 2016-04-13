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

use Oxwall\Core\OW;
use Oxwall\Utilities\HtmlTag;

/**
 * Form element: CheckboxField.
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @since 1.8.3
 */
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

        $markup = HtmlTag::generateTag('input', $this->attributes);

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

        /** @var $value Validator  */
        foreach ( $this->validators as $value )
        {
            $js .= "formElement.addValidator(" . $value->getJsValidator() . ");";
        }

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
