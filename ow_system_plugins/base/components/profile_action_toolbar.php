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
class BASE_CMP_ProfileActionToolbar extends OW_Component
{
    /**
     * @deprecated constant
     */
    const REGISTRY_DATA_KEY = 'base_cmp_profile_action_toolbar';

    const EVENT_NAME = 'base.add_profile_action_toolbar';
    const EVENT_PROCESS_TOOLBAR = 'base.process_profile_action_toolbar';
    const DATA_KEY_LABEL = 'label';
    const DATA_KEY_EXTRA_LABEL = 'extraLabel';
    const DATA_KEY_LINK_ID = 'id';
    const DATA_KEY_LINK_CLASS = 'linkClass';
    const DATA_KEY_CMP_CLASS = 'cmpClass';
    const DATA_KEY_LINK_HREF = 'href';
    const DATA_KEY_LINK_ORDER = 'order';
    const DATA_KEY_ITEM_KEY = 'key';

    const DATA_KEY_LINK_ATTRIBUTES = 'attributes';
    const DATA_KEY_LINK_GROUP_KEY = 'groupKey';
    const DATA_KEY_LINK_GROUP_LABEL = 'groupLabel';

    const GROUP_GLOBAL = 'global';

    protected $userId;
    protected $shownButtonsCount = 3;


    /**
     * Constructor.
     */
    public function __construct( $userId )
    {
        parent::__construct();

        $this->userId = (int) $userId;
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $event = new BASE_CLASS_EventCollector(self::EVENT_NAME, array('userId' => $this->userId));

        OW::getEventManager()->trigger($event);

        $event = new OW_Event(self::EVENT_PROCESS_TOOLBAR, array('userId' => $this->userId), $event->getData());

        OW::getEventManager()->trigger($event);

        $addedData = $event->getData();

        if ( empty($addedData) )
        {
            $this->setVisible(false);

            return;
        }

        $this->initToolbar($addedData);
    }

    public function initToolbar( $items )
    {
        $cmpsMarkup = '';
        $tplActions = array();
        $tplGroups = array();
        
        $maxOrder = count($items);
        
        foreach ( $items as $item  )
        {
            $action = array();
            
            $action["order"] = isset($item["order"]) ? $item["order"] : $maxOrder;
                    
            $action['label'] = isset($item[self::DATA_KEY_LABEL]) ? $item[self::DATA_KEY_LABEL] : null;
            $action["key"] = isset($item[self::DATA_KEY_ITEM_KEY]) ? $item[self::DATA_KEY_ITEM_KEY] : null;
            $action["html"] = isset($item["html"]) ? $item["html"] : null;

            $attrs = isset($item[self::DATA_KEY_LINK_ATTRIBUTES]) && is_array($item[self::DATA_KEY_LINK_ATTRIBUTES])
                ? $item[self::DATA_KEY_LINK_ATTRIBUTES]
                : array();

            $attrs['href'] = isset($item[self::DATA_KEY_LINK_HREF]) ? $item[self::DATA_KEY_LINK_HREF] : 'javascript://';

            if ( isset($item[self::DATA_KEY_LINK_ID]) )
            {
                $attrs['id'] = $item[self::DATA_KEY_LINK_ID];
            }

            if ( isset($item[self::DATA_KEY_LINK_CLASS]) )
            {
                $attrs['class'] = $item[self::DATA_KEY_LINK_CLASS];
            }

            if ( isset($item[self::DATA_KEY_CMP_CLASS]) )
            {
                $cmpClass = trim($item[self::DATA_KEY_CMP_CLASS]);

                $cmp = OW::getEventManager()->call('class.get_instance', array(
                    'className' => $cmpClass,
                    'arguments' => array(
                        array('userId' => $this->userId)
                    )
                ));

                $cmp = $cmp === null ? new $cmpClass(array('userId' => $this->userId)) : $cmp;

                $cmpsMarkup .= $cmp->render();
            }

            $_attrs = array();
            foreach ( $attrs as $name => $value )
            {
                $_attrs[] = $name . '="' . $value . '"';
            }

            $action['attrs'] = implode(' ', $_attrs);
            $action["attrsArr"] = $attrs;
            
            
            
            if ( !empty($item[self::DATA_KEY_LINK_GROUP_KEY]) )
            {
                if ( empty($tplGroups[$item[self::DATA_KEY_LINK_GROUP_KEY]]) )
                {
                    $tplGroups[$item[self::DATA_KEY_LINK_GROUP_KEY]] = array(
                        "key" => $item[self::DATA_KEY_LINK_GROUP_KEY],
                        "label" => $item[self::DATA_KEY_LINK_GROUP_LABEL],
                        "toolbar" => array()
                    );
                }
                $tplGroups[$item[self::DATA_KEY_LINK_GROUP_KEY]]["toolbar"][] = $action;
            }
            elseif (isset($item[self::DATA_KEY_EXTRA_LABEL]) && !empty($item[self::DATA_KEY_EXTRA_LABEL]))
            {
                $label = $action['label'];
                $action['label'] = $item[self::DATA_KEY_EXTRA_LABEL];
                $fake_group = array(
                    'key' => $action['key'],
                    'label' => $label,
                    'toolbar' => array($action)
                );
                $action[self::DATA_KEY_EXTRA_LABEL] = $this->getGroupMenu($fake_group);
                unset($action['html']);
                $tplActions[] = $action;
            }
            else
            {
                $tplActions[] = $action;
            }
        }
        
        usort($tplActions, array($this, "sortCallback"));
        $visibleActions = array_slice($tplActions, 0, $this->shownButtonsCount);
        $moreActions = array_slice($tplActions, $this->shownButtonsCount);

        $this->assign('toolbar', $visibleActions);
        
        $moreGroup = array(
            "key" => "base.more",
            "label" => OW::getLanguage()->text("base", "more"),
            "toolbar" => $moreActions
        );
        
        array_unshift($tplGroups, $moreGroup);
        
        foreach ( array_keys($tplGroups) as $key )
        {
            $tplGroups[$key] = $this->getGroupMenu($tplGroups[$key]);
        }
        
        $this->assign('groups', $tplGroups);
        $this->assign('cmpsMarkup', $cmpsMarkup);
    }
    
    public function sortCallback($a, $b)
    {
        if( $a['order'] == $b['order']) {
            return 0;
        }
        
        return $a['order'] < $b['order'] ? -1 : 1;
    }
    
    public function getGroupMenu( $group )
    {
        if ( empty($group["toolbar"]) )
        {
            return "";
        }
        
        $contextActionMenu = new BASE_CMP_ContextAction();
        $contextActionMenu->setClass("ow_profile_toolbar_group ow_context_action_value_block");
        
        $contextParentAction = new BASE_ContextAction();
        $contextParentAction->setKey($group["key"]);
        $contextParentAction->setLabel($group["label"]);
        
        $contextActionMenu->addAction($contextParentAction);

        usort($group["toolbar"], array($this, "sortCallback"));
        
        foreach ( $group["toolbar"] as $action )
        {
            $attrs = empty($action["attrsArr"]) ? array() : $action["attrsArr"];
            
            $contextAction = new BASE_ContextAction();
            $contextAction->setParentKey($contextParentAction->getKey());
            $contextAction->setLabel($action["label"]);
            
            if ( !empty($attrs["href"]) )
            {
                $contextAction->setUrl($attrs["href"]);
                unset($attrs["href"]);
            }
            
            if ( !empty($attrs["id"]) )
            {
                $contextAction->setId($attrs["id"]);
                unset($attrs["id"]);
            }
            
            if ( !empty($attrs["class"]) )
            {
                $contextAction->setClass($attrs["class"]);
                unset($attrs["class"]);
            }
            
            foreach ( $attrs as $name => $value )
            {
                $contextAction->addAttribute($name, $value);
            }
            
            $contextAction->setKey($action["key"]);
            $contextAction->setOrder($action["order"]);

            $contextActionMenu->addAction($contextAction);
        }
        
        return $contextActionMenu->render();
    }

    /*public function initToolbar( $items )
    {
        $cmpsMarkup = '';
        $ghroupsCount = 0;

        $tplActions = array();

        foreach ( $items as $item  )
        {
            $action = &$tplActions[];

            $action['label'] = $item[self::DATA_KEY_LABEL];
            $action['order'] = count($tplActions);

            $attrs = isset($item[self::DATA_KEY_LINK_ATTRIBUTES]) && is_array($item[self::DATA_KEY_LINK_ATTRIBUTES])
                ? $item[self::DATA_KEY_LINK_ATTRIBUTES]
                : array();

            $attrs['href'] = isset($item[self::DATA_KEY_LINK_HREF]) ? $item[self::DATA_KEY_LINK_HREF] : 'javascript://';

            if ( isset($item[self::DATA_KEY_LINK_ID]) )
            {
                $attrs['id'] = $item[self::DATA_KEY_LINK_ID];
            }

            if ( isset($item[self::DATA_KEY_LINK_CLASS]) )
            {
                $attrs['class'] = $item[self::DATA_KEY_LINK_CLASS];
            }
            
            if ( isset($item[self::DATA_KEY_LINK_ORDER]) )
            {
                $action['order'] = $item[self::DATA_KEY_LINK_ORDER];
            }

            if ( isset($item[self::DATA_KEY_CMP_CLASS]) )
            {
                $cmpClass = trim($item[self::DATA_KEY_CMP_CLASS]);

                $cmp = OW::getClassInstance($cmpClass, array(
                    'userId' => $this->userId
                ));

                $cmpsMarkup .= $cmp->render();
            }

            $_attrs = array();
            foreach ( $attrs as $name => $value )
            {
                $_attrs[] = $name . '="' . $value . '"';
            }

            $action['attrs'] = implode(' ', $_attrs);
        }

        $this->assign('toolbar', $tplActions);
        $this->assign('cmpsMarkup', $cmpsMarkup);
    }*/
}