<?php

if ( !file_exists(OW_DIR_USERFILES . 'plugins' . DS . 'base' . DS . 'favicon.ico') )
{
    @copy(OW_DIR_STATIC . 'favicon.ico', OW_DIR_USERFILES . 'plugins' . DS . 'base' . DS . 'favicon.ico');
}


UPDATE_LanguageService::getInstance()->importPrefixFromZip(dirname(__FILE__) . DS . 'langs.zip', 'base');
