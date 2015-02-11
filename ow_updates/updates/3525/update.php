<?php

$tblPrefix = OW_DB_PREFIX;

$db = Updater::getDbo();

$queryList = array(
    "ALTER TABLE  `{$tblPrefix}base_question_data` ADD INDEX `intValue` (  `intValue` )",
    "INSERT INTO  `{$tblPrefix}base_menu_item` (`prefix`,`key`,`documentKey`,`type`,`order`,`routePath`,`externalUrl`,`newWindow`,`visibleFor`)
        VALUES ('admin','sidebar_menu_item_user_settings','','admin_settings','5','admin_settings_user',NULL,'0','3')",
    // add setting items order for every element
    "INSERT INTO  `{$tblPrefix}base_config` (`key`, `name`, `value`, `description`)
        VALUES ('base', 'html_head_code', '', 'Code (meta, css, js) added from admin panel into head section of HTML document.')",
    "INSERT INTO  `{$tblPrefix}base_config` (`key`, `name`, `value`, `description`)
        VALUES ('base',  'html_prebody_code',  '',  'Code (js) added before ''body'' closing tag.')",
    "ALTER TABLE  `{$tblPrefix}base_language` ADD  `rtl` BOOLEAN NOT NULL DEFAULT  '0'",
    "ALTER TABLE  `{$tblPrefix}base_user_disapprove` ADD INDEX `userId` (  `userId` )",
    "ALTER TABLE  `{$tblPrefix}base_user` ADD INDEX `joinStamp` ( `joinStamp` )",
    "ALTER TABLE  `{$tblPrefix}base_entity_tag` DROP INDEX  `id`",
    "ALTER TABLE  `{$tblPrefix}base_entity_tag` DROP  `active`",
    "ALTER TABLE  `{$tblPrefix}base_entity_tag` ADD  `active` TINYINT NOT NULL DEFAULT  '1'",
    "ALTER TABLE  `{$tblPrefix}base_vote` DROP  `active`",
    "ALTER TABLE  `{$tblPrefix}base_vote` ADD  `active` TINYINT NOT NULL DEFAULT  '1'",
    "ALTER TABLE  `{$tblPrefix}base_rate` DROP INDEX  `id_2`",
    "ALTER TABLE  `{$tblPrefix}base_rate` DROP INDEX  `id`",
    "ALTER TABLE  `{$tblPrefix}base_rate` ADD INDEX  `entityType` (  `entityType` )",
    "ALTER TABLE  `{$tblPrefix}base_rate` ADD INDEX  `entityId` (  `entityId` )",
    "ALTER TABLE  `{$tblPrefix}base_rate` ADD INDEX  `userId` (  `userId` )",
    "ALTER TABLE  `{$tblPrefix}base_rate` DROP  `active`",
    "ALTER TABLE  `{$tblPrefix}base_rate` ADD  `active` TINYINT NOT NULL DEFAULT  '1'",
    "ALTER TABLE  `{$tblPrefix}base_comment_entity` DROP  `active`",
    "ALTER TABLE  `{$tblPrefix}base_comment_entity` ADD  `active` TINYINT NOT NULL DEFAULT  '1'",
    "UPDATE `{$tblPrefix}base_menu_item` SET `order` = 1 WHERE `key` = 'sidebar_menu_item_main_settings'",
    "UPDATE `{$tblPrefix}base_menu_item` SET `order` = 2 WHERE `key` = 'sidebar_menu_item_user_settings'",
    "UPDATE `{$tblPrefix}base_menu_item` SET `order` = 3 WHERE `key` = 'sidebar_menu_item_settings_language'",
    "INSERT INTO `{$tblPrefix}base_config` (`key`, `name`, `value`, `description`) VALUES ('base', 'tf_user_custom_html_disable', '0', NULL)",
    "INSERT INTO `{$tblPrefix}base_config` (`key`, `name`, `value`, `description`) VALUES ('base', 'tf_user_rich_media_disable', '0', NULL)",
    "INSERT INTO `{$tblPrefix}base_config` (`key`, `name`, `value`, `description`) VALUES ('base', 'tf_comments_rich_media_disable', '0', NULL)",
    "INSERT INTO `{$tblPrefix}base_config` (`key`, `name`, `value`, `description`) VALUES ('base', 'tf_resource_list', '[\"clipfish.de\",\"youtube.com\",\"google.com\",\"metacafe.com\",\"myspace.com\"]', NULL)",
    "INSERT INTO `{$tblPrefix}base_config` (`key`, `name`, `value`, `description`) VALUES ('base', 'favicon', '0', NULL)",
    "CREATE TABLE IF NOT EXISTS `{$tblPrefix}base_preference` (
  `id` int(11) NOT NULL auto_increment,
  `key` varchar(100) NOT NULL,
  `defaultValue` text NOT NULL,
  `sectionName` varchar(100) NOT NULL,
  `sortOrder` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `key` (`key`),
  KEY `sortOrder` (`sortOrder`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1",
    "INSERT INTO `{$tblPrefix}base_preference` (`key`, `defaultValue`, `sectionName`, `sortOrder`) VALUES
('mass_mailing_subscribe', 'true', 'general', 1)",
    "CREATE TABLE IF NOT EXISTS `{$tblPrefix}base_preference_data` (
  `id` int(11) NOT NULL auto_increment,
  `key` varchar(100) NOT NULL,
  `userId` int(11) NOT NULL,
  `value` text,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `userId` (`userId`,`key`),
  KEY `key` (`key`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1",
    "CREATE TABLE IF NOT EXISTS `{$tblPrefix}base_preference_section` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL,
  `sortOrder` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1",
    "INSERT INTO `{$tblPrefix}base_preference_section` (`name`, `sortOrder`) VALUES
('general', 1)",
    "INSERT INTO `{$tblPrefix}base_config` (`key`, `name`, `value`, `description`) VALUES
('base', 'join_display_photo_upload', 'not_display', 'Display ''Photo Upload'' field on Join page.'),
('base', 'join_display_terms_of_use', '1', 'Display ''Terms of use'' field on Join page.')",
"UPDATE  `{$tblPrefix}base_menu_item` SET  `type` =  'hidden' WHERE  `type` =  'local'"
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

UPDATE_LanguageService::getInstance()->importPrefixFromZip(dirname(__FILE__) . DS . 'langs.zip', 'base');

if ( !empty($sqlErrors) )
{
    printVar($sqlErrors);
}

