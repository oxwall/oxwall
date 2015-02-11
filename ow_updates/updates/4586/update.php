<?php

$tblPrefix = OW_DB_PREFIX;
$db = Updater::getDbo();

if ( !isset($logArray) )
{
    $errors = array();
}

try
{
    $preference = BOL_PreferenceService::getInstance()->findPreference('send_wellcome_letter');

    if ( empty($preference) )
    {
        $preference = new BOL_Preference();
    }

    $preference->key = 'send_wellcome_letter';
    $preference->sectionName = 'general';
    $preference->defaultValue = 0;
    $preference->sortOrder = 99;

    BOL_PreferenceService::getInstance()->savePreference($preference);
}
catch ( Exception $e )
{
    if ( isset($logArray) )
    {
        $logArray[] = $e;
    }
    else
    {
        $errors[] = $e;
    }
}

$queryList = array(
    "ALTER IGNORE TABLE  `{$tblPrefix}base_document` ADD UNIQUE  `uriIndex` (  `uri` )",
    "ALTER TABLE `{$tblPrefix}base_mail` ADD `senderSuffix` INT NOT NULL ",
    "ALTER TABLE `" . OW_DB_PREFIX . "base_billing_gateway` ADD `dynamic` TINYINT( 1 ) NULL DEFAULT '1' AFTER `recurring`;",
    "ALTER TABLE `" . OW_DB_PREFIX . "base_billing_gateway_product` ADD `pluginKey` VARCHAR( 255 ) NOT NULL AFTER `gatewayId`;",
    "ALTER TABLE `" . OW_DB_PREFIX . "base_billing_gateway_product` CHANGE `productId` `productId` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;",
    "CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "base_user_block` (
      `id` int(11) NOT NULL auto_increment,
      `userId` int(11) NOT NULL,
      `blockedUserId` int(11) NOT NULL,
      PRIMARY KEY  (`id`),
      UNIQUE KEY `userId_blockedUserId` (`userId`,`blockedUserId`),
      KEY `userId` (`userId`),
      KEY `blockedUserId` (`blockedUserId`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;",
    "ALTER TABLE  `" . OW_DB_PREFIX . "base_search_result` ENGINE = MYISAM",
	"ALTER TABLE  `" . OW_DB_PREFIX . "base_preference_section` ENGINE = MYISAM",
	"ALTER TABLE  `" . OW_DB_PREFIX . "base_preference_data` ENGINE = MYISAM",
	"ALTER TABLE  `" . OW_DB_PREFIX . "base_log` ENGINE = MYISAM",
	"ALTER TABLE  `" . OW_DB_PREFIX . "base_search` ENGINE = MYISAM",
    // SARDAR REMOVE QUERY BELOW FOR SERVICE UPDATES
    "INSERT INTO `{$tblPrefix}base_menu_item` (`prefix`, `key`, `documentKey`, `type`, `order`, `routePath`, `externalUrl`, `newWindow`, `visibleFor`) VALUES ('admin', 'sidebar_menu_themes_add', '', 'admin_appearance', '3', 'admin_themes_add_new', NULL, '0', '3');"
);

foreach ( $queryList as $query )
{
    try
    {
        $db->query($query);
    }
    catch ( Exception $e )
    {
        if ( isset($logArray) )
        {
            $logArray[] = $e;
        }
        else
        {
            $errors[] = $e;
        }
    }
}

UPDATE_LanguageService::getInstance()->importPrefixFromZip(dirname(__FILE__) . DS . 'langs.zip', 'base');

// add new configs
if ( !UPDATE_ConfigService::getInstance()->configExists('base', 'cachedEntitiesPostfix') )
{
    UPDATE_ConfigService::getInstance()->addConfig('base', 'cachedEntitiesPostfix', '123');
}

// favicon fix
if ( !file_exists(OW_DIR_USERFILES . 'plugins' . DS . 'base' . DS . 'favicon.ico') )
{
    @copy(OW_DIR_STATIC . 'favicon.ico', OW_DIR_USERFILES . 'plugins' . DS . 'base' . DS . 'favicon.ico');
}

UPDATE_ConfigService::getInstance()->saveConfig('base', 'favicon', file_exists(OW_DIR_USERFILES . 'plugins' . DS . 'base' . DS . 'favicon.ico'));

// clean and rename custom css files
$query = "SELECT * FROM `{$tblPrefix}base_theme`";
$themes = $db->queryForList($query);
foreach ( $themes as $theme )
{
    if ( empty($theme['customCssFileName']) )
    {
        continue;
    }

    $filePath = OW_DIR_USERFILES . 'themes' . DS . $theme['customCssFileName'];

    if( !file_exists($filePath) )
    {
        $db->query("UPDATE `{$tblPrefix}base_theme` SET  `customCssFileName` = NULL WHERE `id` = :id", array('id' => $theme['id']));
    }
}

