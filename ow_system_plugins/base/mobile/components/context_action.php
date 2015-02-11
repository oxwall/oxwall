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
class BASE_MCMP_ContextAction extends BASE_MCMP_AbstractButtonList
{
    protected $items = array();
    protected $uniqId;
    
    /**
     * Constructor.
     */
    public function __construct( $items, $label = null )
    {
        parent::__construct();
        
        if ( empty($items) )
        {
            $this->setVisible(false);
        }
        
        $this->items = $items;
        $this->uniqId = uniqid("ca-");
        
        $this->assign("uniqId", $this->uniqId);
        $this->assign("label", $label);
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();
        
        $this->initList();
        
        $js = UTIL_JsGenerator::newInstance();
        $js->jQueryEvent("#" . $this->uniqId . " .ca-dropdown-btn", "click", 
                'var dd = $(this).parents(".ca-dropdown-wrap:eq(0)").find(".ca-dropdown"); isVisible = dd.is(":visible"); '
                . '$(".ca-dropdown:visible").hide(); '
                . 'return isVisible ? (dd.hide(), true) : (dd.show(), false);');
        
        $js->addScript('$(document).on("click", function(e) { return $(e.target).is(".ca-dropdown, .ca-dropdown *") ? false : $(".ca-dropdown:visible").hide(), true; });');
        
        OW::getDocument()->addOnloadScript($js);
    }

    protected function initList()
    {
        $tplActions = array();

        foreach ( $this->items as $item  )
        {
            $tplActions[] = $this->prepareItem($item, "owm_context_action_list_item");
        }
       
        $this->assign("buttons", $this->getSortedItems($tplActions));
    }
}