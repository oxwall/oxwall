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
     * Returns formatted date string for provided time stamp.
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
     * @param int $timestamp
     * @param bool $onlyDate
     * @return string
     */
    public static function formatSimpleDate( $timestamp, $onlyDate = false )
    {
        $militaryTime = (bool) OW::getConfig()->getValue('base', 'military_time');

        $currentDate = new DateTimeImmutable('now');
        $givenDate = (new DateTimeImmutable())->setTimestamp($timestamp);

        $fmt = self::getDateTimeFmt();

        if ($onlyDate) {
            return $currentDate->diff($givenDate)->y === 0
                ? self::formatDateByPattern($fmt, "LLL d", $givenDate->getTimestamp())
                : self::formatDateByPattern($fmt, "LLL d ''yy", $givenDate->getTimestamp());
        }

        if ($currentDate->diff($givenDate)->y === 0) {
            return $militaryTime
                ? self::formatDateByPattern($fmt, "LLL d, H:mm", $givenDate->getTimestamp())
                : self::formatDateByPattern($fmt, "LLL d, h:mm a", $givenDate->getTimestamp());

        }

        return $militaryTime
            ? self::formatDateByPattern($fmt, "LLL d ''y, H:mm", $givenDate->getTimestamp())
            : self::formatDateByPattern($fmt, "LLL d ''y, h:mm a", $givenDate->getTimestamp());
    }

    /**
     * Returns formatted date string/literal date string for provided time stamp.
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
     * @param int $timestamp
     * @param bool $onlyDate
     * @return string
     */

    public static function formatDate( $timestamp, $onlyDate = false )
    {
        if (!(bool) OW::getConfig()->getValue('base', 'site_use_relative_time')) {
            return self::formatSimpleDate($timestamp, $onlyDate);
        }

        $language = OW::getLanguage();

        $militaryTime = (bool) OW::getConfig()->getValue('base', 'military_time');

        $currentDate = new DateTimeImmutable('now');
        $givenDate = (new DateTimeImmutable())->setTimestamp($timestamp);

        $dateDiff = $currentDate->diff($givenDate);

        if ($dateDiff->days === 0) {
            if ($onlyDate) {
                return $language->text('base', 'date_time_today');
            }

            $secondsPast = $currentDate->getTimestamp() - $givenDate->getTimestamp();

            switch (true)
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
        } else if ($dateDiff->days === 1) {
            if ($onlyDate) {
                return $language->text('base', 'date_time_yesterday');
            }

            $fmt = self::getDateTimeFmt();

            $formattedDate = $militaryTime
                ? self::formatDateByPattern($fmt, "H:mm", $givenDate->getTimestamp())
                : self::formatDateByPattern($fmt, "h:mm a", $givenDate->getTimestamp());

            return $language->text('base', 'date_time_yesterday') . ', ' . $formattedDate;
        }

        return self::formatSimpleDate($timestamp, $onlyDate);
    }

    /**
     * Converts a date string to a timestamp.
     * @param string the date string to be parsed
     * @param string the pattern that the date string is following
     * @return bool|array for the date string. null if parsing fails.
     */
    public static function parseDate( $value, $pattern = self::DEFAULT_DATE_FORMAT )
    {
        $tokens = self::tokenize($pattern);
        $i = 0;
        $n = strlen($value ?? '');
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

        return [
            'minute' => $minute,
            'hour' => $hour,
            'second' => $second,
            'day' => $day,
            'month' => $month,
            'year' => $year
        ];
    }

    public static function getAge( $year, $month, $day )
    {
        $currentDate = new DateTimeImmutable('now');
        $givenDate = DateTimeImmutable::createFromFormat("Y-m-d", $year . '-' . $month . '-' . $day);

        return $currentDate->diff($givenDate)->y;
    }

    public static function formatBirthdate( $year, $month, $day )
    {
        $fmt = self::getDateTimeFmt();

        $format = OW::getConfig()->getValue('base', 'date_field_format');

        $givenDate = DateTimeImmutable::createFromFormat("Y-m-d", $year . '-' . $month . '-' . $day);

        return $format === 'dmy'
            ? self::formatDateByPattern($fmt, "d MMM", $givenDate->getTimestamp())
            : self::formatDateByPattern($fmt, "MMM d", $givenDate->getTimestamp());
    }

    public static function getTimezones()
    {
        $zones = DateTimeZone::listIdentifiers();
        return array_combine($zones, $zones);
    }

    /**
     * Format date by given pattern.
     *
     * @param IntlDateFormatter $fmt
     * @param string $pattern
     * @param int $timestamp
     * @return string
     */
    public static function formatDateByPattern($fmt, $pattern, $timestamp)
    {
        $fmt->setPattern($pattern);
        return datefmt_format($fmt, $timestamp);
    }

    /**
     * Create intl date formatter.
     *
     * @return IntlDateFormatter|null
     */
    public static function getDateTimeFmt()
    {
        $languageService = BOL_LanguageService::getInstance();

        return datefmt_create(
            $languageService->getCurrent()->getTag(),
            IntlDateFormatter::FULL,
            IntlDateFormatter::FULL,
            date_default_timezone_get(),
            IntlDateFormatter::GREGORIAN,
        );
    }

    protected static function parseInteger( $value, $offset, $minLength, $maxLength )
    {
        for ( $len = $maxLength; $len >= $minLength; --$len )
        {
            $v = substr($value ?? '', $offset, $len);
            if ( ctype_digit($v) )
                return $v;
        }

        return false;
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
}
