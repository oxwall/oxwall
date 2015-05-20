<?php

$tblPrefix = OW_DB_PREFIX;
$db = Updater::getDbo();
$logger = Updater::getLogger();

$queryList = array(
    "ALTER TABLE  `{$tblPrefix}base_attachment` ADD  `size` INT NOT NULL DEFAULT  '0', ADD  `bundle` VARCHAR( 128 ) NULL DEFAULT NULL",
    "ALTER TABLE  `{$tblPrefix}base_attachment` ADD  `origFileName` VARCHAR( 100 ) NOT NULL AFTER  `fileName`",
    "ALTER TABLE  `{$tblPrefix}base_attachment` ADD INDEX (  `userId` )",
    "ALTER TABLE  `{$tblPrefix}base_attachment` ADD INDEX (  `bundle` )",
    "ALTER TABLE  `{$tblPrefix}base_user_online` ADD  `context` TINYINT UNSIGNED NOT NULL",
    "ALTER TABLE  `{$tblPrefix}base_attachment`  ADD `pluginKey` VARCHAR(100) NOT NULL,  ADD INDEX (`pluginKey`) ",
    "UPDATE `{$tblPrefix}base_user_online` SET `context` = 1 ",
    "CREATE TABLE IF NOT EXISTS `{$tblPrefix}base_user_auth_token` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `userId` int(11) NOT NULL,
      `token` varchar(50) NOT NULL,
      `timeStamp` int(11) NOT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `userId` (`userId`,`token`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1",
    "CREATE TABLE  `{$tblPrefix}base_question_to_account_type` (
        `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
        `accountType` VARCHAR( 32 ) NOT NULL ,
        `questionName` VARCHAR( 255 ) NOT NULL,
        UNIQUE KEY `uniq` (`questionName`,`accountType`)
        ) ENGINE = MYISAM",
    "INSERT INTO `{$tblPrefix}base_question_to_account_type` ( `questionName`, `accountType` ) SELECT q.name, a.name
        FROM  `{$tblPrefix}base_question` q, `{$tblPrefix}base_question_account_type` a
        WHERE q.`accountTypeName` IS NULL
        OR q.`accountTypeName` = a.name ",
    " ALTER TABLE `{$tblPrefix}base_question_account_type` ADD `roleId` INT NOT NULL DEFAULT '0' ",
    "ALTER TABLE `{$tblPrefix}base_question_section` ADD `isHidden` INT NOT NULL DEFAULT '0'",
    "ALTER TABLE `{$tblPrefix}base_user_reset_password` ADD  `updateTimeStamp` INT NOT NULL",
    "ALTER TABLE `{$tblPrefix}base_question_value` CHANGE `questionName` `questionName` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ",
    "UPDATE `{$tblPrefix}base_question` SET type='multiselect' WHERE type='select' AND presentation='multicheckbox'  "
);

if ( !defined('OW_PLUGIN_XP') )
{
    $queryList[] = "DELETE FROM  `{$tblPrefix}base_config` WHERE  `key` =  'chuppochat'";
}

foreach ( $queryList as $query )
{
    try
    {
        $db->query($query);
    }
    catch ( Exception $e )
    {
        $logger->addEntry(json_encode($e));
    }
}

if ( !Updater::getConfigService()->configExists('base', 'admin_cookie') )
{
    Updater::getConfigService()->addConfig('base', 'admin_cookie', '');
}

if ( !Updater::getConfigService()->configExists('base', 'disable_mobile_context') )
{
    Updater::getConfigService()->addConfig('base', 'disable_mobile_context', 0);
}

if ( !Updater::getConfigService()->configExists('base', 'log_file_max_size_mb') )
{
    Updater::getConfigService()->addConfig('base', 'log_file_max_size_mb', 20);
}

if ( !Updater::getConfigService()->configExists('base', 'attch_file_max_size_mb') )
{
    Updater::getConfigService()->addConfig('base', 'attch_file_max_size_mb', 2);
}

if ( !UPDATE_ConfigService::getInstance()->configExists('base', 'users_on_page') )
{
    UPDATE_ConfigService::getInstance()->addConfig('base', 'users_on_page', 12);
}

if ( !Updater::getConfigService()->configExists('base', 'attch_ext_list') )
{
    $ext = array(
        'txt', 'doc', 'docx', 'sql', 'csv', 'xls', 'ppt', 'pdf',
        'jpg', 'jpeg', 'png', 'gif', 'bmp', 'psd', 'ai',
        'avi', 'wmv', 'mp3', '3gp', 'flv', 'mkv', 'mpeg', 'mpg', 'swf',
        'zip', 'gz', 'tgz', 'gzip', '7z', 'bzip2', 'rar'
    );

    Updater::getConfigService()->addConfig('base', 'attch_ext_list', json_encode($ext));
}

$preference = BOL_PreferenceService::getInstance()->findPreference('profile_details_update_stamp');

if ( empty($preference) )
{
    $preference = new BOL_Preference();
}

$preference->key = 'profile_details_update_stamp';
$preference->sectionName = 'general';
$preference->defaultValue = 0;
$preference->sortOrder = 1;

BOL_PreferenceService::getInstance()->savePreference($preference);

try
{
    Updater::getNavigationService()->addMenuItem(
        OW_Navigation::ADMIN_MOBILE,
        'mobile.admin_settings',
        'mobile',
        'mobile_admin_settings',
        OW_Navigation::VISIBLE_FOR_MEMBER);
}
catch ( Exception $e )
{
    $logger->addEntry(json_encode($e));
}

try
{
    Updater::getLanguageService()->deleteLangKey('base', 'avatar_back_profile_edit');
    Updater::getLanguageService()->deleteLangKey('admin', 'questions_delete_account_type_confirmation');
    Updater::getLanguageService()->deleteLangKey('admin', 'questions_admin_add_new_values');
    Updater::getLanguageService()->deleteLangKey('base', 'questions_admin_add_new_values');
    Updater::getLanguageService()->deleteLangKey('base', 'questions_possible_values_label');
    Updater::getLanguageService()->deleteLangKey('base', 'question_possible_values_label');
    Updater::getLanguageService()->deleteLangKey('admin', 'questions_possible_values_label');
    Updater::getLanguageService()->deleteLangKey('admin', 'question_possible_values_label');
    Updater::getLanguageService()->deleteLangKey('admin', 'questions_matched_question_values');
    Updater::getLanguageService()->deleteLangKey('base', 'local_page_meta_tags_page-119658');
    Updater::getLanguageService()->deleteLangKey('base', 'form_validate_common_error_message');
    Updater::getLanguageService()->addValue('base', 'local_page_meta_tags_page-119658', ' ');
    Updater::getLanguageService()->addValue('base', 'local_page_meta_tags_page_81959573', ' ');
}
catch ( Exception $e )
{
    $logger->addEntry(json_encode($e));
}

if ( defined('SOFT_PACK') )
{
    
    try
    {
        Updater::getLanguageService()->deleteLangKey('base', 'welcome_letter_template_html');
        Updater::getLanguageService()->addValue('base', 'welcome_letter_template_html', 'Welcome to <a href="{$site_url}">{$site_name}</a>! Thanks for joining us. Here are some quick links that you need to find your way around:<br/><br />

- <a href="{$site_url}">Main page</a> - welcome!<br/>
- <a href="{$site_url}profile/edit">Change profile details</a> - again, people will only engage if they get some impression of you. Invest time in your profile;<br/>
- <a href="{$site_url}users/search">Look who\'s in</a><br/><br />

We are eager to send many dates your way!<br/><br />

<a href="{$site_url}">{$site_name}</a> administration<br/>');
    }
    catch ( Exception $e )
    {
        $logger->addEntry(json_encode($e));
    }
}

try
{
    Updater::getLanguageService()->addValue('base', 'no_items', 'No items');
}
catch ( Exception $e )
{
    $logger->addEntry(json_encode($e));
}

UPDATE_LanguageService::getInstance()->importPrefixFromZip(dirname(__FILE__) . DS . 'langs.zip', 'base');
