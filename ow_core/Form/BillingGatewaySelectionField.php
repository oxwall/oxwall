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

namespace Oxwall\Core\Form;

use Oxwall\Core\OW;
use Oxwall\Utilities\HtmlTag;

/**
 * Billing adapter interface.
 *
 * @author Egor Bulgakov <egor.bulgakov@gmail.com>
 * @since 1.8.3
 */
class BillingGatewaySelectionField extends FormElement
{

    /**
     * Constructor.
     *
     * @param string $name
     */
    public function __construct( $name )
    {
        parent::__construct($name);
    }

    /**
     * @see FormElement::getElementJs()
     */
    public function getElementJs()
    {
        $js = "var formElement = new OwFormElement('" . $this->getId() . "', '" . $this->getName() . "');";

        return $js;
    }

    /**
     * @see FormElement::renderInput()
     *
     * @param array $params
     * @return string
     */
    public function renderInput( $params = null )
    {
        parent::renderInput($params);

        $name = $this->getName();

        $gateways = $this->getActiveGatewaysList();

        if ( $gateways )
        {
            $paymentOptions = $this->getAdapterData($gateways);

            $gatewaysNumber = count($paymentOptions);

            $id = HtmlTag::generateAutoId('input');

            $urlFieldAttrs = array(
                'type' => 'hidden',
                'id' => 'url-' . $id,
                'value' => '',
                'name' => $name . '[url]'
            );

            $renderedString = HtmlTag::generateTag('input', $urlFieldAttrs);

            $cont_id = $id . '-cont';
            $renderedString .= '<ul class="ow_billing_gateways clearfix" id="' . $cont_id . '">';

            $i = 0;
            foreach ( $paymentOptions as $option )
            {
                $this->addAttributes(array(
                    'type' => 'radio',
                    'rel' => $option['orderUrl'],
                    'value' => $option['dto']->gatewayKey,
                    'name' => $name . '[key]'
                ));

                if ( $i == 0 )
                {
                    $url = $option['orderUrl'];
                    $this->addAttribute(self::ATTR_CHECKED, 'checked');
                }

                if ( $gatewaysNumber == 1 )
                {
                    $renderedString .= '<li style="display: inline-block; padding-right: 20px;">' . OW::getLanguage()->text('base',
                            'billing_pay_with') . '</li>';
                    $field = HtmlTag::generateTag('input',
                            array(
                            'type' => 'hidden',
                            'id' => 'url-' . $id,
                            'value' => $option['dto']->gatewayKey,
                            'name' => $name . '[key]'
                            )
                    );
                }
                else
                {
                    $field = HtmlTag::generateTag('input', $this->attributes);
                }


                $renderedString .= $this->getItemMarkUp($option, $field);
                $i++;
                $this->removeAttribute(self::ATTR_CHECKED);
            }

            $renderedString .= '</ul>';

            $js = 'var $url_field = $("#url-' . $id . '");
                $url_field.val("' . $url . '");
                $("ul#' . $cont_id . ' input").change(function(){
                    $url_field.val($(this).attr("rel"));
                });';

            OW::getDocument()->addOnloadScript($js);
        }
        else
        {
            $renderedString = OW::getLanguage()->text('base', 'billing_no_gateways');
        }

        return $renderedString;
    }

    protected function getItemMarkUp( $option, $field )
    {
        return '<li style="display: inline-block;">
                    <label>' . $field . '<img src="' . $option['logoUrl'] . '" alt="' . $option['dto']->gatewayKey . '" /></label>
                </li>';
    }

    protected function getActiveGatewaysList()
    {
        return \BOL_BillingService::getInstance()->getActiveGatewaysList();
    }

    protected function getAdapterData( $gateways )
    {
        $paymentOptions = array();

        foreach ( $gateways as $gateway )
        {
            /* @var $adapter OW_BillingAdapter */
            if ( $adapter = OW::getClassInstance($gateway->adapterClassName) )
            {
                $paymentOptions[$gateway->gatewayKey]['dto'] = $gateway;
                $paymentOptions[$gateway->gatewayKey]['orderUrl'] = $adapter->getOrderFormUrl();
                $paymentOptions[$gateway->gatewayKey]['logoUrl'] = $adapter->getLogoUrl();
            }
        }

        return $paymentOptions;
    }
}
