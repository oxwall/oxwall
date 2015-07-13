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
     * Default range days
     */
    const DEFAULT_RANGE_DAYS = 6; // a one week

    /**
     * Max range days
     */
    const MAX_RANGE_DAYS = 30;

    /**
     * Default start date
     * @var integer
     */
    protected $defaultStartDate;

    /**
     * Default end date
     * @var integer
     */
    protected $defaultEndDate;

    /**
     * Class constructor
     */
    public function __construct( BASE_CLASS_WidgetParameter $paramObj )
    {
        parent::__construct();

        //--  register js and css files --//
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'chart.js');

        $service = BOL_SiteStatisticService::getInstance();

        
        // get widget settings
        $defaultDays = (int) $paramObj->customParamList['defaultRangeDays'] + 1;
        $startYear   = (int) $paramObj->customParamList['startYear'];
        $defaultReportType  = empty($_GET['type']) ?  BOL_SiteStatisticDao::REPORT_TYPE_WEEK : $_GET['type'];

        // init both default start and end date
        $this->defaultStartDate = strtotime('-' . $defaultDays . ' day', time());
        $this->defaultEndDate   = time();

        // add a filter form
        $this->addForm(new ContentStatisticForm('content_statistics_form', $this->
                defaultStartDate, $this->defaultEndDate, $startYear));

        // get statistic data
        $data = $service->getContentStatistics('comments', date('Y-m-d', 
                $this->defaultStartDate), date('Y-m-d', $this->defaultEndDate), $defaultReportType);

        // assign view variables
        $this->assign('categories', 
                json_encode($service->getCategoriesLabel($defaultReportType)));

        $this->assign('data', json_encode($data, JSON_NUMERIC_CHECK));
    }

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

        $settingList['defaultRangeDays'] = array(
            'presentation' => self::PRESENTATION_SELECT, 
            'label' => OW::getLanguage()->text('admin', 'widget_content_statistics_default_range_days_setting'),
            'value' => self::DEFAULT_RANGE_DAYS - 1,
            'optionList' => range(1, self::MAX_RANGE_DAYS)
        );

        $settingList['startYear'] = array(
            'presentation' => self::PRESENTATION_TEXT, 
            'label' => OW::getLanguage()->text('admin', 'widget_content_statistics_start_year_setting'),
            'value' => date('Y')
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

class ContentStatisticForm extends Form
{
    /**
     * Class constructor
     * 
     * @param string $name
     * @param integer $startDate
     * @param integer $endDate
     * @param integer $startYear
     */
    public function __construct($name, $startDate, $endDate, $startYear) 
    {
        parent::__construct($name);

        $year = date('Y');
        $dateRangeField = new DateRangePicker('date');
        $dateRangeField->setFormat('y-m-d');
        $dateRangeField->setDevider(' - ');
        $dateRangeField->setMinYear(date('Y', strtotime('-' . ($year - $startYear) . ' years')));
        $dateRangeField->setMaxYear($year);
        $dateRangeField->setValues($startDate, $endDate);


        $this->addElement($dateRangeField);

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
        $groupField->setValue($selectedGroup);
        $this->addElement($groupField);
    }
}