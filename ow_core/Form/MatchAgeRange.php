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

/**
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @since 1.8.3
 */
class MatchAgeRange extends AgeRange
{

    /**
     * Sets form element value.
     *
     * @param array $value

     * @return FormElement
     */
    public function setValue( $value )
    {
        if ( empty($value) )
        {
            $this->value = null;
        }

        if ( is_array($value) )
        {
            if ( empty($value) )
            {
                $this->value = '';
            }
            else
            {
                $this->value = $value['from'] . '-' . $value['to'];
            }
        }
        else
        {
            if ( empty($value) )
            {
                $this->value = array();
            }
            else
            {
                $valueArray = explode('-', $value);
                $value = array(
                    'from' => $valueArray[0],
                    'to' => $valueArray[1]
                );

                if ( (int) $value['from'] >= $this->minAge && (int) $value['from'] <= $this->maxAge &&
                    (int) $value['to'] >= $this->minAge && (int) $value['to'] <= $this->maxAge &&
                    (int) $value['from'] <= (int) $value['to'] )
                {
                    $this->value['from'] = (int) $value['from'];
                    $this->value['to'] = (int) $value['to'];
                }
            }
        }
    }

    public function getElementJs()
    {
        $js = "var formElement = new OwRange(" . json_encode($this->getId()) . ", " . json_encode($this->getName()) . ");";

        /** @var $value Validator  */
        foreach ( $this->validators as $value )
        {
            $js .= "formElement.addValidator(" . $value->getJsValidator() . ");";
        }

        return $js;
    }
}
