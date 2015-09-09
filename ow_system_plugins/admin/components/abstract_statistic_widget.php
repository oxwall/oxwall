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
 * Abstract statistics widget component
 *
 * @author Alex Ermashev <alexermashev@gmail.com>
 * @package ow_system_plugins.base.components
 * @since 1.7.6
 */
abstract class ADMIN_CMP_AbstractStatisticWidget extends BASE_CLASS_Widget
{
    /**
     * Default period
     * @var string
     */
    protected $defaultPeriod;

    /**
     * Add menu
     *
     * @param string $prefix
     * @return void
     */
    protected function addMenu($prefix)
    {
        $this->addComponent('menu', new BASE_CMP_WidgetMenu(array(
            'today' => array(
                'label' => OW::getLanguage()->text('admin', 'site_statistics_today_period'),
                'id' => $prefix . '_menu_statistics_' . BOL_SiteStatisticService::PERIOD_TYPE_TODAY,
                'active' => $this->defaultPeriod == BOL_SiteStatisticService::PERIOD_TYPE_TODAY
            ),
            'yesterday' => array(
                'label' => OW::getLanguage()->text('admin', 'site_statistics_yesterday_period'),
                'id' => $prefix. '_menu_statistics_' . BOL_SiteStatisticService::PERIOD_TYPE_YESTERDAY,
                'active' => $this->defaultPeriod == BOL_SiteStatisticService::PERIOD_TYPE_YESTERDAY
            ),
            'last_7_days' => array(
                'label' => OW::getLanguage()->text('admin', 'site_statistics_last_7_days_period'),
                'id' => $prefix. '_menu_statistics_' . BOL_SiteStatisticService::PERIOD_TYPE_LAST_7_DAYS,
                'active' => $this->defaultPeriod == BOL_SiteStatisticService::PERIOD_TYPE_LAST_7_DAYS
            ),
            'last_30_days' => array(
                'label' => OW::getLanguage()->text('admin', 'site_statistics_last_30_days_period'),
                'id' => $prefix. '_menu_statistics_' . BOL_SiteStatisticService::PERIOD_TYPE_LAST_30_DAYS,
                'active' => $this->defaultPeriod == BOL_SiteStatisticService::PERIOD_TYPE_LAST_30_DAYS
            ),
            'last_year' => array(
                'label' => OW::getLanguage()->text('admin', 'site_statistics_last_year_period'),
                'id' => $prefix. '_menu_statistics_' . BOL_SiteStatisticService::PERIOD_TYPE_LAST_YEAR,
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
}