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
 * @author Aybat Duyshokov <duyshokov@gmail.com>, Sardar Madumarov <madumarov@gmail.com>
 * @package ow_system_plugins.base.controllers
 * @since 1.0
 */
class BASE_CTRL_UserList extends OW_ActionController
{
    private $usersPerPage;

    public function __construct()
    {
        parent::__construct();
        OW::getNavigation()->activateMenuItem(OW_Navigation::MAIN, 'base', 'users_main_menu_item');

        $this->setPageHeading(OW::getLanguage()->text('base', 'users_browse_page_heading'));
        $this->setPageTitle(OW::getLanguage()->text('base', 'users_browse_page_heading'));
        $this->setPageHeadingIconClass('ow_ic_user');
        $this->usersPerPage = (int)OW::getConfig()->getValue('base', 'users_count_on_page');
    }

    public function index( $params )
    {
        $listType = empty($params['list']) ? 'latest' : strtolower(trim($params['list']));
        $language = OW::getLanguage();
        $this->addComponent('menu', self::getMenu($listType));

        $page = (!empty($_GET['page']) && intval($_GET['page']) > 0 ) ? intval($_GET['page']) : 1;
        list($list, $itemCount) = $this->getData($listType, (($page - 1) * $this->usersPerPage), $this->usersPerPage);

        //$cmp = new BASE_Members($list, $itemCount, $this->usersPerPage, true, $listType);
        $cmp = OW::getClassInstance("BASE_Members", $list, $itemCount, $this->usersPerPage, true, $listType);
        
        $this->addComponent('cmp', $cmp);

        $this->assign('listType', $listType);

        $description = '';
        try
        {
            $description = BOL_LanguageService::getInstance()->getText(BOL_LanguageService::getInstance()->getCurrent()->getId(), 'base', 'users_list_'.$listType.'_meta_description');
        }
        catch ( Exception $e )
        {

        }

        if ( !empty($description) )
        {
            OW::getDocument()->setDescription($description);
        }
    }

    public function forApproval()
    {
        $this->setTemplate(OW::getPluginManager()->getPlugin('base')->getCtrlViewDir() . 'user_list_index.html');

        $language = OW::getLanguage();

        $page = (!empty($_GET['page']) && intval($_GET['page']) > 0 ) ? $_GET['page'] : 1;
        list($list, $itemCount) = $this->getData('waiting-for-approval', (($page - 1) * $this->usersPerPage), $this->usersPerPage);

        //$cmp = new BASE_Members($list, $itemCount, $this->usersPerPage, false, 'waiting-for-approval');
        $cmp = OW::getClassInstance("BASE_Members", $list, $itemCount, $this->usersPerPage, false, 'waiting-for-approval');
        
        $this->addComponent('cmp', $cmp);
        
        $this->assign('listType', 'waiting-for-approval');
    }

    private function getData( $listKey, $first, $count )
    {
        $service = BOL_UserService::getInstance();
        return $service->getDataForUsersList($listKey, $first, $count);
    }

    public static function getMenu( $activeListType )
    {
        $language = OW::getLanguage();

        $menuArray = array(
            array(
                'label' => $language->text('base', 'user_list_menu_item_latest'),
                'url' => OW::getRouter()->urlForRoute('base_user_lists', array('list' => 'latest')),
                'iconClass' => 'ow_ic_clock',
                'key' => 'latest',
                'order' => 1
            ),
            array(
                'label' => $language->text('base', 'user_list_menu_item_online'),
                'url' => OW::getRouter()->urlForRoute('base_user_lists', array('list' => 'online')),
                'iconClass' => 'ow_ic_push_pin',
                'key' => 'online',
                'order' => 3
            ),
            array(
                'label' => $language->text('base', 'user_search_menu_item_label'),
                'url' => OW::getRouter()->urlForRoute('users-search'),
                'iconClass' => 'ow_ic_lens',
                'key' => 'search',
                'order' => 4
            )
        );

        if ( BOL_UserService::getInstance()->countFeatured() > 0 )
        {
            $menuArray[] =  array(
                'label' => $language->text('base', 'user_list_menu_item_featured'),
                'url' => OW::getRouter()->urlForRoute('base_user_lists', array('list' => 'featured')),
                'iconClass' => 'ow_ic_push_pin',
                'key' => 'featured',
                'order' => 2
            );
        }

        $event = new BASE_CLASS_EventCollector('base.add_user_list');
        OW::getEventManager()->trigger($event);
        $data = $event->getData();

        if ( !empty($data) )
        {
            $menuArray = array_merge($menuArray, $data);
        }

        $menu = new BASE_CMP_ContentMenu();

        foreach ( $menuArray as $item )
        {
            $menuItem = new BASE_MenuItem();
            $menuItem->setLabel($item['label']);
            $menuItem->setIconClass($item['iconClass']);
            $menuItem->setUrl($item['url']);
            $menuItem->setKey($item['key']);
            $menuItem->setOrder(empty($item['order']) ? 999 : $item['order']);
            $menu->addElement($menuItem);

            if ( $activeListType == $item['key'] )
            {
                $menuItem->setActive(true);
            }
        }

        return $menu;
    }
}

class BASE_Members extends BASE_CMP_Users
{
    private $listKey;

    public function __construct( $list, $itemCount, $usersOnPage, $showOnline, $listKey )
    {
        $this->listKey = $listKey;

        if ( $this->listKey == 'birthdays' )
        {
            $showOnline = false;
        }

        parent::__construct($list, $itemCount, $usersOnPage, $showOnline);
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

                        $birthdate = '<span class="ow_green" style="font-weight: bold; text-transform: uppercase;">' . $questionList[$uid]['birthday'] . '</a>';
                    }
                    else if ( intval(date('d')) == intval($dinfo['day']) )
                    {
                        $questionList[$uid]['birthday'] = OW::getLanguage()->text('base', 'date_time_today');

                        $birthdate = '<span class="ow_green" style="font-weight: bold; text-transform: uppercase;">' . $questionList[$uid]['birthday'] . '</span>';
                    }
                    else
                    {
                        $birthdate = UTIL_DateTime::formatBirthdate($dinfo['year'], $dinfo['month'], $dinfo['day']);
                    }

                    $fields[$uid][] = array(
                        'label' => OW::getLanguage()->text('birthdays', 'birthday'),
                        'value' => $birthdate
                    );
                }
            }
        }

        return $fields;
    }
}