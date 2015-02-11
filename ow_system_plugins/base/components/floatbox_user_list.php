<?php

class BASE_CMP_FloatboxUserList extends OW_Component
{
    public function __construct( $idList )
    {
        parent::__construct();

        $service = BOL_UserService::getInstance();

        $displayNameList = $service->getDisplayNamesForList($idList);
        $usernameList = $service->getUserNamesForList($idList);
        $avatarList = BOL_AvatarService::getInstance()->getDataForUserAvatars($idList);

        $scroll = count($idList) > 18;
        $this->assign( 'scroll', $scroll );

        $this->assign( 'fields', $this->getFields($idList) );
        $this->assign('usernameList', $usernameList);
        $this->assign('avatarList', $avatarList);
        $this->assign('displayNameList', $displayNameList);
        $this->assign('onlineInfo', array());

        $this->assign('list', $idList);
    }

    public function getFields( $userIdList )
    {
        $fields = array();

        $qs = array();

        $qBdate = BOL_QuestionService::getInstance()->findQuestionByName('birthdate');

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

            if( !empty($q['birthdate']) )
            {
                $date = UTIL_DateTime::parseDate($q['birthdate'], UTIL_DateTime::MYSQL_DATETIME_DATE_FORMAT);

                $age = UTIL_DateTime::getAge($date['year'], $date['month'], $date['day']);
            }

            if ( !empty($q['sex']) )
            {
                $fields[$uid][] = array(
                    'label' => '',
                    'value' => BOL_QuestionService::getInstance()->getQuestionValueLang('sex', $q['sex']) . ' ' . $age
                );
            }

            if( !empty($q['birthdate']) )
            {
                $dinfo = date_parse($q['birthdate']);
            }
        }

        return $fields;
    }
}