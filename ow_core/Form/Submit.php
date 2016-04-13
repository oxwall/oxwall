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

use Oxwall\Core\OW as OW;

/**
 * Form element: Submit.
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @since 1.8.3
 */
class Submit extends FormElement
{
    private $decorator;

    /**
     * Constructor.
     *
     * @param string $name
     */
    public function __construct( $name, $decorator = "button" )
    {
        parent::__construct($name);

        $this->addAttribute("type", "submit");

        $this->setValue(OW::getLanguage()->text("base", "form_element_submit_default_value"));
        $this->decorator = $decorator;
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

        $this->addAttribute("value", $this->getValue());

        if ( $params === null )
        {
            $params = array();
        }

        $params = array_merge($params, $this->attributes);
        $params["label"] = $params["value"];

        $extraString = "";

        foreach ( $this->attributes as $attr => $val )
        {
            if ( !in_array($attr, array("class", "id", "buttonName", "langLabel", "label", "type")) )
            {
                $extraString .= $attr . '="' . $val . '" ';
            }
        }

        $params["extraString"] = $extraString;

        if ( $this->decorator !== false )
        {
            $finalMarkup = OW::getThemeManager()->processDecorator("button", $params);
        }
        else
        {
            $finalMarkup = \Oxwall\Utilities\HtmlTag::generateTag("input", $params);
        }

        return $finalMarkup;
    }
}
