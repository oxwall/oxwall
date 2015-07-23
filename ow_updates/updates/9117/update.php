<?php

$languageService = Updater::getLanguageService();

$db = Updater::getDbo();
$logger = Updater::getLogger();
$tblPrefix = OW_DB_PREFIX;

$queryList = array();

$queryList[] = "CREATE TABLE IF NOT EXISTS `{$tblPrefix}file_temporary` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL,
  `userId` int(11) NOT NULL,
  `addDatetime` int(11) NOT NULL,
  `order` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";

$queryList[] = "CREATE TABLE IF NOT EXISTS `{$tblPrefix}base_file` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `addDatetime` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `order` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";

$queryList[] = "ALTER TABLE `{$tblPrefix}base_theme_image`
  ADD `addDatetime` INT NULL ,
  ADD `description` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL ;";

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


$languages = $languageService->getLanguages();
$langId = null;
foreach ($languages as $lang)
{
    if ($lang->tag == 'en')
    {
        $langId = $lang->id;
        break;
    }
}

if ( !is_null($langId) )
{
    $languageService->addOrUpdateValue($langId, 'admin', 'all_files', 'All files');
    $languageService->addOrUpdateValue($langId, 'admin', 'copy_url', 'Copy Url');
    $languageService->addOrUpdateValue($langId, 'admin', 'delete_image', 'Delete');
    $languageService->addOrUpdateValue($langId, 'admin', 'undefined_action', 'Undefined action');
    $languageService->addOrUpdateValue($langId, 'admin', 'not_enough_params', 'Not enough params');
}

$keys = array(
    'tb_edit_photo', 'confirm_delete', 'mark_featured', 'remove_from_featured', 'rating_total', 'rating_your', 'of',
    'album', 'slideshow_interval', 'pending_approval', 'not_all_photos_uploaded', 'size_limit', 'type_error',
    'dnd_support', 'dnd_not_support', 'drop_here', 'please_wait', 'describe_photo', 'photo_upload_error',
    'error_ini_size', 'error_form_size', 'error_partial', 'error_no_file', 'error_no_tmp_dir', 'error_cant_write',
    'error_extension', 'no_photo_uploaded', 'photos_uploaded'
);

foreach ($keys as $key)
{
    $photoKey = $languageService->findKey('photo', $key);
    if ( is_null($photoKey) )
    {
        continue;
    }
    $photoValue = $languageService->findValue($langId, $photoKey->id);
    $languageService->addOrUpdateValue($langId, 'admin', $key, $photoValue->value);
}

