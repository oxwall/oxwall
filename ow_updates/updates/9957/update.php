<?php

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

}
