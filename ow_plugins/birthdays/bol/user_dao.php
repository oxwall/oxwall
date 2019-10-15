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
 * Data Access Object for `base_user` table.
 * 
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_plugins.birthdays.bol
 * @since 1.0
 */
class BIRTHDAYS_BOL_UserDao extends OW_BaseDao
{
    /**
     * @var BOL_UserDao
     */
    private $userDao;
    /**
     * Singleton instance.
     *
     * @var BOL_UserDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_UserDao
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * Constructor.
     */
    protected function __construct()
    {
        parent::__construct();
        $this->userDao = BOL_UserDao::getInstance();
    }

    /**
     * @see OW_BaseDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return $this->userDao->getDtoClassName();
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return $this->userDao->getTableName();
    }

    public function findListByBirthdayPeriod( $start, $end, $first, $count, $idList = null, $privacy = null )
    {
        if ( $idList === array() )
        {
            return array();
        }

        $queryParts = BOL_UserDao::getInstance()->getUserQueryFilter("u", "id", array(
            "method" => "BIRTHDAYS_BOL_UserDao::findListByBirthdayPeriod"
        ));

        $query = "SELECT `u`.* FROM `{$this->getTableName()}` AS `u`
			INNER JOIN `" . BOL_QuestionDataDao::getInstance()->getTableName() . "` AS `qd` ON( `u`.`id` = `qd`.`userId` )
            " . $queryParts["join"] . "
            ".( !empty($privacy) ? "LEFT JOIN `" . BIRTHDAYS_BOL_PrivacyDao::getInstance()->getTableName() . "` AS `bp` ON( `u`.`id` = bp.userId AND bp.privacy NOT IN (". $this->dbo->mergeInClause($privacy) ." ) ) " : '' ). "
			WHERE " . $queryParts["where"] . " AND " . ( !empty($privacy) ?" `bp`.id IS NULL AND " : "" ). " `qd`.`questionName` = 'birthdate'
            AND ( DATE_FORMAT(`qd`.`dateValue`, '" . date('Y') . "-%m-%d') BETWEEN :start1 AND :end1 OR DATE_FORMAT(`qd`.`dateValue`, '" . ( intval(date('Y')) + 1 ) . "-%m-%d') BETWEEN :start2 AND :end2 )
            ".( !empty($idList) ? "AND `qd`.`userId` IN ( ".$this->dbo->mergeInClause($idList)." )" : '' )."
			ORDER BY MONTH(`qd`.`dateValue`) " . (date('m') == 12 ? 'DESC' : 'ASC') . " , DAY(`qd`.`dateValue`) ASC
			LIMIT :first, :count";

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array('start1' => $start, 'start2' => $start, 'end1' => $end, 'end2' => $end, 'first' => $first, 'count' => $count));
    }

    public function countByBirthdayPeriod( $start, $end, $idList = null, $privacy = null )
    {
        if ( $idList === array() )
        {
            return 0;
        }

        $queryParts = BOL_UserDao::getInstance()->getUserQueryFilter("q", "userId", array(
            "method" => "BIRTHDAYS_BOL_UserDao::countByBirthdayPeriod"
        ));

        $query = "SELECT COUNT(*) FROM `" . BOL_QuestionDataDao::getInstance()->getTableName() . "` q
            " . $queryParts["join"] . " 
            ".( !empty($privacy) ? "LEFT JOIN `" . BIRTHDAYS_BOL_PrivacyDao::getInstance()->getTableName() . "` AS `bp` ON( `q`.`userId` = bp.userId AND ( bp.privacy NOT IN (". $this->dbo->mergeInClause($privacy) .") ) ) " : '' ). "
			WHERE " . $queryParts["where"] . " AND ". ( !empty($privacy) ? " `bp`.id IS NULL AND " : "" ). " q.`questionName` = 'birthdate' AND
            ( DATE_FORMAT(q.`dateValue`, '" . date('Y') . "-%m-%d') BETWEEN :start1 AND :end1 OR DATE_FORMAT(q.`dateValue`, '" . ( intval(date('Y')) + 1 ) . "-%m-%d') BETWEEN :start2 AND :end2 )
            " . ( !empty($idList) ? "AND q.`userId` IN (".$this->dbo->mergeInClause($idList).")" : '');

        return $this->dbo->queryForColumn($query, array('start1' => $start, 'start2' => $start, 'end1' => $end, 'end2' => $end));
    }

//    public function findListByBirthdayPeriodAndUserIdList( $start, $end, $first, $count, $idList )
//    {
//        if ( empty($idList) )
//        {
//            return array();
//        }
//
//        $query = " SELECT `u`.* FROM `{$this->getTableName()}` AS `u`
//			INNER JOIN `" . BOL_QuestionDataDao::getInstance()->getTableName() . "` AS `qd` ON(`u`.`id` = `qd`.`userId`)
//			WHERE `qd`.`questionName` = 'birthdate' AND DATE_FORMAT(`qd`.`dateValue`, '" . date('Y') . "-%m-%d') BETWEEN :start AND :end
//                AND `u`.`id` IN ({$this->dbo->mergeInClause($idList)})
//			ORDER BY DAY(`qd`.`dateValue`) ASC
//			LIMIT :first, :count";
//
//        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array('start' => $start, 'end' => $end, 'first' => $first, 'count' => $count));
//    }

//    public function countByBirthdayPeriodAndUserIdList( $start, $end, $idList )
//    {
//        $query = "SELECT COUNT(*) FROM `" . BOL_QuestionDataDao::getInstance()->getTableName() . "`
//			WHERE `questionName` = 'birthdate' AND DATE_FORMAT(`dateValue`, '" . date('Y') . "-%m-%d') BETWEEN :start AND :end";
//
//        return $this->dbo->queryForColumn($query, array('start' => $start, 'end' => $end));
//    }

    public function findUserListByBirthday( $date )
    {
        $query = "SELECT `u`.`id` FROM `".$this->getTableName()."` AS `u`
            INNER JOIN `" . BOL_QuestionDataDao::getInstance()->getTableName() . "` AS `qd` ON(`u`.`id` = `qd`.`userId`)
            WHERE `qd`.`questionName` = 'birthdate' AND DATE_FORMAT(`qd`.`dateValue`, '" . date('Y') . "-%m-%d') = :date";

        return $this->dbo->queryForColumnList($query, array('date' => $date));
    }
}