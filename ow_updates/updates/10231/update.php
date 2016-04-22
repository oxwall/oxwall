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

$languageService = Updater::getLanguageService();

$languages = $languageService->getLanguages();
$langId = null;

foreach ( $languages as $lang )
{
    if ( $lang->tag == "en" )
    {
        $langId = $lang->id;
        break;
    }
}

if ( $langId !== null )
{
    $languageService->addOrUpdateValue($langId, "admin", "mail_template_admin_invalid_license_subject",
        "Unlicensed Plugin/Theme Notice");

    $value = 'It appears that following plugins/themes obtained through Oxwall Store and installed on your website ({$siteURL}) '
        . 'have failed license verification check: <br /><br /> {$itemList}<br /><br /> To continue using these plugins/themes, '
        . 'please make sure that all license keys for the listed plugins/themes are valid. Note that you may need to enter '
        . 'license keys manually in the Admin Panel: {$adminUrl} <br /><br /> You may find all licenses for purchased plugins/themes in '
        . 'your Oxwall Store account: http://www.oxwall.org/store/granted-list/plugin <br /><br /> You may also contact '
        . 'specific plugin/theme developers to obtain a new license key. <br /><br /> IMPORTANT: After three consecutive unsuccessful '
        . 'license verification checks the plugin/theme may be deactivated. <br /><br /> Please note that all commercial third party '
        . 'plugins/themes sold through Oxwall Store are covered by Oxwall Store Commercial License (http://www.oxwall.org/store/oscl), '
        . 'and require a valid license key to operate. <br />';

    $languageService->addOrUpdateValue($langId, "admin", "mail_template_admin_invalid_license_content_html", $value);

    $value = 'It appears that following plugins/themes obtained through Oxwall Store and installed on your website ({$siteURL}) '
        . 'have failed license verification check: {$itemList} To continue using these plugins/themes, please make sure that all '
        . 'license keys for the listed plugins/themes are valid. Note that you may need to enter license keys manually in the Admin Panel: '
        . '{$adminUrl} You may find all licenses for purchased plugins/themes in your Oxwall Store account: http://www.oxwall.org/store/granted-list/plugin '
        . 'You may also contact specific plugin/theme developers to obtain a new license key. IMPORTANT: After three consecutive unsuccessful '
        . 'license verification checks the plugin/theme may be deactivated. Please note that all commercial third party plugins/themes sold '
        . 'through Oxwall Store are covered by Oxwall Store Commercial License (http://www.oxwall.org/store/oscl), and require a valid license key to operate.';

    $languageService->addOrUpdateValue($langId, "admin", "mail_template_admin_invalid_license_content_text", $value);
}
