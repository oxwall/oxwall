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
  ADD `title` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL;";

$queryList[] = "UPDATE `{$tblPrefix}base_theme_image`
  set `addDatetime` = UNIX_TIMESTAMP();";

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

if ( !is_null($langId) ) {
    $languageService->addOrUpdateValue($langId, 'admin', 'all_files', 'All files');
    $languageService->addOrUpdateValue($langId, 'admin', 'copy_url', 'Copy URL');
    $languageService->addOrUpdateValue($langId, 'admin', 'delete_image', 'Delete');
    $languageService->addOrUpdateValue($langId, 'admin', 'undefined_action', 'Undefined action');
    $languageService->addOrUpdateValue($langId, 'admin', 'not_enough_params', 'Not enough params');
    $languageService->addOrUpdateValue($langId, 'admin', 'no_photo_selected', 'No photo selected');
    $languageService->addOrUpdateValue($langId, 'admin', 'select_mode', 'Select mode');
    $languageService->addOrUpdateValue($langId, 'admin', 'delete_selected', 'Delete selected');
    $languageService->addOrUpdateValue($langId, 'admin', 'exit_select_mode', 'Exit select mode');
    $languageService->addOrUpdateValue($langId, 'admin', 'period', 'Period');
    $languageService->addOrUpdateValue($langId, 'admin', 'all_time', 'All time');
    $languageService->addOrUpdateValue($langId, 'admin', 'title', 'Title');
    $languageService->addOrUpdateValue($langId, 'admin', 'url', 'URL');
    $languageService->addOrUpdateValue($langId, 'admin', 'date', 'Date');
    $languageService->addOrUpdateValue($langId, 'admin', 'size', 'Size');
    $languageService->addOrUpdateValue($langId, 'admin', 'filesize', 'Filesize');
    $languageService->addOrUpdateValue($langId, 'admin', 'upload_date', 'Upload date');

    $languageService->addOrUpdateValue($langId, 'admin', 'confirm_delete_images', 'Are you sure you want to delete this photos?');
    $languageService->addOrUpdateValue($langId, 'admin', 'no_items', 'No items');
	$languageService->addOrUpdateValue($langId, 'admin', 'album', 'Album');
	$languageService->addOrUpdateValue($langId, 'admin', 'confirm_delete', 'Are you sure you want to delete this photo?');
	$languageService->addOrUpdateValue($langId, 'admin', 'describe_photo', 'Description text...');
	$languageService->addOrUpdateValue($langId, 'admin', 'dnd_not_support', 'Click to browse photos');
	$languageService->addOrUpdateValue($langId, 'admin', 'dnd_support', 'Drag & Drop photos here or click to browse');
	$languageService->addOrUpdateValue($langId, 'admin', 'drop_here', 'Drop photos to start upload');
	$languageService->addOrUpdateValue($langId, 'admin', 'error_cant_write', 'Failed to write file to disk');
	$languageService->addOrUpdateValue($langId, 'admin', 'error_extension', 'A PHP extension stopped the file upload. PHP does not provide a way to ascertain which extension caused the file upload to stop; examining the list of loaded extensions with phpinfo() may help');
	$languageService->addOrUpdateValue($langId, 'admin', 'error_form_size', 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form');
	$languageService->addOrUpdateValue($langId, 'admin', 'error_ini_size', 'The uploaded file exceeds the upload_max_filesize directive in php.ini');
	$languageService->addOrUpdateValue($langId, 'admin', 'error_no_file', 'No file was uploaded');
	$languageService->addOrUpdateValue($langId, 'admin', 'error_no_tmp_dir', 'Missing a temporary folder');
	$languageService->addOrUpdateValue($langId, 'admin', 'error_partial', 'The uploaded file was only partially uploaded');
	$languageService->addOrUpdateValue($langId, 'admin', 'mark_featured', 'Mark as Featured');
	$languageService->addOrUpdateValue($langId, 'admin', 'not_all_photos_uploaded', 'Some photos were not uploaded because of excessive size or wrong format');
	$languageService->addOrUpdateValue($langId, 'admin', 'no_photo_uploaded', 'No photos were uploaded because of excessive size or wrong format');
	$languageService->addOrUpdateValue($langId, 'admin', 'of', 'of');
	$languageService->addOrUpdateValue($langId, 'admin', 'pending_approval', 'Pending Approval . . .');
	$languageService->addOrUpdateValue($langId, 'admin', 'photos_uploaded', '{$count} photos uploaded');
	$languageService->addOrUpdateValue($langId, 'admin', 'photo_upload_error', 'Photo upload error');
	$languageService->addOrUpdateValue($langId, 'admin', 'please_wait', 'Please wait while previously photo is being uploaded');
	$languageService->addOrUpdateValue($langId, 'admin', 'rating_total', '(Total {$count})');
	$languageService->addOrUpdateValue($langId, 'admin', 'rating_your', '(Total {$count}. Your is {$score})');
	$languageService->addOrUpdateValue($langId, 'admin', 'remove_from_featured', 'Remove from Featured');
	$languageService->addOrUpdateValue($langId, 'admin', 'size_limit', 'Photo "{$name}" file size cannot be greater than <b>{$size}</b> Mb');
	$languageService->addOrUpdateValue($langId, 'admin', 'slideshow_interval', 'Slideshow time:&nbsp;');
	$languageService->addOrUpdateValue($langId, 'admin', 'tb_edit_photo', 'Edit photo');
	$languageService->addOrUpdateValue($langId, 'admin', 'type_error', 'Invalid file type. {$name}');

}