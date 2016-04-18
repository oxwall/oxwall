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
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @since 1.8.3
 */
class DateRange extends FormElement implements DateRangeInterface
{
    protected $minDate;
    protected $maxDate;
    protected $defaultRange = array();

    /**
     * Constructor.
     *
     * @param string $name
     */
    public function __construct( $name )
    {
        parent::__construct($name);

        $this->minDate = new DateField($name . '[from]');
        $this->maxDate = new DateField($name . '[to]');

        $this->minDate->setMaxYear(date("Y"));
        //$this->minDate->setValue($this->minDate->getMinYear().'/1/1');

        $this->maxDate->setMaxYear(date("Y"));
        //$this->maxDate->setValue(date("Y").'/12/31');
        /* @var $this->minDate = DateField */
    }

    /**
     * Sets form element value.
     *
     * @param array $value

     * @return FormElement
     */
    public function setValue( $value )
    {
        if ( isset($value['from']) && isset($value['to']) )
        {
            $this->minDate->setValue($value['from']);
            $this->maxDate->setValue($value['to']);
        }

        return $this;
    }

    public function getValue()
    {
        $value = array(
            'from' => $this->minDate->getValue(),
            'to' => $this->maxDate->getValue()
        );

        return $value;
    }

    public function getMinYear()
    {
        return $this->minDate->getMinYear();
    }

    public function getMaxYear()
    {
        return $this->minDate->getMaxYear();
    }

    public function setMaxYear( $year )
    {
        $this->minDate->setMaxYear($year);
        $this->maxDate->setMaxYear($year);

        return $this;
    }

    public function setMinYear( $year )
    {
        $this->minDate->setMinYear($year);
        $this->maxDate->setMinYear($year);

        return $this;
    }

    public function renderInput( $params = null )
    {
        parent::renderInput($params);

        $language = OW::getLanguage();

        $result = '<div id="' . $this->getAttribute('id') . '" class="' . $this->getAttribute('name') . '">
                       ' . $language->text('base', 'form_element_from') . '  <div class="ow_inline">' . ( $this->minDate->renderInput() ) . '</div>
                       ' . $language->text('base', 'form_element_to') . '
                       <div class="ow_inline">' . ( $this->maxDate->renderInput() ) . '</div>
                    </div>';

        return $result;
    }
}