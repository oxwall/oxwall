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
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_utilities
 * @since 1.0
 */
class UTIL_DateTime
{
    const PARSE_DATE_HOUR = 'hour';
    const PARSE_DATE_MINUTE = 'minute';
    const PARSE_DATE_SECOND = 'second';
    const PARSE_DATE_DAY = 'day';
    const PARSE_DATE_MONTH = 'month';
    const PARSE_DATE_YEAR = 'year';
    const DEFAULT_DATE_FORMAT = 'yyyy/M/d';
    const MYSQL_DATETIME_DATE_FORMAT = 'yyyy-MM-dd hh:mm:ss';

    /**
     * Returns formated date string for provided time stamp.
     * 
     * Format:
     * 		`{month:str} {day:int} '{year:int}, {hours:int}:{minutes:int}[am/pm]`
     * 		`{month:str} {day:int} '{year:int} | $onlyDate = true
     * 
     * Samples:
     * 		`Jan 17 '09, 11:07[am]`
     * 		`Oct 25 '09, 08:09[pm]`
     * 		`Oct 25 '09 | $onlyDate = true
     *
     * @param integer $timeStamp
     * @param boolean $onlyDate
     * @return string
     */
    public static function formatSimpleDate( $timeStamp, $onlyDate = false )
    {
        $militaryTime = (bool) OW::getConfig()->getValue('base', 'military_time');

        $language = OW::getLanguage();

        if ( !$timeStamp )
        {
            return '_INVALID_TS_';
        }

        $month = $language->text('base', 'date_time_month_short_' . date('n', $timeStamp));

        //$at = $language->text('base', 'date_time_at_label');

        if ( $onlyDate )
        {
            return ( date('y', $timeStamp) === date('y', time()) ) ?
                $month . strftime(" %e", $timeStamp) :
                $month . strftime(" %e '%y", $timeStamp);
        }

        return ( date('y', $timeStamp) === date('y', time()) ) ?
            $month . ( $militaryTime ? strftime(" %e, %H:%M", $timeStamp) : strftime(" %e, %I:%M%p", $timeStamp) ) :
            $month . ( $militaryTime ? strftime(" %e '%y, %H:%M", $timeStamp) : strftime(" %e '%y, %I:%M%p", $timeStamp) );
    }
    /*     * p, $onlyDate = null )
     * Returns formated date string/literal date string for provided time stamp.
     *
     * Format:
     * 		`{literal|str}`
     * 		`{literal|str} {hours:int}:{minutes:int}[am/pm]`
     * 		`{month:str} {day:int} '{year:int} {hours:int}:{minutes:int}[am/pm]`
     *
     * Samples:
     * 		`within 1 minute`
     * 		`within 3 minutes`
     * 		`1 hour ago`
     * 		`5 hours ago`
     * 		`today` | $onlyDate = true
     * 		`yesterday, 15:48[am/pm]`
     * 		`yesterday` | $onlyDate = true
     * 		`Jan 17 '09, 11:07[am]`
     * 		`Oct 25 '09, 08:09[pm]`
     * 		`Oct 25 '09` | $onlyDate = true
     *
     * @param integer $timeStamp
     * @param boolean $onlyDate
     * @return string
     */

    public static function formatDate( $timeStamp, $onlyDate = null )
    {
        if ( !(bool) OW::getConfig()->getValue('base', 'site_use_relative_time') )
        {
            return self::formatSimpleDate($timeStamp, $onlyDate);
        }

        $language = OW::getLanguage();

        $militaryTime = (bool) OW::getConfig()->getValue('base', 'military_time');

        if ( !$timeStamp )
        {
            return '_INVALID_TS_';
        }

        $currentTs = time();

        if ( date('j', $timeStamp) === date('j', $currentTs) &&
            date('n', $timeStamp) === date('n', $currentTs) &&
            date('y', $timeStamp) === date('y', $currentTs)
        )
        {
            if ( $onlyDate )
            {
                return $language->text('base', 'date_time_today');
            }

            $secondsPast = $currentTs - $timeStamp;

            switch ( true )
            {
                case $secondsPast < 60:
                    return $language->text('base', 'date_time_within_one_minute');

                case $secondsPast < 120:
                    return $language->text('base', 'date_time_one_minute_ago');

                case $secondsPast < 3600:
                    return $language->text('base', 'date_time_minutes_ago', array('minutes' => floor($secondsPast / 60)));

                case $secondsPast < 7200:
                    return $language->text('base', 'date_time_one_hour_ago');

                default:
                    return $language->text('base', 'date_time_hours_ago', array('hours' => floor($secondsPast / 3600)));
            }
        }
        else if ( ( date('j', $currentTs) - date('j', $timeStamp) ) === 1 &&
            date('n', $currentTs) === date('n', $timeStamp) &&
            date('y', $currentTs) === date('y', $timeStamp)
        )
        {
            if ( $onlyDate )
            {
                return $language->text('base', 'date_time_yesterday');
            }

            return $language->text('base', 'date_time_yesterday') . ', ' . ( $militaryTime ? strftime("%H:%M", $timeStamp) : strftime("%I:%M%p", $timeStamp) );
        }

        if ( $onlyDate === null )
        {
            $onlyDate = true;
        }

        return self::formatSimpleDate($timeStamp, $onlyDate);
    }

    /**
     * Converts a date string to a timestamp.
     * @param string the date string to be parsed
     * @param string the pattern that the date string is following
     * @return DateTime for the date string. null if parsing fails.
     */
    public static function parseDate( $value, $pattern = self::DEFAULT_DATE_FORMAT )
    {
        $tokens = self::tokenize($pattern);
        $i = 0;
        $n = strlen($value);
        foreach ( $tokens as $token )
        {
            switch ( $token )
            {
                case 'yyyy':
                    {
                        if ( ($year = self::parseInteger($value, $i, 4, 4)) === false )
                            return null;
                        $i+=4;
                        break;
                    }
                case 'yy':
                    {
                        if ( ($year = self::parseInteger($value, $i, 1, 2)) === false )
                            return null;
                        $i+=strlen($year);
                        break;
                    }
                case 'MM':
                    {
                        if ( ($month = self::parseInteger($value, $i, 2, 2)) === false )
                            return null;
                        $i+=2;
                        break;
                    }
                case 'M':
                    {
                        if ( ($month = self::parseInteger($value, $i, 1, 2)) === false )
                            return null;
                        $i+=strlen($month);
                        break;
                    }
                case 'dd':
                    {
                        if ( ($day = self::parseInteger($value, $i, 2, 2)) === false )
                            return null;
                        $i+=2;
                        break;
                    }
                case 'd':
                    {
                        if ( ($day = self::parseInteger($value, $i, 1, 2)) === false )
                            return null;
                        $i+=strlen($day);
                        break;
                    }
                case 'h':
                case 'H':
                    {
                        if ( ($hour = self::parseInteger($value, $i, 1, 2)) === false )
                            return null;
                        $i+=strlen($hour);
                        break;
                    }
                case 'hh':
                case 'HH':
                    {
                        if ( ($hour = self::parseInteger($value, $i, 2, 2)) === false )
                            return null;
                        $i+=2;
                        break;
                    }
                case 'm':
                    {
                        if ( ($minute = self::parseInteger($value, $i, 1, 2)) === false )
                            return null;
                        $i+=strlen($minute);
                        break;
                    }
                case 'mm':
                    {
                        if ( ($minute = self::parseInteger($value, $i, 2, 2)) === false )
                            return null;
                        $i+=2;
                        break;
                    }
                case 's':
                    {
                        if ( ($second = self::parseInteger($value, $i, 1, 2)) === false )
                            return null;
                        $i+=strlen($second);
                        break;
                    }
                case 'ss':
                    {
                        if ( ($second = self::parseInteger($value, $i, 2, 2)) === false )
                            return null;
                        $i+=2;
                        break;
                    }
                default:
                    {
                        $tn = strlen($token);
                        if ( $i >= $n || substr($value, $i, $tn) !== $token )
                            return null;
                        $i+=$tn;
                        break;
                    }
            }
        }
        if ( $i < $n )
        {
            return false;
        }

        if ( !isset($year) || !isset($month) || !isset($day) )
        {
            return null;
        }

        if ( strlen($year) === 2 )
        {
            if ( $year > 70 )
                $year+=1900;
            else
                $year+=2000;
        }

        $year = (int) $year;
        $month = (int) $month;
        $day = (int) $day;

        if ( !isset($hour) && !isset($minute) && !isset($second) )
        {
            $hour = $minute = $second = 0;
        }
        else
        {
            if ( !isset($hour) )
            {
                $hour = 0;
            }

            if ( !isset($minute) )
            {
                $minute = 0;
            }

            if ( !isset($second) )
            {
                $second = 0;
            }

            $hour = (int) $hour;
            $minute = (int) $minute;
            $second = (int) $second;
        }

        return array(
            'minute' => $minute,
            'hour' => $hour,
            'second' => $second,
            'day' => $day,
            'month' => $month,
            'year' => $year);

//               $dateTime = new DateTime();
//               $dateTime->setDate( $year, $month, $day );
//               $dateTime->setTime( $hour, $minute, $second );
//
//               return $dateTime;
    }

    private static function tokenize( $pattern )
    {
        if ( !($n = strlen($pattern)) )
            return array();

        $tokens = array();
        for ( $c0 = $pattern[0], $start = 0, $i = 1; $i < $n; ++$i )
        {
            if ( ($c = $pattern[$i]) !== $c0 )
            {
                $tokens[] = substr($pattern, $start, $i - $start);
                $c0 = $c;
                $start = $i;
            }
        }

        $tokens[] = substr($pattern, $start, $n - $start);

        return $tokens;
    }

    protected static function parseInteger( $value, $offset, $minLength, $maxLength )
    {
        for ( $len = $maxLength; $len >= $minLength; --$len )
        {
            $v = substr($value, $offset, $len);
            if ( ctype_digit($v) )
                return $v;
        }

        return false;
    }

    public static function getAge( $year, $month, $day )
    {
        list($y, $m, $d ) = explode(':', date('Y:m:d', time()));

        $age = $y - $year;

        if ( $month > $m )
        {
            $age--;
        }
        else if ( ( $month == $m ) && ( $day > $d ) )
        {
            $age--;
        }

        return $age;
    }

    public static function formatBirthdate( $year, $month, $day )
    {
        $language = OW::getLanguage();

        $format = OW::getConfig()->getValue('base', 'date_field_format');

        $result = '';

        if ( $format === 'dmy' )
        {
            $result = date("d", mktime(0, 0, 0, $month, $day, $year)) . " " . $language->text('base', 'date_time_month_short_' . (int)$month) ;
        }
        else
        {
            $result = $language->text('base', 'date_time_month_short_' . (int)$month) . " " . date("d", mktime(0, 0, 0, $month, $day, $year));
        }

        return $result;
    }
}

