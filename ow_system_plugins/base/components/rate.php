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
class BASE_CMP_Rate extends OW_Component
{

    public function __construct( $pluginKey, $entityType, $entityId, $ownerId )
    {
        parent::__construct();

        $service = BOL_RateService::getInstance();

        $maxRate = $service->getConfig(BOL_RateService::CONFIG_MAX_RATE);

        $cmpId = uniqid();

        $entityId = (int) $entityId;
        $entityType = trim($entityType);
        $ownerId = (int) $ownerId;

        if ( OW::getUser()->isAuthenticated() )
        {
            $userRateItem = $service->findRate($entityId, $entityType, OW::getUser()->getId());

            if ( $userRateItem !== null )
            {
                $userRate = $userRateItem->getScore();
            }
            else
            {
                $userRate = null;
            }
        }
        else
        {
            $userRate = null;
        }

        $this->assign('maxRate', $maxRate);
        $this->addComponent('totalScore', new BASE_CMP_TotalScore($entityId, $entityType, $maxRate));
        $this->assign('cmpId', $cmpId);

        $jsParamsArray = array(
            'cmpId' => $cmpId,
            'userRate' => $userRate,
            'entityId' => $entityId,
            'entityType' => $entityType,
            'itemsCount' => $maxRate,
            'respondUrl' => OW::getRouter()->urlFor('BASE_CTRL_Rate', 'updateRate'),
            'ownerId' => $ownerId
        );

        OW::getDocument()->addOnloadScript("var rate$cmpId = new OwRate(" . json_encode($jsParamsArray) . "); rate$cmpId.init();");
    }
}