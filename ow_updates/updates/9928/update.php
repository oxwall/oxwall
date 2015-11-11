<?php

$preference = BOL_PreferenceService::getInstance()->findPreference('timeZoneSelect');

if ( empty($preference) )
{
    $preference = new BOL_Preference();
}

$preference->key = 'timeZoneSelect';
$preference->sectionName = 'general';
$preference->defaultValue = OW::getConfig()->getValue('base', 'site_timezone');
$preference->sortOrder = 1;

BOL_PreferenceService::getInstance()->savePreference($preference);