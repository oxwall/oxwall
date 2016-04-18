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
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @since 1.8.3
 */
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

            $fromAgeOptionsString .= HtmlTag::generateTag('option', $fromAgeAttrs, true, trim($i));
            $toAgeOptionsString .= HtmlTag::generateTag('option', $toAgeAttrs, true, trim($i));
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
                        <div class="ow_inline">' . HtmlTag::generateTag('select', $fromAgeAttrs, true,
                $fromAgeOptionsString) . '</div>
                        <div class="ow_range_to ow_inline">' . $language->text('base', 'form_element_to') . '</div>
                        <div class="ow_inline">' . HtmlTag::generateTag('select', $toAgeAttrs, true,
                $toAgeOptionsString) . '</div>
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
