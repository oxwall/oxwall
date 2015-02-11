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
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
class BASE_CMP_AddNewContent extends BASE_CLASS_Widget
{
    /**
     * @deprecated contstant
     */
    const REGISTRY_DATA_KEY = 'base_cmp_add_new_item';

    const EVENT_NAME = 'base.add_new_content_item';
    const DATA_KEY_ICON_CLASS = 'iconClass';
    const DATA_KEY_URL = 'url';
    const DATA_KEY_LABEL = 'label';
    const DATA_KEY_ID = 'id';

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $event = new BASE_CLASS_EventCollector(self::EVENT_NAME);
        OW::getEventManager()->trigger($event);
        $data = $event->getData();
        if( empty($data) )
        {
            $this->setVisible(false);
            return;
        }
        $this->assign('items', $event->getData());
    }

    public static function getSettingList()
    {
        return array();
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_TITLE => OW::getLanguage()->text('base', 'component_add_new_box_cap_label'),
            self::SETTING_ICON => self::ICON_ADD
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_MEMBER;
    }
}