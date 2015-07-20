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
 * Widget Settings
 *
 * @author Sergey Kambalin <greyexpert@gmail.com>
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
class BASE_CMP_ComponentSettings extends OW_Component
{
    /**
     * Component default settings
     *
     * @var array
     */
    private $defaultSettingList = array();
    /**
     * Component default settings
     *
     * @var array
     */
    private $componentSettingList = array();
    private $standardSettingValueList = array();
    private $hiddenFieldList = array();
    private $access;

    private $uniqName;

    /**
     * Class constructor
     *
     * @param array $menuItems
     */
    public function __construct( $uniqName, array $componentSettings = array(), array $defaultSettings = array(), $access = null )
    {
        parent::__construct();

        $this->componentSettingList = $componentSettings;
        $this->defaultSettingList = $defaultSettings;
        $this->uniqName = $uniqName;
        $this->access = $access;
        
        $tpl = OW::getPluginManager()->getPlugin("base")->getCmpViewDir() . "component_settings.html";
        $this->setTemplate($tpl);
    }

    public function setStandardSettingValueList( $valueList )
    {
        $this->standardSettingValueList = $valueList;
    }

    protected function makeSettingList( $defaultSettingList )
    {
        $settingValues = $this->standardSettingValueList;
        foreach ( $defaultSettingList as $name => $value )
        {
            $settingValues[$name] = $value;
        }

        return $settingValues;
    }

    public function markAsHidden( $settingName )
    {
        $this->hiddenFieldList[] = $settingName;
    }

    /**
     * @see OW_Renderable::onBeforeRender()
     *
     */
    public function onBeforeRender()
    {
        $settingValues = $this->makeSettingList($this->defaultSettingList);

        $this->assign('values', $settingValues);

        $this->assign('avaliableIcons', IconCollection::allWithLabel());

        foreach ( $this->componentSettingList as $name => & $setting )
        {
            if ( $setting['presentation'] == BASE_CLASS_Widget::PRESENTATION_HIDDEN )
            {
                unset($this->componentSettingList[$name]);
                continue;
            }

            if ( isset($settingValues[$name]) )
            {
                $setting['value'] = $settingValues[$name];
            }

            if ( $setting['presentation'] == BASE_CLASS_Widget::PRESENTATION_CUSTOM )
            {
                $setting['markup'] = call_user_func($setting['render'], $this->uniqName, $name, empty($setting['value']) ? null : $setting['value']);
            }

            $setting['display'] = !empty($setting['display']) ? $setting['display'] : 'table';
        }

        $this->assign('settings', $this->componentSettingList);


        $authorizationService = BOL_AuthorizationService::getInstance();

        $roleList = array();
        $isModerator = OW::getUser()->isAuthorized('base');
        
        if ( $this->access == BASE_CLASS_Widget::ACCESS_GUEST || !$isModerator )
        {
            $this->markAsHidden(BASE_CLASS_Widget::SETTING_RESTRICT_VIEW);
        }
        else
        {
            $roleList = $authorizationService->findNonGuestRoleList();

            if ( $this->access == BASE_CLASS_Widget::ACCESS_ALL )
            {
                $guestRoleId = $authorizationService->getGuestRoleId();
                $guestRole = $authorizationService->getRoleById($guestRoleId);
                array_unshift($roleList, $guestRole);
            }
        }

        $this->assign('roleList', $roleList);

        $this->assign('hidden', $this->hiddenFieldList);
    }

}

class IconCollection
{
    private static $all = array(
        "ow_ic_add",
        "ow_ic_aloud",
        "ow_ic_app",
        "ow_ic_attach",
        "ow_ic_birthday",
        "ow_ic_bookmark",
        "ow_ic_calendar",
        "ow_ic_cart",
        "ow_ic_chat",
        "ow_ic_clock",
        "ow_ic_comment",
        "ow_ic_cut",
        "ow_ic_dashboard",
        "ow_ic_delete",
        "ow_ic_down_arrow",
        "ow_ic_edit",
        "ow_ic_female",
        "ow_ic_file",
        "ow_ic_files",
        "ow_ic_flag",
        "ow_ic_folder",
        "ow_ic_forum",
        "ow_ic_friends",
        "ow_ic_gear_wheel",
        "ow_ic_help",
        "ow_ic_heart",
        "ow_ic_house",
        "ow_ic_info",
        "ow_ic_key",
        "ow_ic_left_arrow",
        "ow_ic_lens",
        "ow_ic_link",
        "ow_ic_lock",
        "ow_ic_mail",
        "ow_ic_male",
        "ow_ic_mobile",
        "ow_ic_moderator",
        "ow_ic_monitor",
        "ow_ic_move",
        "ow_ic_music",
        "ow_ic_new",
        "ow_ic_ok",
        "ow_ic_online",
        "ow_ic_picture",
        "ow_ic_plugin",
        "ow_ic_push_pin",
        "ow_ic_reply",
        "ow_ic_right_arrow",
        "ow_ic_rss",
        "ow_ic_save",
        "ow_ic_script",
        "ow_ic_server",
        "ow_ic_star",
        "ow_ic_tag",
        "ow_ic_trash",
        "ow_ic_unlock",
        "ow_ic_up_arrow",
        "ow_ic_update",
        "ow_ic_user",
        "ow_ic_video",
        "ow_ic_warning",
        "ow_ic_write"
    );

    public static function all()
    {
        return self::$all;
    }

    public static function allWithLabel()
    {
        $out = array();

        foreach ( self::$all as $icon )
        {
            $item = array();
            $item['class'] = $icon;
            $item['label'] = ucfirst(str_replace('_', ' ', substr($icon, 6)));
            $out[] = $item;
        }

        return $out;
    }
}
