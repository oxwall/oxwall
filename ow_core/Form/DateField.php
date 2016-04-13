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

use Oxwall\Core\OW as OW;
use Oxwall\Utilities\HtmlTag;

/**
 * Form element: TextField.
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @since 1.8.3
 */
class DateField extends FormElement
{
    const MIN_YEAR = 1900;

    protected $maxYear;
    protected $minYear;
    protected $defaultDate = array();
    protected $dateFormat = "";

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

        $this->dateFormat = \Oxwall\Utilities\DateTime::DEFAULT_DATE_FORMAT;

        $this->addAttribute("type", "text");
    }

    /**
     * Sets form element value.
     *
     * @param mixed $value
     * @return FormElement
     */
    public function setValue( $value )
    {
        $date = \Oxwall\Utilities\DateTime::parseDate($value, $this->dateFormat);

        if ( isset($date) )
        {
            $this->setDefaultDate($date["year"], $date["month"], $date["day"]);
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
        if ( \Oxwall\Utilities\Validator::isDateValid((int) $month, (int) $day, (int) $year) )
        {
            $this->defaultDate["year"] = (int) $year;
            $this->defaultDate["month"] = (int) $month;
            $this->defaultDate["day"] = (int) $day;
        }
        else
        {
            throw new \InvalidArgumentException("Invalid date!");
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
            throw new \InvalidArgumentException("Invalid argument `{$format}`!");
        }

        $this->dateFormat = $format;
    }

    public function renderInput( $params = null )
    {
        parent::renderInput($params);

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin("base")->getStaticJsUrl() . "date_field.js");

        OW::getDocument()->addOnloadScript(" if( window.date_field == undefined ) { window.date_field = {}; } window.date_field['" . $this->getId() . "'] = new DateField( '" . ( $this->getName() ) . "' ); ");

        $language = OW::getLanguage();

        $yearOptionsString = HtmlTag::generateTag("option", array("value" => ""), true, $language->text("base", "year"));
        $mounthOptionsString = HtmlTag::generateTag("option", array("value" => ""), true,
                $language->text("base", "month"));
        $dayOptionsString = HtmlTag::generateTag("option", array("value" => ""), true, $language->text("base", "day"));

        for ( $i = $this->maxYear; $i >= $this->minYear; $i-- )
        {
            $attrs = (isset($this->defaultDate["year"]) && (string) $i === (string) $this->defaultDate["year"]) ? array(
                "selected" => "selected") : array();

            $attrs["value"] = $i;

            $yearOptionsString .= HtmlTag::generateTag("option", $attrs, true, trim($i));
        }

        for ( $i = 1; $i <= 12; $i++ )
        {
            $attrs = (isset($this->defaultDate["month"]) && (string) $i === (string) $this->defaultDate["month"]) ? array(
                "selected" => "selected") : array();

            $attrs["value"] = $i;

            $mounthOptionsString .= HtmlTag::generateTag("option", $attrs, true,
                    $language->text("base", "date_time_month_short_" . $i));
        }

        $lastDay = 31;

        if ( isset($this->defaultDate["month"]) && isset($this->defaultDate["year"]) )
        {
            $time = mktime(0, 0, 0, $this->defaultDate["month"], 1, $this->defaultDate["year"]);
            $lastDay = date("d", strtotime("+1 month last day", $time));
        }

        for ( $i = 1; $i <= $lastDay; $i++ )
        {
            $attrs = (isset($this->defaultDate["day"]) && (string) $i === (string) $this->defaultDate["day"]) ? array("selected" => "selected") : array();

            $attrs["value"] = $i;

            $dayOptionsString .= HtmlTag::generateTag("option", $attrs, true, trim($i));
        }

        $attributes = array();
        $attributes["name"] = $this->attributes["name"];
        $attributes["id"] = $this->attributes["id"];
        $attributes["type"] = "hidden";

        if ( !empty($this->defaultDate) )
        {
            $attributes["value"] = $this->defaultDate["year"] . "/" . $this->defaultDate["month"] . "/" . $this->defaultDate["day"];
        }

        $dayAttributes = $this->attributes;
        $dayAttributes["name"] = "day_" . $this->getAttribute("name");

        if ( isset($dayAttributes["id"]) )
        {
            unset($dayAttributes["id"]);
        }

        $monthAttributes = $this->attributes;
        $monthAttributes["name"] = "month_" . $this->getAttribute("name");

        if ( isset($monthAttributes["id"]) )
        {
            unset($monthAttributes["id"]);
        }

        $yearAttributes = $this->attributes;
        $yearAttributes["name"] = "year_" . $this->getAttribute("name");

        if ( isset($yearAttributes["id"]) )
        {
            unset($yearAttributes["id"]);
        }

        $config = OW::getConfig()->getValue("base", "date_field_format");

        $result = "";

        if ( $config === "dmy" )
        {
            $result = '<div class="' . $this->getAttribute("name") . '">
                            ' . HtmlTag::generateTag('input', $attributes) . '
                            <div class="ow_inline owm_inline">' . HtmlTag::generateTag('select', $dayAttributes, true,
                    $dayOptionsString) . '</div>
                            <div class="ow_inline owm_inline">' . HtmlTag::generateTag('select', $monthAttributes,
                    true, $mounthOptionsString) . '</div>
                            <div class="ow_inline owm_inline">' . HtmlTag::generateTag('select', $yearAttributes, true,
                    $yearOptionsString) . '</div>
                        </div>';
        }
        else
        {
            $result = '<div class="' . $this->getAttribute('name') . '">
                            ' . HtmlTag::generateTag('input', $attributes) . '
                            <div class="ow_inline owm_inline">' . HtmlTag::generateTag('select', $monthAttributes,
                    true, $mounthOptionsString) . '</div>
                            <div class="ow_inline owm_inline">' . HtmlTag::generateTag('select', $dayAttributes, true,
                    $dayOptionsString) . '</div>
                            <div class="ow_inline owm_inline">' . HtmlTag::generateTag('select', $yearAttributes, true,
                    $yearOptionsString) . '</div>
                        </div>';
        }

        return $result;
    }
}
