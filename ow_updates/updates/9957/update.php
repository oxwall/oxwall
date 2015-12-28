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

$tblPrefix = OW_DB_PREFIX;
$dbo = Updater::getDbo();

$logger = Updater::getLogger();

$languageService = Updater::getLanguageService();

$languages = $languageService->getLanguages();
$langId = null;

foreach ($languages as $lang)
{
    if ($lang->tag == 'en')
    {
        $langId = $lang->id;
        break;
    }
}

if ($langId !== null)
{
    $languageService->addOrUpdateValue($langId, 'base',  'questions_question_presentation_select_label',
        'Single Choice - Optimized (Faster results, limited values)');
    $languageService->addOrUpdateValue($langId, 'admin', 'questions_possible_values_description',
        'Add up to 31 values. Field values are stored in a specific way in the database to greatly speed up profiles binary search.');

    $languageService->addOrUpdateValue($langId, 'base',  'questions_question_presentation_fselect_label',
        'Single Choice - Regular (Slower results, unlimited)');
    $languageService->addOrUpdateValue($langId, 'admin', 'questions_infinite_possible_values_description',
        'Add unlimited number of values. Field values are stored regularly, which might affects the speed of profiles search.');
    $languageService->addOrUpdateValue($langId, 'admin', 'questions_infinite_possible_values_label',
        'Possible values');
    $languageService->addOrUpdateValue($langId, 'admin', 'questions_values_should_not_be_empty',
        'The value should not be empty');

}


$queryList = array(
    "ALTER TABLE `{$tblPrefix}base_question` CHANGE `type` `type` ENUM('text','select','datetime','boolean','multiselect','fselect')",
    "ALTER TABLE `{$tblPrefix}base_question` CHANGE `presentation` `presentation` ENUM('text','textarea','select','date','location','checkbox','multicheckbox','radio','url','password','age','birthdate','range','fselect')"
);

foreach ( $queryList as $query )
{
    try
    {
        $dbo->query($query);
    }
    catch (Exception $e)
    {
        $logger->addEntry(json_encode($e));
    }
}
