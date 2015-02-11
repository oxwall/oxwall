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
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow_system_plugins.base.classes
 * @since 1.0
 */

class BASE_CLASS_SearchResultList extends BASE_CMP_Users
{
    public function getFields($userIdList)
    {
        $fields = array();

        $qs = array();

        $qBdate = BOL_QuestionService::getInstance()->findQuestionByName('birthdate', 'sex');

        if ( $qBdate->onView )
            $qs[] = 'birthdate';

        $qSex = BOL_QuestionService::getInstance()->findQuestionByName('sex');

        if ( $qSex->onView )
            $qs[] = 'sex';

        $questionList = BOL_QuestionService::getInstance()->getQuestionData($userIdList, $qs);

        foreach ( $questionList as $uid => $q )
        {

            $fields[$uid] = array();

            $age = '';
            
            if(!empty($q['birthdate'])){

                $date = UTIL_DateTime::parseDate( $q['birthdate'], UTIL_DateTime::MYSQL_DATETIME_DATE_FORMAT );
                $dinfo = date_parse($q['birthdate']);
                
                $age = UTIL_DateTime::getAge($date['year'], $date['month'], $date['day']);
            }


            if ( !empty($q['sex']) )
            {
                $sex = $q['sex'];
                $sexValue = '';

                for( $i = 0 ; $i < 31; $i++ )
                {
                    $val = pow( 2, $i );
                    if ( (int)$sex & $val  )
                    {
                        $sexValue .= BOL_QuestionService::getInstance()->getQuestionValueLang('sex', $val) . ', ';
                    }
                }

                if( !empty($sexValue) )
                {
                    $sexValue = substr($sexValue, 0, -2);
                }
            }

            if ( !empty($sexValue) && !empty($age) )
            {
                $fields[$uid][] = array(
                        'label' => '',
                        'value' => $sexValue . ' ' . $age
                    );
            }
        }

        return $fields;
    }
}