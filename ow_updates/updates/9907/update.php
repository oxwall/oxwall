<?php

$db = Updater::getDbo();
$logger = Updater::getLogger();
$storage = Updater::getStorage();
$tblPrefix = OW_DB_PREFIX;

$queryList = array();

$queryList[] = "ALTER TABLE `{$tblPrefix}base_theme_image`
  ADD `dimensions` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
  ADD `filesize` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL;";

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

$themeService = BOL_ThemeService::getInstance();
$images = $themeService->findAllCssImages();

foreach ( $images as $image )
{
	try
	{
		$path = $themeService->getUserfileImagesDir() . $image->getFilename();
		if ($storage->fileExists($path))
        {
            if (get_class($storage) == 'UPDATE_AmazonCloudStorage')
            {
                $tempPath = tempnam($themeService->getUserfileImagesDir(), 'themeTmpImage');
                $info = $storage->copyFileToLocalFS($path, $tempPath);
                $dimensions = getimagesize($tempPath);
                $filesize = UTIL_File::getFileSize($tempPath);
                unlink($tempPath);
            }
            else
            {
                $dimensions = getimagesize($path);
                $filesize = UTIL_File::getFileSize($path);
            }
            $image->dimensions = "{$dimensions[0]}x{$dimensions[1]}";
            $image->filesize = $filesize;
            BOL_ThemeImageDao::getInstance()->save($image);
        }
	}
	catch ( Exception $e )
	{
        $logger->addEntry(json_encode($e));
	}
}
