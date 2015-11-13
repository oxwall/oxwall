<?php

$preference = BOL_PreferenceService::getInstance()->findPreference('timeZoneSelect');

if ( empty($preference) )
{
    $preference = new BOL_Preference();
}

$preference->key = 'timeZoneSelect';
$preference->sectionName = 'general';
$preference->defaultValue = json_encode(null);
$preference->sortOrder = 1;

BOL_PreferenceService::getInstance()->savePreference($preference);