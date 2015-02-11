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
 * @author Sergey Kambalin <greyexpert@gmail.com>
 * @package ow_system_plugins.base.mobile.components
 * @since 1.6.0
 */
abstract class BASE_MCMP_AbstractButtonList extends OW_MobileComponent
{
    protected function prepareItem( $item, $defaultClass = "" )
    {
        $action = array();

        $action['label'] = $item["label"];
        $action['order'] = 999;

        $attrs = isset($item["attributes"]) && is_array($item["attributes"])
            ? $item["attributes"]
            : array();

        $attrs['class'] = empty($attrs['class']) 
                ? $defaultClass 
                : $defaultClass . " " . $attrs['class'];

        $attrs['href'] = isset($item["href"]) ? $item["href"] : 'javascript://';

        if ( isset($item["id"]) )
        {
            $attrs['id'] = $item["id"];
        }

        if ( isset($item["class"]) )
        {
            $attrs['class'] .= " " . $item["class"];
        }

        if ( isset($item["order"]) )
        {
            $action['order'] = $item["order"];
        }

        $_attrs = array();
        foreach ( $attrs as $name => $value )
        {
            $_attrs[] = $name . '="' . $value . '"';
        }

        $action['attrs'] = implode(' ', $_attrs);
        
        return $action;
    }
    
    protected function getSortedItems( $items )
    {
        usort($items, array($this, "itemsSorter"));
        
        return $items;
    }
    
    private function itemsSorter( $a, $b )
    {
        if ($a["order"] == $b["order"]) {
            return 0;
        }
        return ($a["order"] < $b["order"]) ? -1 : 1;
    }
}