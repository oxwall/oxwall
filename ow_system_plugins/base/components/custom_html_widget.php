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
 * Custom HTML widget
 *
 * @author Sergey Kambalin <greyexpert@gmail.com>
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
class BASE_CMP_CustomHtmlWidget extends BASE_CLASS_Widget
{
    private $content = false;
    private $nl2br = false;

    public function __construct( BASE_CLASS_WidgetParameter $paramObject )
    {
        parent::__construct();

        $params = $paramObject->customParamList;

        if ( !empty($params['content']) )
        {
            $this->content = $paramObject->customizeMode && !empty($_GET['disable-js']) ? UTIL_HtmlTag::stripJs($params['content']) : $params['content'];
        }

        if ( isset($params['nl_to_br']) )
        {
            $this->nl2br = (bool) $params['nl_to_br'];
        }
    }

    public static function getSettingList()
    {
        $settingList = array();
        $settingList['content'] = array(
            'presentation' => self::PRESENTATION_TEXTAREA,
            'label' => OW::getLanguage()->text('base', 'custom_html_widget_content_label'),
            'value' => ''
        );

        $settingList['nl_to_br'] = array(
            'presentation' => self::PRESENTATION_CHECKBOX,
            'label' => OW::getLanguage()->text('base', 'custom_html_widget_nl2br_label'),
            'value' => '0'
        );

        return $settingList;
    }

    public static function processSettingList( $settings, $place, $isAdmin )
    {
        if ( $place != BOL_ComponentService::PLACE_DASHBOARD && !OW::getUser()->isAdmin() )
        {
            $settings['content'] = UTIL_HtmlTag::stripJs($settings['content']);
            //$settings['content'] = UTIL_HtmlTag::stripTags($settings['content'], array('frame'), array(), true, true);
        }
        else
        {
            $settings['content'] = UTIL_HtmlTag::sanitize($settings['content']);
        }

       return parent::processSettingList($settings, $place, $isAdmin);
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_TITLE => OW::getLanguage()->text('base', 'custom_html_widget_default_title')
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }

    public function onBeforeRender()
    {
        $content = $this->nl2br ? nl2br( $this->content ) : $this->content;
        //$content = UTIL_HtmlTag::stripTags($this->content, array(), array(), (bool) $this->nl2br);
        $this->assign('content', $content);
    }
}