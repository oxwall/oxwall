<?php

$tblPrefix = OW_DB_PREFIX;

$db = Updater::getDbo();

$queryList = array(
    "INSERT INTO  `{$tblPrefix}base_config` ( `key`, `name`, `value`, `description` ) VALUES ( 'base',  'users_count_on_page',  '30', 'Users count on page' )",
    "INSERT INTO  `{$tblPrefix}base_config` ( `key`, `name`, `value`, `description` ) VALUES ( 'base',  'cron_is_active',  '0', 'Flag showing if cron script is activated after soft install' )",
    "UPDATE  `{$tblPrefix}base_menu_item` SET  `key` = 'sidebar_menu_item_permission_role' WHERE  `key` = 'sidebar_menu_item_permission_roles'",
    "INSERT INTO  `{$tblPrefix}base_menu_item` ( `prefix`, `key`,`documentKey`, `type`, `order`, `routePath`, `externalUrl`, `newWindow`, `visibleFor` )
        VALUES ( 'admin',  'sidebar_menu_item_users_roles',  '',  'admin_users',  '3',  'admin_user_roles', NULL ,  '0',  '3' )",
    "INSERT IGNORE INTO `{$tblPrefix}base_question_config` (`id`, `questionPresentation`, `name`, `description`, `presentationClass`) VALUES
    (1, 'date', 'year_range', '', 'YearRange'),
    (2, 'age', 'year_range', '', 'YearRange'),
    (3, 'birthdate', 'year_range', '', 'YearRange')",
    "ALTER TABLE  `{$tblPrefix}base_authorization_role` ADD `displayLabel` TINYINT( 1 ) NULL DEFAULT '0'",
    "ALTER TABLE  `{$tblPrefix}base_authorization_role` ADD `custom` VARCHAR( 255 ) NULL DEFAULT NULL"
);

$sqlErrors = array();

foreach ( $queryList as $query )
{
    try
    {
        $db->query($query);
    }
    catch ( Exception $e )
    {
        $sqlErrors[] = $e;
    }
}

try
{
    OW::getAuthorization()->addAction('base', 'view_profile', true);
}
catch( Exception $e )
{
    $sqlErrors[] = $e;
}

UPDATE_LanguageService::getInstance()->importPrefixFromZip(dirname(__FILE__) . DS . 'langs.zip', 'base');

if ( !empty($sqlErrors) )
{
    //printVar($sqlErrors);
}

