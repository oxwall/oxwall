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
     * Class constructor
     */
    public function __construct( BASE_CLASS_WidgetParameter $paramObj )
    {
        parent::__construct();

        // add a filter form
        $this->addForm(new ContentStatisticForm('content_statistics_form'));
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_TITLE => OW::getLanguage()->text('admin', 'content_statistic_widget'),
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
     */
    public function __construct($name) 
    {
        parent::__construct($name);

        $dateRangeField = new DateRangePicker('date');
        $dateRangeField->setMinYear(2011);
        $dateRangeField->setMaxYear(2015);
        $dateRangeField->setValues(strtotime("-1 day"), time());
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