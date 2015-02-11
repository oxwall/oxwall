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
 * Finance action controller
 *
 * @author Egor Bulgakov <egor.bulgakov@gmail.com>
 * @package ow.ow_system_plugins.admin.controllers
 * @since 1.0
 */
class ADMIN_CTRL_Finance extends ADMIN_CTRL_Abstract
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Finance list page controller
     *
     * @param array $params
     */
    public function index( array $params )
    {
        $service = BOL_BillingService::getInstance();
        $lang = OW::getLanguage();

        $page = isset($_GET['page']) ? $_GET['page'] : 1;
        $onPage = 20;
        $list = $service->getFinanceList($page, $onPage);

        $userIdList = array();
        foreach ( $list as $sale )
        {
            if ( isset($sale['userId']) && !in_array($sale['userId'], $userIdList))
            {
                array_push($userIdList, $sale['userId']);
            }
        }
        
        $displayNames = BOL_UserService::getInstance()->getDisplayNamesForList($userIdList);
        $userNames = BOL_UserService::getInstance()->getUserNamesForList($userIdList);

        $this->assign('list', $list);
        $this->assign('displayNames', $displayNames);
        $this->assign('userNames', $userNames);
        
        $total = $service->countSales();
        
        // Paging
        $pages = (int) ceil($total / $onPage);
        $paging = new BASE_CMP_Paging($page, $pages, 10);
        $this->assign('paging', $paging->render());
    
        $this->assign('total', $total);
        
        $stats = $service->getTotalIncome();
        $this->assign('stats', $stats);
        
        OW::getDocument()->setHeading($lang->text('admin', 'page_title_finance'));
        OW::getDocument()->setHeadingIconClass('ow_ic_app');
    }
}