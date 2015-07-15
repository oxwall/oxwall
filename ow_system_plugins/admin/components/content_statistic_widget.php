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
 * Admin content statistics widget component
 *
 * @author Alex Ermashev <alexermashev@gmail.com>
 * @package ow_system_plugins.base.components
 * @since 1.7.6
 */
class ADMIN_CMP_ContentStatisticWidget extends BASE_CLASS_Widget
{
    /**
     * Default content group
     * @var string
     */
    protected $defaultContentGroup;

    /**
     * Default period
     * @var string
     */
    protected $defaultPeriod;

    /**
     * Class constructor
     */
    public function __construct( BASE_CLASS_WidgetParameter $paramObj )
    {
        parent::__construct();

        $this->defaultContentGroup = $paramObj->customParamList['defaultContentGroup'];
        $this->defaultPeriod = $paramObj->customParamList['defaultPeriod'];
    }

    /**
     * On before render
     *
     * @return void
     */
    public function onBeforeRender()
    {
        // register forms
        $this->addForm(new ContentStatisticForm('content_statistics_form', $this->defaultContentGroup));

        // register components
        $this->addComponent('statistics', new ADMIN_CMP_ContentStatistic(array(
            'defaultContentGroup' => $this->defaultContentGroup,
            'defaultPeriod' => $this->defaultPeriod
        )));

        $this->addMenu();

        // assign view variables
        $this->assign('defaultContentGroup', $this->defaultContentGroup);
        $this->assign('defaultPeriod', $this->defaultPeriod);
    }

    /**
     * Add menu
     *
     * @return void
     */
    protected function addMenu()
    {
        $this->addComponent('menu', new BASE_CMP_WidgetMenu(array(
            'today' => array(
                'label' => OW::getLanguage()->text('admin', 'site_statistics_today_period'),
                'id' => 'content-statistics-' . BOL_SiteStatisticService::PERIOD_TYPE_TODAY,
                'active' => $this->defaultPeriod == BOL_SiteStatisticService::PERIOD_TYPE_TODAY
            ),
            'yesterday' => array(
                'label' => OW::getLanguage()->text('admin', 'site_statistics_yesterday_period'),
                'id' => 'content-statistics-' . BOL_SiteStatisticService::PERIOD_TYPE_YESTERDAY,
                'active' => $this->defaultPeriod == BOL_SiteStatisticService::PERIOD_TYPE_YESTERDAY
            ),
            'last_7_days' => array(
                'label' => OW::getLanguage()->text('admin', 'site_statistics_last_7_days_period'),
                'id' => 'content-statistics-' . BOL_SiteStatisticService::PERIOD_TYPE_LAST_7_DAYS,
                'active' => $this->defaultPeriod == BOL_SiteStatisticService::PERIOD_TYPE_LAST_7_DAYS
            ),
            'last_30_days' => array(
                'label' => OW::getLanguage()->text('admin', 'site_statistics_last_30_days_period'),
                'id' => 'content-statistics-' . BOL_SiteStatisticService::PERIOD_TYPE_LAST_30_DAYS,
                'active' => $this->defaultPeriod == BOL_SiteStatisticService::PERIOD_TYPE_LAST_30_DAYS
            ),
            'last_year' => array(
                'label' => OW::getLanguage()->text('admin', 'site_statistics_last_year_period'),
                'id' => 'content-statistics-' . BOL_SiteStatisticService::PERIOD_TYPE_LAST_YEAR,
                'active' => $this->defaultPeriod == BOL_SiteStatisticService::PERIOD_TYPE_LAST_YEAR
            )
        )));
    }

    /**
     * Get widget access
     *
     * @return string
     */
    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }

    /**
     * Get custom settings list
     *
     * @return array
     */
    public static function getSettingList()
    {
        $settingList = array();

        // get all registered content groups
        $contentGroups = BOL_ContentService::getInstance()->getContentGroups();
        $groupsValues = array();

        foreach($contentGroups as $group)
        {
            $groupsValues[$group['name']] = $group['label'];
        }

        $defaultGroup = $contentGroups ? array_shift($contentGroups) : array();
        $settingList['defaultContentGroup'] = array(
            'presentation' => self::PRESENTATION_SELECT,
            'label' => OW::getLanguage()->text('admin', 'widget_content_statistics_default_content_group'),
            'value' => !empty($defaultGroup) ? $defaultGroup['name'] : null,
            'optionList' => $groupsValues
        );

        $settingList['defaultPeriod'] = array(
            'presentation' => self::PRESENTATION_SELECT,
            'label' => OW::getLanguage()->text('admin', 'site_statistics_default_period'),
            'value' => BOL_SiteStatisticService::PERIOD_TYPE_LAST_7_DAYS,
            'optionList' => array(
                BOL_SiteStatisticService::PERIOD_TYPE_TODAY => OW::getLanguage()->text('admin', 'site_statistics_today_period'),
                BOL_SiteStatisticService::PERIOD_TYPE_YESTERDAY => OW::getLanguage()->text('admin', 'site_statistics_yesterday_period'),
                BOL_SiteStatisticService::PERIOD_TYPE_LAST_7_DAYS => OW::getLanguage()->text('admin', 'site_statistics_last_7_days_period'),
                BOL_SiteStatisticService::PERIOD_TYPE_LAST_30_DAYS => OW::getLanguage()->text('admin', 'site_statistics_last_30_days_period'),
                BOL_SiteStatisticService::PERIOD_TYPE_LAST_YEAR => OW::getLanguage()->text('admin', 'site_statistics_last_year_period')
            )
        );

        return $settingList;
    }

    /**
     * Get standart setting values list
     *
     * @return array
     */
    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_TITLE => OW::getLanguage()->text('admin', 'widget_content_statistics'),
            self::SETTING_ICON => self::ICON_FILES,
            self::SETTING_SHOW_TITLE => true
        );
    }
}

/**
 * Class ContentStatisticForm
 */
class ContentStatisticForm extends Form
{
    /**
     * Class constructor
     *
     * @param string $name
     * @apram string $defaultGroup
     */
    public function __construct($name, $defaultGroup)
    {
        parent::__construct($name);

        $contentGroups = BOL_ContentService::getInstance()->getContentGroups();
        $processedGroups = array();
        $selectedGroup   = null;

        foreach ($contentGroups as $group => $data)
        {
            if ( !$selectedGroup )
            {
                $selectedGroup = $group;
            }

            $processedGroups[$group] = $data['label'];
        }

        $groupField = new Selectbox('group');
        $groupField->setOptions($processedGroups);
        $groupField->setValue($defaultGroup);
        $this->addElement($groupField);
    }
}