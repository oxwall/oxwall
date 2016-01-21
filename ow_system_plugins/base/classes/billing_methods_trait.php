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
 * Billing trait class
 *
 * @author Sergey Pryadkin <GiperProger@gmail.com>
 * @package ow.ow_system_plugins.base.classes
 * @since 1.8.2
 */

trait BASE_CLASS_BillingMethodsTrait
{
    /**
     * Controller action for completed orders

     * @param array $params
     */
    public function completed( array $params )
    {
        $billingService = BOL_BillingService::getInstance();
        $lang = OW::getLanguage();

        if ( isset($params['hash']) )
        {
            if ( !$sale = $billingService->getSaleByHash($params['hash']) )
            {
                $msg = $lang->text('base', 'billing_sale_not_found');
            }
            else
            {
                switch ( $sale->status )
                {
                    case BOL_BillingSaleDao::STATUS_DELIVERED:
                        $msg = $lang->text('base', 'billing_order_completed_successfully');
                        break;

                    case BOL_BillingSaleDao::STATUS_VERIFIED:
                        $msg = $lang->text('base', 'billing_order_verified');
                        break;

                    case BOL_BillingSaleDao::STATUS_PREPARED:
                    case BOL_BillingSaleDao::STATUS_PROCESSING:
                        $msg = $lang->text('base', 'billing_order_processing');
                        break;

                    case BOL_BillingSaleDao::STATUS_ERROR:
                        $msg = $lang->text('base', 'billing_order_failed');
                        break;

                    default:
                        $msg = $lang->text('base', 'billing_order_failed');
                        break;
                }
            }
        }
        else
        {
            $msg = $lang->text('base', 'billing_order_completed_successfully');
        }

        $this->assign('message', $msg);

        $this->setPageHeading($lang->text('base', 'billing_order_status_page_heading'));
        $this->setPageHeadingIconClass('ow_ic_cart');
    }

    /**
     * Controller action for canceled orders
     *
     * @param $params
     */
    public function canceled( array $params )
    {
        $this->assign('message', OW::getLanguage()->text('base', 'billing_order_canceled'));

        $this->setPageHeading(OW::getLanguage()->text('base', 'billing_order_status_page_heading'));
        $this->setPageHeadingIconClass('ow_ic_cart');
    }

    /**
     * Controller action for failed orders
     *
     * @param $params
     */
    public function error( array $params )
    {
        $this->assign('message', OW::getLanguage()->text('base', 'billing_order_failed'));

        $this->setPageHeading(OW::getLanguage()->text('base', 'billing_order_status_page_heading'));
        $this->setPageHeadingIconClass('ow_ic_cart');
    }

    public function saveGatewayProduct()
    {
        if ( OW::getRequest()->isPost() && $_POST['action'] == 'update_products' )
        {
            $service = BOL_BillingService::getInstance();

            foreach ( $_POST['products'] as $id => $prodId )
            {
                $service->updateGatewayProduct($id, $prodId);
            }

            OW::getFeedback()->info(OW::getLanguage()->text('admin', 'settings_submit_success_message'));
            OW::getApplication()->redirect(urldecode($_POST['back_url']));
        }
    }
}
