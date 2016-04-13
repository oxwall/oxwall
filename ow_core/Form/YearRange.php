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

        /** @var $value Validator  */
        foreach ( $this->validators as $value )
        {
            $jsString .= "formElement.addValidator(" . $value->getJsValidator() . ");";
        }

        return $jsString;
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

            $fromAgeOptionsString .= HtmlTag::generateTag('option', $fromYearAttrs, true, trim($i));
            $toAgeOptionsString .= HtmlTag::generateTag('option', $toYearAttrs, true, trim($i));
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
                        <div class="ow_inline">' . HtmlTag::generateTag('select', $fromYearAttrs, true,
                $fromAgeOptionsString) . '</div>
                        <div class="ow_range_to ow_inline">' . $language->text('base', 'form_element_to') . '</div>
                        <div class="ow_inline">' . HtmlTag::generateTag('select', $toYearAttrs, true,
                $toAgeOptionsString) . '</div>
                        <div class="ow_range_label ow_inline">' . $language->text('base', 'form_element_year_range') . '</div>
                    </div>';

        return $result;
    }
}
