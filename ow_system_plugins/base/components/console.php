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
 * User console component class.
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
class BASE_CMP_Console extends OW_Component
{

    const EVENT_NAME = 'console.collect_items';

    const ALIGN_LEFT = -1;
    const ALIGN_RIGHT = 0;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $event = new BASE_CLASS_ConsoleItemCollector(self::EVENT_NAME);
        OW::getEventManager()->trigger($event);
        $items = $event->getData();

        $resultItems = array();

        foreach ( $items as $item )
        {
            $itemCmp = null;
            $order = self::ALIGN_LEFT;
            if ( is_array($item) )
            {
                if ( empty($item['item']) )
                {
                    continue;
                }

                $itemCmp = $item['item'];

                $order = isset($item['order']) ? $item['order'] : self::ALIGN_LEFT;
            }
            else
            {
                $itemCmp = $item;
            }

            if ( $order == self::ALIGN_LEFT )
            {
                $order = count($resultItems);
            }

            if ( is_subclass_of($itemCmp, 'OW_Renderable') && $itemCmp->isVisible() )
            {
                $resultItems[] = array(
                    'item' => $itemCmp->render(),
                    'order' => $order
                );
            }
        }

        usort($resultItems, array($this, '_sortItems'));

        $tplItems = array();

        foreach ( $resultItems as $item )
        {
            $tplItems[] = $item['item'];
        }

        $this->assign('items', $tplItems);


        $jsUrl = OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'console.js';
        OW::getDocument()->addScript($jsUrl);

        $event = new OW_Event(BASE_CTRL_Ping::PING_EVENT . '.consoleUpdate');
        OW::getEventManager()->trigger($event);

        $params = array(
            'pingInterval' => 30000
        );

        $js = UTIL_JsGenerator::newInstance();
        $js->newObject(array('OW', 'Console'), 'OW_Console', array($params, $event->getData()));

        OW::getDocument()->addOnloadScript($js, 900);
    }

    public function _sortItems( $item1, $item2 )
    {
        $a = (int) $item1['order'];
        $b = (int) $item2['order'];

        if ($a == $b)
        {
            return 0;
        }

        return ($a > $b) ? -1 : 1;
    }




    /* Deprecated Block */

    const DATA_KEY_ICON_CLASS = 'icon_class';
    const DATA_KEY_URL = 'url';
    const DATA_KEY_ID = 'id';
    const DATA_KEY_BLOCK = 'block';
    const DATA_KEY_BLOCK_ID = 'block_id';
    const DATA_KEY_ITEMS_LABEL = 'block_items_count';
    const DATA_KEY_BLOCK_CLASS = 'block_class';
    const DATA_KEY_TITLE = 'title';
    const DATA_KEY_HIDDEN_CONTENT = 'hidden_content';

    const VALUE_BLOCK_CLASS_GREEN = 'ow_mild_green';
    const VALUE_BLOCK_CLASS_RED = 'ow_mild_red';

}