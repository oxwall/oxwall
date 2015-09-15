<?php

require_once __DIR__ . DS . "install" . DS . "configs.php";
require_once __DIR__ . DS . "install" . DS . "authorization.php";
require_once __DIR__ . DS . "install" . DS . "navigation.php";
require_once __DIR__ . DS . "install" . DS . "widgets.php";

// Langs
OW::getLanguage()->importPluginLangs(dirname(__FILE__) . DS . "langs.zip", "admin");