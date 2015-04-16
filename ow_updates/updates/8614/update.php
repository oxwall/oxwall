<?php

UPDATE_LanguageService::getInstance()->deleteLangKey("base", "suspend_floatbox_title");
UPDATE_LanguageService::getInstance()->importPrefixFromZip(dirname(__FILE__) . DS . 'langs.zip', 'base');
