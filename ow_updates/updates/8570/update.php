<?php
UPDATE_LanguageService::getInstance()->deleteLangKey('base', 'welcome_widget_content');
UPDATE_LanguageService::getInstance()->importPrefixFromZip(dirname(__FILE__) . DS . 'langs.zip', 'base');
