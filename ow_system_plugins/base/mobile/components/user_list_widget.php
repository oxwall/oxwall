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
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
class BASE_MCMP_UserListWidget extends BASE_CMP_UserListWidget
{
    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct( $params );
        //$this->setSettingValue('capContent',  );
        //printVar($this->getComponent('menu')->render());
        $params->standartParamList->capContent = $this->getComponent('menu')->render();
        $this->setTemplate(OW::getPluginManager()->getPlugin('base')->getMobileCmpViewDir() . 'user_list_widget.html');
    }

    public function getData( BASE_CLASS_WidgetParameter $params )
    {
        return parent::getData($params);
    }

    protected function getUsersCmp( $list )
    {
        return new BASE_MCMP_AvatarUserList($list);
    }

    protected function getMenuCmp( $menuItems )
    {
        return new BASE_MCMP_WidgetMenu($menuItems);
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_TITLE => OW::getLanguage()->text('base', 'user_list_widget_settings_title'),
            self::SETTING_ICON => self::ICON_USER
        );
    }
}