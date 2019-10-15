<?php

/**
 * This software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is
 * licensed under The BSD license.

 * ---
 * Copyright (c) 2011, Oxwall Foundation
 * All rights reserved.

 * Redistribution and use in source and binary forms, with or without modification, are permitted provided that the
 * following conditions are met:
 *
 *  - Redistributions of source code must retain the above copyright notice, this list of conditions and
 *  the following disclaimer.
 *
 *  - Redistributions in binary form must reproduce the above copyright notice, this list of conditions and
 *  the following disclaimer in the documentation and/or other materials provided with the distribution.
 *
 *  - Neither the name of the Oxwall Foundation nor the names of its contributors may be used to endorse or promote products
 *  derived from this software without specific prior written permission.

 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES,
 * INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED
 * AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

/**
 * Birthdays Service.
 * 
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_plugins.birthdays.bol
 * @since 1.0
 */
final class BIRTHDAYS_BOL_Service
{
    /**
     * @var BIRTHDAYS_BOL_UserDao
     */
    private $birthdaysDao;

    private $birthdaysPrivacyDao;

    /**
     * Constructor.
     */
    private function __construct()
    {
        $this->birthdaysDao = BIRTHDAYS_BOL_UserDao::getInstance();
        $this->birthdaysPrivacyDao = BIRTHDAYS_BOL_PrivacyDao::getInstance();
    }
    /**
     * Singleton instance.
     *
     * @var BIRTHDAYS_BOL_Service
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BIRTHDAYS_BOL_Service
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function findListByBirthdayPeriod( $start, $end, $first, $count, $idList = null, $privacy = null )
    {
        return $this->birthdaysDao->findListByBirthdayPeriod($start, $end, $first, $count, $idList, $privacy);
    }

    public function countByBirthdayPeriod( $start, $end, $idList = null, $privacy = null )
    {
        return $this->birthdaysDao->countByBirthdayPeriod($start, $end, $idList, $privacy);
    }

//    public function findListByBirthdayPeriodAndUserIdList( $start, $end, $first, $count, $idList )
//    {
//        return $this->birthdaysDao->findListByBirthdayPeriodAndUserIdList($start, $end, $first, $count, $idList);
//    }

//    public function countByBirthdayPeriodAndUserIdList( $start, $end, $idList )
//    {
//        return $this->birthdaysDao->countByBirthdayPeriodAndUserIdList($start, $end, $idList);
//    }

    /**
     * Checks and raises event on users birthday list.
     */
    public function checkBirthdays()
    {
        $configTs = OW::getConfig()->getValue('birthdays', 'users_birthday_event_ts');

        if ( date('j', $configTs) !== date('j', time()) )
        {
            $userList = $this->birthdaysDao->findUserListByBirthday(date('Y-m-d'));

            $event = new OW_Event('birthdays.today_birthday_user_list', array('userIdList' => $userList));
            OW::getEventManager()->trigger($event);

            OW::getConfig()->saveConfig('birthdays', 'users_birthday_event_ts', time());
        }
    }

    public function getUserListData( $first, $count )
    {
        //set date bounds for birthdays
        $period = array(
            'start' => date('Y-m-d'),
            'end' => date('Y-m-d', strtotime('+7 day'))
        );

        return array(
            $this->findListByBirthdayPeriod($period['start'], $period['end'], $first, $count, null, array('everybody')), // get users
            $this->countByBirthdayPeriod($period['start'], $period['end'], null, array('everybody')) // count users
        );
    }

    /**
     * @param int $userId
     * @return BIRTHDAYS_BOL_Privacy
     */
    
    public function findBirthdayPrivacyByUserId( $userId )
    {
        return $this->birthdaysPrivacyDao->findByUserId($userId);
    }

    public function deleteBirthdayPrivacyByUserId( $userId )
    {
        $this->birthdaysPrivacyDao->deleteByUserId($userId);
    }

    public function saveBirthdayPrivacy( BIRTHDAYS_BOL_Privacy $dto )
    {
        return $this->birthdaysPrivacyDao->save($dto);
    }
}