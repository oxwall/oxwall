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

            $fromValueOptionsString .= HtmlTag::generateTag('option', $fromValueAttrs, true, trim($i));
            $toValueOptionsString .= HtmlTag::generateTag('option', $toValueAttrs, true, trim($i));
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
                        <div style="display:inline;padding-left:5px;padding-right:5px;">' . $language->text('base',
                'form_element_from') . '</div>
                        <div style="display:inline;">' . HtmlTag::generateTag('select', $fromValueAttrs, true,
                $fromValueOptionsString) . '</div>
                        <div style="display:inline;padding-left:5px;padding-right:5px;">' . $language->text('base',
                'form_element_to') . '</div>
                        <div style="display:inline;">' . HtmlTag::generateTag('select', $toValueAttrs, true,
                $toValueOptionsString) . '</div>
                        <div style="display:inline;padding-left:5px;">&nbsp;</div>
                    </div>';

        return $result;
    }

    public function getElementJs()
    {
        $js = "var formElement = new OwRange(" . json_encode($this->getId()) . ", " . json_encode($this->getName()) . ");";

        /** @var $value Validator  */
        foreach ( $this->validators as $value )
        {
            $js .= "formElement.addValidator(" . $value->getJsValidator() . ");";
        }

        return $js;
    }
}
