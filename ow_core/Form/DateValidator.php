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
use Oxwall\Utilities\DateTime;

/**
 * UrlValidator validates Url.
 *
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @since 1.8.3
 */
class DateValidator extends Validator
{
    /**
     * @var int
     */
    private $minYear;

    /**
     * @var int
     */
    private $maxYear;

    /**
     * @var string
     */
    private $dateFormat = \Oxwall\Utilities\DateTime::DEFAULT_DATE_FORMAT;

    /**
     * Class constructor
     *
     * @param int $min
     * @param int $max
     */
    public function __construct( $minYear = null, $maxYear = null )
    {

        if ( isset($minYear) )
        {
            $this->setMinYear($minYear);
        }

        if ( isset($maxYear) )
        {
            $this->setMaxYear($maxYear);
        }

        $errorMessage = OW::getLanguage()->text('base', 'form_validator_date_error_message');

        if ( empty($errorMessage) )
        {
            $errorMessage = 'Date Validator Error!';
        }

        $this->setErrorMessage($errorMessage);
    }

    public function setMaxYear( $maxYear )
    {
        $value = (int) $maxYear;

        if ( empty($value) )
        {
            throw new \InvalidArgumentException('Incorrect max year value!');
        }

        $this->maxYear = (int) $value;
    }

    public function setDateFormat( $dateFormat )
    {
        $format = trim($dateFormat);

        if ( empty($format) )
        {
            throw new \InvalidArgumentException('Incorrect argument `$format`!');
        }

        $this->dateFormat = trim($format);
    }

    public function setMinYear( $minYear )
    {
        $value = (int) $minYear;

        if ( empty($value) )
        {
            throw new \InvalidArgumentException('Incorrect min year value!');
        }

        $this->minYear = (int) $value;
    }

    public function isValid( $value )
    {
        // doesn't check empty values
        if ( (is_array($value) && sizeof($value) === 0) || $value === null || mb_strlen(trim($value)) === 0 )
        {
            return true;
        }

        if ( is_array($value) )
        {
            foreach ( $value as $val )
            {
                if ( !$this->checkValue($value) )
                {
                    return false;
                }
            }
            return true;
        }
        else
        {
            return $this->checkValue($value);
        }
    }

    public function checkValue( $value )
    {
        if ( $value === null || mb_strlen(trim($value)) === 0 )
        {
            return true;
        }

        $date = DateTime::parseDate($value, $this->dateFormat);

        if ( $date === null )
        {
            return false;
        }

        if ( !\Oxwall\Utilities\Validator::isDateValid($date[DateTime::PARSE_DATE_MONTH], $date[DateTime::PARSE_DATE_DAY],
                $date[DateTime::PARSE_DATE_YEAR]) )
        {
            return false;
        }

        if ( !empty($this->maxYear) && $date[DateTime::PARSE_DATE_YEAR] > $this->maxYear )
        {
            return false;
        }

        if ( !empty($this->minYear) && $date[DateTime::PARSE_DATE_YEAR] < $this->minYear )
        {
            return false;
        }

        return true;
    }

    public function getJsValidator()
    {
        return "{
        	validate : function( value ){
        },
        	getErrorMessage : function(){ return " . json_encode($this->getError()) . " }
        }";
    }
}
