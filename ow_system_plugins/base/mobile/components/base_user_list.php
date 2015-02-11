<?php

class BASE_MCMP_BaseUserList extends BASE_MCMP_UserList
{
    private $listKey;

    public function __construct($listKey, $list, $showOnline)//__construct( $listKey, $showOnline = false, $excludeList = array(), $count = 10 )
    {
        $this->listKey = $listKey;

        if ( $this->listKey == 'birthdays' )
        {
            $showOnline = false;
        }

        parent::__construct($list, $showOnline);
    }

    public function getFields( $userIdList )
    {
        $fields = array();

        $qs = array();

        $qBdate = BOL_QuestionService::getInstance()->findQuestionByName('birthdate');

        if ( $qBdate->onView )
        {
            $qs[] = 'birthdate';
        }

        $qSex = BOL_QuestionService::getInstance()->findQuestionByName('sex');

        if ( $qSex->onView )
        {
            $qs[] = 'sex';
        }

        $questionList = BOL_QuestionService::getInstance()->getQuestionData($userIdList, $qs);

        foreach ( $questionList as $uid => $question )
        {

            $fields[$uid] = array();

            $age = '';

            if ( !empty($question['birthdate']) )
            {
                $date = UTIL_DateTime::parseDate($question['birthdate'], UTIL_DateTime::MYSQL_DATETIME_DATE_FORMAT);

                $age = UTIL_DateTime::getAge($date['year'], $date['month'], $date['day']);
            }

            $sexValue = '';
            if ( !empty($question['sex']) )
            {
                $sex = $question['sex'];

                for ( $i = 0; $i < 31; $i++ )
                {
                    $val = pow(2, $i);
                    if ( (int) $sex & $val )
                    {
                        $sexValue .= BOL_QuestionService::getInstance()->getQuestionValueLang('sex', $val) . ', ';
                    }
                }

                if ( !empty($sexValue) )
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

            if ( !empty($question['birthdate']) )
            {
                $dinfo = date_parse($question['birthdate']);

                if ( $this->listKey == 'birthdays' )
                {
                    $birthdate = '';

                    if ( intval(date('d')) + 1 == intval($dinfo['day']) )
                    {
                        $questionList[$uid]['birthday'] = OW::getLanguage()->text('base', 'date_time_tomorrow');

                        $birthdate = '<a href="#" class="ow_lbutton ow_green">' . $questionList[$uid]['birthday'] . '</a>';
                    }
                    else if ( intval(date('d')) == intval($dinfo['day']) )
                    {
                        $questionList[$uid]['birthday'] = OW::getLanguage()->text('base', 'date_time_today');

                        $birthdate = '<a href="#" class="ow_lbutton ow_green">' . $questionList[$uid]['birthday'] . '</a>';
                    }
                    else
                    {
                        $birthdate = UTIL_DateTime::formatBirthdate($dinfo['year'], $dinfo['month'], $dinfo['day']);
                    }

                    $fields[$uid][] = array(
                        'label' => 'Birthday: ',
                        'value' => $birthdate
                    );

                }
            }
        }

        return $fields;
    }
}