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
require_once OW_DIR_ROOT . 'ow_includes/config.php';
require_once OW_DIR_ROOT . 'ow_includes/define.php';
require_once OW_DIR_UTIL . 'debug.php';
require_once OW_DIR_UTIL . 'string.php';
require_once OW_DIR_CORE . 'autoload.php';
require_once OW_DIR_CORE . 'exception.php';
require_once OW_DIR_INC . 'function.php';
require_once OW_DIR_CORE . 'ow.php';
require_once OW_DIR_CORE . 'plugin.php';

mb_internal_encoding('UTF-8');

if ( OW_DEBUG_MODE )
{
    ob_start();
}

spl_autoload_register(array('OW_Autoload', 'autoload'));

// adding standard package pointers
$autoloader = OW::getAutoloader();
$autoloader->addPackagePointer('OW', OW_DIR_CORE);
$autoloader->addPackagePointer('INC', OW_DIR_INC);
$autoloader->addPackagePointer('UTIL', OW_DIR_UTIL);
$autoloader->addPackagePointer('BOL', OW_DIR_SYSTEM_PLUGIN . 'base' . DS . 'bol');

// Force autoload of classes without package pointer
$classesToAutoload = array(
    'Form' => OW_DIR_CORE . 'form.php',
    'TextField' => OW_DIR_CORE . 'form_element.php',
    'HiddenField' => OW_DIR_CORE . 'form_element.php',
    'FormElement' => OW_DIR_CORE . 'form_element.php',
    'RequiredValidator' => OW_DIR_CORE . 'validator.php',
    'StringValidator' => OW_DIR_CORE . 'validator.php',
    'RegExpValidator' => OW_DIR_CORE . 'validator.php',
    'EmailValidator' => OW_DIR_CORE . 'validator.php',
    'UrlValidator' => OW_DIR_CORE . 'validator.php',
    'AlphaNumericValidator' => OW_DIR_CORE . 'validator.php',
    'IntValidator' => OW_DIR_CORE . 'validator.php',
    'FloatValidator' => OW_DIR_CORE . 'validator.php',
    'DateValidator' => OW_DIR_CORE . 'validator.php',
    'CaptchaValidator' => OW_DIR_CORE . 'validator.php',
    'RadioField' => OW_DIR_CORE . 'form_element.php',
    'CheckboxField' => OW_DIR_CORE . 'form_element.php',
    'Selectbox' => OW_DIR_CORE . 'form_element.php',
    'CheckboxGroup' => OW_DIR_CORE . 'form_element.php',
    'RadioField' => OW_DIR_CORE . 'form_element.php',
    'PasswordField' => OW_DIR_CORE . 'form_element.php',
    'Submit' => OW_DIR_CORE . 'form_element.php',
    'Button' => OW_DIR_CORE . 'form_element.php',
    'Textarea' => OW_DIR_CORE . 'form_element.php',
    'FileField' => OW_DIR_CORE . 'form_element.php',
    'TagsField' => OW_DIR_CORE . 'form_element.php',
    'SuggestField' => OW_DIR_CORE . 'form_element.php',
    'MultiFileField' => OW_DIR_CORE . 'form_element.php',
    'Multiselect' => OW_DIR_CORE . 'form_element.php',
    'CaptchaField' => OW_DIR_CORE . 'form_element.php',
    'InvitationFormElement' => OW_DIR_CORE . 'form_element.php',
    'Range' => OW_DIR_CORE . 'form_element.php',
    'WyswygRequiredValidator' => OW_DIR_CORE . 'validator.php',
    'DateField' => OW_DIR_CORE . 'form_element.php',
    'DateRangeInterface' => OW_DIR_CORE . 'form_element.php'
);

OW::getAutoloader()->addClassArray($classesToAutoload);

if ( defined("OW_URL_HOME") )
{
    OW::getRouter()->setBaseUrl(OW_URL_HOME);
}

if ( OW_PROFILER_ENABLE )
{
    UTIL_Profiler::getInstance();
}

require_once OW_DIR_SYSTEM_PLUGIN . 'base' . DS . 'classes' . DS . 'file_log_writer.php';
require_once OW_DIR_SYSTEM_PLUGIN . 'base' . DS . 'classes' . DS . 'db_log_writer.php';
require_once OW_DIR_SYSTEM_PLUGIN . 'base' . DS . 'classes' . DS . 'err_output.php';

$errorManager = OW_ErrorManager::getInstance(OW_DEBUG_MODE);
$errorManager->setErrorOutput(new BASE_CLASS_ErrOutput());
