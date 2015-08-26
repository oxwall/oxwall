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
 * RSS widget
 *
 * @author Sergey Kambalin <greyexpert@gmail.com>
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
require_once OW_DIR_LIB . 'rss' . DS . 'rss.php';

class BASE_CMP_RssWidget extends BASE_CLASS_Widget
{
    private $rss = array();

    private $titleOnly = false;

    private static $countInterval = array(1, 10);

    private $count = 5;

    public function __construct( BASE_CLASS_WidgetParameter $param )
    {
        parent::__construct();

        $this->titleOnly = (bool)$param->customParamList['title_only'];
        $this->assign('titleOnly', $this->titleOnly);
        $url = trim($param->customParamList['rss_url']);

        if ( !$url )
        {
            return;
        }

        $cacheKey = 'rss_widget_cache_' . $url;
        $cachedState = OW::getCacheService()->get($cacheKey);

        if ( $cachedState === false )
        {
            try
            {
                $rssLoading = OW::getConfig()->getValue('base', 'rss_loading');

                if ( !empty($rssLoading) && ( time() - $rssLoading ) < ( 60 * 5 ) )
                {
                    return;
                }
                else if ( $rssLoading === null )
                {
                    OW::getConfig()->addConfig('base', 'rss_loading', time());
                }
                else
                {
                    OW::getConfig()->saveConfig('base', 'rss_loading', time());
                }

                $rssIterator = RssParcer::getIterator($param->customParamList['rss_url'], self::$countInterval[1]);

                OW::getConfig()->saveConfig('base', 'rss_loading', 0);
            }
            catch (Exception $e)
            {
                OW::getConfig()->saveConfig('base', 'rss_loading', 0);

                return;
            }

            foreach ( $rssIterator as $item )
            {
                $item->time = strtotime($item->date);
                $this->rss[] = (array) $item;
            }

            try
            {
                OW::getCacheService()->set($cacheKey, json_encode($this->rss), 60 * 60);
            }
            catch (Exception $e) {}
        }
        else
        {
            $this->rss = (array) json_decode($cachedState, true);
        }

        $this->count = intval($param->customParamList['item_count']);
    }

    public function render()
    {
        $rss = array_slice($this->rss, 0, $this->count);
        $this->assign('rss', $rss);

        $toolbars = array();
        if ( !$this->titleOnly )
        {
            foreach ( $rss as $key => $item )
            {
                $toolbars[$key] = array(array('label' => UTIL_DateTime::formatDate($item['time'])));
            }
        }
        $this->assign('toolbars', $toolbars);

        return parent::render();
    }

    public static function getSettingList()
    {
        $settingList = array();
        $settingList['rss_url'] = array(
            'presentation' => self::PRESENTATION_TEXT,
            'label' => OW::getLanguage()->text('base', 'rss_widget_url_label'),
            'value' => ''
        );

        $settingList['item_count'] = array(
            'presentation' => self::PRESENTATION_SELECT,
            'label' => OW::getLanguage()->text('base', 'rss_widget_count_label'),
            'value' => 5
        );

        for ( $i = self::$countInterval[0]; $i <= self::$countInterval[1]; $i++ )
        {
            $settingList['item_count']['optionList'][$i] = $i;
        }

        $settingList['title_only'] = array(
            'presentation' => self::PRESENTATION_CHECKBOX,
            'label' => OW::getLanguage()->text('base', 'rss_widget_title_only_label'),
            'value' => false
        );

        return $settingList;
    }

    public static function validateSettingList( $settingList )
    {
        parent::validateSettingList($settingList);

        if ( !UTIL_Validator::isUrlValid($settingList['rss_url']) )
        {
            throw new WidgetSettingValidateException(OW::getLanguage()->text('base', 'rss_widget_url_invalid_msg'), 'rss_url');
        }
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_TITLE => OW::getLanguage()->text('base', 'rss_widget_default_title'),
            self::SETTING_ICON => self::ICON_RSS
        );
    }


}