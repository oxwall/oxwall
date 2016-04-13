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
 * Base validator class.
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_core
 * @since 1.0
 */
abstract class OW_Validator extends Oxwall\Core\Form\Validator
{
    
}

/**
 * Required validator.
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_core
 * @since 1.0
 */
class RequiredValidator extends Oxwall\Core\Form\RequiredValidator
{
    
}

/**
 * Wyswyg required validator.
 *
 * @author Alex Ermashev <alexermashev@gmail.com>
 * @package ow_core
 * @since 1.0
 */
class WyswygRequiredValidator extends Oxwall\Core\Form\WyswygRequiredValidator
{
    
}

/**
 * StringValidator validates String.
 *
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow_core
 * @since 1.0
 */
class StringValidator extends Oxwall\Core\Form\StringValidator
{
    
}

/**
 * RegExpValidator validates value by RegExp.
 *
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow_core
 * @since 1.0
 */
class RegExpValidator extends Oxwall\Core\Form\RegExpValidator
{
    
}

/**
 * EmailValidator validates Email.
 *
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow_core
 * @since 1.0
 */
class EmailValidator extends Oxwall\Core\Form\EmailValidator
{
    
}

/**
 * UrlValidator validates Url.
 *
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow_core
 * @since 1.0
 */
class UrlValidator extends Oxwall\Core\Form\UrlValidator
{
    
}

/**
 * AlphaNumericValidator
 *
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow_core
 * @since 1.0
 */
class AlphaNumericValidator extends Oxwall\Core\Form\AlphaNumericValidator
{
    
}

/**
 * In array validator
 *
 * @author Alex Ermashev <alexermashev@gmail.com>
 * @package ow_core
 * @since 8.1
 */
class InArrayValidator extends Oxwall\Core\Form\InArrayValidator
{
    
}

/**
 * IntValidator
 *
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow_core
 * @since 1.0
 */
class IntValidator extends Oxwall\Core\Form\IntValidator
{
    
}

/**
 * Oxwall: Open Source Community Software
 * @copyright Skalfa LLC Copyright (C) 2009. All rights reserved.
 * @license CPAL 1.0 License - http://www.oxwall.org/license
 */

/**
 * FloatValidator
 *
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow_core
 * @since 1.0
 */
class FloatValidator extends Oxwall\Core\Form\FloatValidator
{
    
}

/**
 * DateValidator
 *
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow_core
 * @since 1.0
 */
class DateValidator extends Oxwall\Core\Form\DateValidator
{
    
}

/**
 * DateValidator
 *
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow_core
 * @since 1.0
 */
class CaptchaValidator extends Oxwall\Core\Form\CaptchaValidator
{
    
}

class RangeValidator extends Oxwall\Core\Form\RangeValidator
{
    
}
