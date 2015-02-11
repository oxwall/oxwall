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
 * @author Zarif Safiullin <zaph.saph@gmail.com>
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
class BASE_CMP_SortControl extends OW_Component
{
    const ITEM_LABEL = 'label';
    const ITEM_URL = 'url';
    const ITEM_ISACTIVE = 'isActive';
    
    public $sortItems = array();
    /**
     * Constructor.
     *
     */
    public function __construct( array $sortItems = array() )
    {
        parent::__construct();
        
        if (!empty($sortItems))
        {
            $this->setSortItems($sortItems);
        }
        
        $this->assign('itemList', $this->sortItems);
    }

    public function addItem($sortOrder, $label, $url, $isActive = false)
    {
        $this->sortItems[$sortOrder] = array(
            self::ITEM_LABEL => $label,
            self::ITEM_URL => $url,
            self::ITEM_ISACTIVE => $isActive
        );
    }
    
    public function setActive($sortOrder)
    {
        $this->sortItems[$sortOrder]['isActive'] = true;
    }
    
    public function setSortItems(array $sortItems)
    {
        $this->sortItems = $sortItems;
    }
    
    public function render() {
        
        if (empty($this->sortItems))
        {
            $this->setVisible(false);
        }
        
        $this->assign('itemList', $this->sortItems);
        
        return parent::render();
    }
    
     
}