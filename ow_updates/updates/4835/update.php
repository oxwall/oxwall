<?php

$tblPrefix = OW_DB_PREFIX;
$db = Updater::getDbo();

$queryList = array(
   " ALTER TABLE `".OW_DB_PREFIX."base_user` ADD `joinIp` INT( 11 ) NOT NULL "
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

/* code to move all custom css graphics to clouds */

if ( defined('OW_USE_AMAZON_S3_CLOUDFILES') && OW_USE_AMAZON_S3_CLOUDFILES || defined('OW_USE_CLOUDFILES') && OW_USE_CLOUDFILES )
{
    $storage = Updater::getStorage() ;
    $cssImages = BOL_ThemeService::getInstance()->findAllCssImages();

    /* @var $image BOL_ThemeImage */
    foreach ( $cssImages as $image )
    {
        $path = OW_DIR_THEME_USERFILES . $image->getFilename();
        $storage->copyFile($path, $path);
    }

    $themesList = BOL_ThemeService::getInstance()->findAllThemes();

    
    $newDirUrl = $storage->getFileUrl(OW_DIR_THEME_USERFILES);

    if( mb_substr($newDirUrl, -1) !== '/' )
    {
        $newDirUrl .= '/';
    }

    /* @var $theme BOL_Theme */
    foreach ( $themesList as $theme )
    {
        if ( $theme->getCustomCss() && mb_strstr($theme->getCustomCss(), OW_URL_USERFILES) )
        {
            $theme->setCustomCss(str_replace(OW_URL_THEME_USERFILES, $newDirUrl, $theme->getCustomCss()));
            BOL_ThemeService::getInstance()->saveTheme($theme);
        }
    }
}

/* end of code to move all custom css graphics to clouds */

//---------- ONLY FOR SERVICE --------
/* try
{

    $sql = " SELECT m.* FROM `".OW_DB_PREFIX."base_media_panel_file` m
        LEFT JOIN `".OW_DB_PREFIX."base_user` u ON ( m.userId = u.id )
        WHERE u.id IS NULL ";

    $fileList = $db->queryForObjectList($sql, 'BOL_MediaPanelFile');

    foreach( $fileList as $image )
    {
        $data = $image->getData();

        try
        {
            $storage = Updater::getStorage();
            $storage->removeFile(OW::getPluginManager()->getPlugin('base')->getUserFilesDir() . $image->id . '-' . $data->name);
            $db->query("DELETE FROM `".OW_DB_PREFIX."base_media_panel_file` WHERE id = :id", array('id' => $image->id) );
        }
        catch ( Exception $e )
        {
            print_r($e->getMessage()." file: ".$e->getFile(). " line: ". $e->getLine() . " \n " .$e->getTraceAsString()."\n"."\n");
        }
    }
}
catch ( Exception $e )
{
    print_r($e->getMessage()." file: ".$e->getFile(). " line: ". $e->getLine() . " \n " .$e->getTraceAsString()."\n"."\n");
    //Updater::getLogger()->addEntry($e->getMessage()." file: ".$e->getFile(). " line: ". $e->getLine() . " \n " .$e->getTraceAsString() );
} */
//-------------------------------------
