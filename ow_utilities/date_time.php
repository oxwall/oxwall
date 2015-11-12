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

    public static function getTimezones()
    {
        $zones = array(
            "Australia/West", "Australia/Victoria", "Australia/Tasmania", "Australia/Sydney", "Australia/South",
            "Australia/Queensland", "Australia/Perth", "Australia/North", "Australia/NSW", "Australia/Melbourne",
            "Australia/Lord_Howe", "Australia/Lindeman", "Australia/LHI", "Australia/Hobart", "Australia/Darwin",
            "Australia/Currie", "Australia/Canberra", "Australia/Broken_Hill", "Australia/Brisbane", "Australia/Adelaide",
            "Australia/ACT", "Atlantic/Stanley", "Atlantic/St_Helena", "Atlantic/South_Georgia", "Atlantic/Reykjavik",
            "Atlantic/Madeira", "Atlantic/Jan_Mayen", "Atlantic/Faeroe", "Atlantic/Cape_Verde", "Atlantic/Canary",
            "Atlantic/Bermuda", "Atlantic/Azores", "Asia/Yerevan", "Asia/Yekaterinburg", "Asia/Yakutsk",
            "Asia/Vladivostok", "Asia/Vientiane", "Asia/Urumqi", "Asia/Ulan_Bator", "Asia/Ulaanbaatar",
            "Asia/Ujung_Pandang", "Asia/Tokyo", "Asia/Thimphu", "Asia/Thimbu", "Asia/Tel_Aviv",
            "Asia/Tehran", "Asia/Tbilisi", "Asia/Tashkent", "Asia/Taipei", "Asia/Singapore",
            "Asia/Shanghai", "Asia/Seoul", "Asia/Samarkand", "Asia/Sakhalin", "Asia/Saigon",
            "Asia/Riyadh", "Asia/Rangoon", "Asia/Qyzylorda", "Asia/Qatar", "Asia/Pyongyang",
            "Asia/Pontianak", "Asia/Phnom_Penh", "Asia/Oral", "Asia/Omsk", "Asia/Novosibirsk",
            "Asia/Nicosia", "Asia/Muscat", "Asia/Manila", "Asia/Makassar", "Asia/Magadan",
            "Asia/Macau", "Asia/Macao", "Asia/Kuwait", "Asia/Kuching", "Asia/Kuala_Lumpur",
            "Asia/Krasnoyarsk", "Asia/Katmandu", "Asia/Kashgar", "Asia/Karachi", "Asia/Kamchatka",
            "Asia/Kabul", "Asia/Jerusalem", "Asia/Jayapura", "Asia/Jakarta", "Asia/Istanbul",
            "Asia/Irkutsk", "Asia/Hovd", "Asia/Hong_Kong", "Asia/Harbin", "Asia/Gaza",
            "Asia/Dushanbe", "Asia/Dubai", "Asia/Dili", "Asia/Dhaka", "Asia/Damascus",
            "Asia/Dacca", "Asia/Colombo", "Asia/Chungking", "Asia/Chongqing", "Asia/Choibalsan",
            "Asia/Calcutta", "Asia/Brunei", "Asia/Bishkek", "Asia/Beirut", "Asia/Bangkok",
            "Asia/Baku", "Asia/Bahrain", "Asia/Baghdad", "Asia/Ashkhabad", "Asia/Ashgabat",
            "Asia/Aqtobe", "Asia/Aqtau", "Asia/Anadyr", "Asia/Amman", "Asia/Almaty",
            "Asia/Aden", "Antarctica/Vostok", "Antarctica/Syowa", "Antarctica/South_Pole", "Antarctica/Rothera",
            "Antarctica/Palmer", "Antarctica/McMurdo", "Antarctica/Mawson", "Antarctica/DumontDUrville",
            "Antarctica/Davis", "Antarctica/Casey", "America/Yellowknife", "America/Yakutat", "America/Winnipeg",
            "America/Whitehorse", "America/Virgin", "America/Vancouver", "America/Tortola", "America/Toronto",
            "America/Tijuana", "America/Thunder_Bay", "America/Thule", "America/Tegucigalpa", "America/Swift_Current",
            "America/St_Vincent", "America/St_Thomas", "America/St_Lucia", "America/St_Kitts", "America/St_Johns",
            "America/Shiprock", "America/Scoresbysund", "America/Sao_Paulo", "America/Santo_Domingo", "America/Santiago",
            "America/Rosario", "America/Rio_Branco", "America/Regina", "America/Recife", "America/Rankin_Inlet",
            "America/Rainy_River", "America/Puerto_Rico", "America/Porto_Velho", "America/Porto_Acre", "America/Port_of_Spain",
            "America/Port-au-Prince", "America/Phoenix", "America/Paramaribo", "America/Pangnirtung", "America/Panama",
            "America/North_Dakota/Center", "America/Noronha", "America/Nome", "America/Nipigon", "America/New_York",
            "America/Nassau", "America/Montserrat", "America/Montreal", "America/Montevideo", "America/Monterrey",
            "America/Miquelon", "America/Mexico_City", "America/Merida", "America/Menominee", "America/Mendoza",
            "America/Mazatlan", "America/Martinique", "America/Manaus", "America/Managua", "America/Maceio",
            "America/Louisville", "America/Los_Angeles", "America/Lima", "America/La_Paz", "America/Knox_IN",
            "America/Kentucky/Monticello", "America/Kentucky/Louisville", "America/Juneau", "America/Jujuy", "America/Jamaica",
            "America/Iqaluit", "America/Inuvik", "America/Indianapolis", "America/Indiana/Vevay", "America/Indiana/Marengo",
            "America/Indiana/Knox", "America/Indiana/Indianapolis", "America/Hermosillo", "America/Havana", "America/Halifax",
            "America/Guyana", "America/Guayaquil", "America/Guatemala", "America/Guadeloupe", "America/Grenada",
            "America/Grand_Turk", "America/Goose_Bay", "America/Godthab", "America/Glace_Bay", "America/Fortaleza",
            "America/Fort_Wayne", "America/Ensenada", "America/El_Salvador", "America/Eirunepe", "America/Edmonton",
            "America/Dominica", "America/Detroit", "America/Denver", "America/Dawson_Creek", "America/Dawson",
            "America/Danmarkshavn", "America/Curacao", "America/Cuiaba", "America/Costa_Rica", "America/Cordoba",
            "America/Coral_Harbour", "America/Chihuahua", "America/Chicago", "America/Cayman", "America/Cayenne",
            "America/Catamarca", "America/Caracas", "America/Cancun", "America/Campo_Grande", "America/Cambridge_Bay",
            "America/Buenos_Aires", "America/Boise", "America/Bogota", "America/Boa_Vista", "America/Belize",
            "America/Belem", "America/Barbados", "America/Bahia", "America/Atka", "America/Asuncion",
            "America/Aruba", "America/Argentina/Ushuaia", "America/Argentina/Tucuman", "America/Argentina/San_Juan", "America/Argentina/Rio_Gallegos",
            "America/Argentina/Mendoza", "America/Argentina/La_Rioja", "America/Argentina/Jujuy", "America/Argentina/Cordoba", "America/Argentina/ComodRivadavia",
            "America/Argentina/Catamarca", "America/Argentina/Buenos_Aires", "America/Araguaina", "America/Antigua", "America/Anguilla",
            "America/Anchorage", "America/Adak", "Africa/Windhoek", "Africa/Tunis", "Africa/Tripoli",
            "Africa/Timbuktu", "Africa/Sao_Tome", "Africa/Porto-Novo", "Africa/Ouagadougou", "Africa/Nouakchott",
            "Africa/Niamey", "Africa/Ndjamena", "Africa/Nairobi", "Africa/Monrovia", "Africa/Mogadishu",
            "Africa/Mbabane", "Africa/Maseru", "Africa/Maputo", "Africa/Malabo", "Africa/Lusaka",
            "Africa/Lubumbashi", "Africa/Luanda", "Africa/Lome", "Africa/Libreville", "Africa/Lagos",
            "Africa/Kinshasa", "Africa/Kigali", "Africa/Khartoum", "Africa/Kampala", "Africa/Johannesburg",
            "Africa/Harare", "Africa/Gaborone", "Africa/Freetown", "Africa/El_Aaiun", "Africa/Douala",
            "Africa/Djibouti", "Africa/Dar_es_Salaam", "Africa/Dakar", "Africa/Conakry", "Africa/Ceuta",
            "Africa/Casablanca", "Africa/Cairo", "Africa/Bujumbura", "Africa/Brazzaville", "Africa/Blantyre",
            "Africa/Bissau", "Africa/Banjul", "Africa/Bangui", "Africa/Bamako", "Africa/Asmera",
            "Africa/Algiers", "Africa/Addis_Ababa", "Africa/Accra", "Africa/Abidjan", "Australia/Yancowinna",
            "Brazil/Acre", "Brazil/DeNoronha", "Brazil/East", "Brazil/West", "Canada/Atlantic",
            "Canada/Central", "Canada/East-Saskatchewan", "Canada/Eastern", "Canada/Mountain", "Canada/Newfoundland",
            "Canada/Pacific", "Canada/Saskatchewan", "Canada/Yukon", "Chile/Continental", "Chile/EasterIsland",
            "Europe/Amsterdam", "Europe/Andorra", "Europe/Athens", "Europe/Belfast", "Europe/Belgrade",
            "Europe/Berlin", "Europe/Bratislava", "Europe/Brussels", "Europe/Bucharest", "Europe/Budapest",
            "Europe/Chisinau", "Europe/Copenhagen", "Europe/Dublin", "Europe/Gibraltar", "Europe/Helsinki",
            "Europe/Istanbul", "Europe/Kaliningrad", "Europe/Kiev", "Europe/Lisbon", "Europe/Ljubljana",
            "Europe/London", "Europe/Luxembourg", "Europe/Madrid", "Europe/Malta", "Europe/Mariehamn",
            "Europe/Minsk", "Europe/Monaco", "Europe/Moscow", "Europe/Nicosia", "Europe/Oslo",
            "Europe/Paris", "Europe/Prague", "Europe/Riga", "Europe/Rome", "Europe/Samara",
            "Europe/San_Marino", "Europe/Sarajevo", "Europe/Simferopol", "Europe/Skopje", "Europe/Sofia",
            "Europe/Stockholm", "Europe/Tallinn", "Europe/Tirane", "Europe/Tiraspol", "Europe/Uzhgorod",
            "Europe/Vaduz", "Europe/Vatican", "Europe/Vienna", "Europe/Vilnius", "Europe/Warsaw",
            "Europe/Zagreb", "Europe/Zaporozhye", "Europe/Zurich", "Indian/Antananarivo", "Indian/Chagos",
            "Indian/Christmas", "Indian/Cocos", "Indian/Comoro", "Indian/Kerguelen", "Indian/Mahe",
            "Indian/Maldives", "Indian/Mauritius", "Indian/Mayotte", "Indian/Reunion", "Mexico/BajaNorte",
            "Mexico/BajaSur", "Mexico/General", "Pacific/Apia", "Pacific/Auckland", "Pacific/Chatham",
            "Pacific/Easter", "Pacific/Efate", "Pacific/Enderbury", "Pacific/Fakaofo", "Pacific/Fiji",
            "Pacific/Funafuti", "Pacific/Galapagos", "Pacific/Gambier", "Pacific/Guadalcanal", "Pacific/Guam",
            "Pacific/Honolulu", "Pacific/Johnston", "Pacific/Kiritimati", "Pacific/Kosrae", "Pacific/Kwajalein",
            "Pacific/Majuro", "Pacific/Marquesas", "Pacific/Midway", "Pacific/Nauru", "Pacific/Niue",
            "Pacific/Norfolk", "Pacific/Noumea", "Pacific/Pago_Pago", "Pacific/Palau", "Pacific/Pitcairn",
            "Pacific/Ponape", "Pacific/Port_Moresby", "Pacific/Rarotonga", "Pacific/Saipan", "Pacific/Samoa",
            "Pacific/Tahiti", "Pacific/Tarawa", "Pacific/Tongatapu", "Pacific/Truk", "Pacific/Wake",
            "Pacific/Wallis", "Pacific/Yap", "US/Alaska", "US/Aleutian", "US/Arizona",
            "US/Central", "US/East-Indiana", "US/Eastern", "US/Hawaii", "US/Indiana-Starke",
            "US/Michigan", "US/Mountain", "US/Pacific", "US/Pacific-New", "US/Samoa"
        );



        $timezones = array();
        foreach ( $zones as $z )
        {
            $timezones[$z] = $z;
        }

        return $timezones;
    }

}

