<?php

/**
 * EXHIBIT A. Common Public Attribution License Version 1.0
 * The contents of this file are subject to the Common Public Attribution License Version 1.0 (the “License”);
 * you may not use this file except in compliance with the License. You may obtain a copy of the License at
 * http://www.oxwall.org/license. The License is based on the Mozilla Public License Version 1.1
 * but Sections 14 and 15 have been added to cover use of software over a computer network and provide for
 * limited attribution for the Original Developer. In addition, Exhibit A has been modified to be consistent
 * with Exhibit B. Software distributed under the License is distributed on an “AS IS” basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for the specific language
 * governing rights and limitations under the License. The Original Code is Oxwall software.
 * The Initial Developer of the Original Code is Oxwall Foundation (http://www.oxwall.org/foundation).
 * All portions of the code written by Oxwall Foundation are Copyright (c) 2011. All Rights Reserved.

 * EXHIBIT B. Attribution Information
 * Attribution Copyright Notice: Copyright 2011 Oxwall Foundation. All rights reserved.
 * Attribution Phrase (not exceeding 10 words): Powered by Oxwall community software
 * Attribution URL: http://www.oxwall.org/
 * Graphic Image as provided in the Covered Code.
 * Display of Attribution Information is required in Larger Works which are defined in the CPAL as a work
 * which combines Covered Code or portions thereof with code not governed by the terms of the CPAL.
 */

/**
 * Avatar service class
 *
 * @author Egor Bulgakov <egor.bulgakov@gmail.com>
 * @package ow.ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_AvatarService
{
    /**
     * @var BOL_AvatarDao
     */
    private $avatarDao;


    const AVATAR_PREFIX = 'avatar_';

    const AVATAR_BIG_PREFIX = 'avatar_big_';

    const AVATAR_ORIGINAL_PREFIX = 'avatar_original_';

    const AVATAR_CHANGE_GALLERY_LIMIT = 12;

    const AVATAR_CHANGE_SESSION_KEY = 'base.avatar_change_key';

    /**
     * @var BOL_AvatarService
     */
    private static $classInstance;

    /**
     * Class constructor
     */
    private function __construct()
    {
        $this->avatarDao = BOL_AvatarDao::getInstance();
    }

    /**
     * Singleton instance.
     *
     * @return BOL_AvatarService
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * Find avatar object by userId
     *
     * @param int $userId
     * @return BOL_Avatar
     */
    public function findByUserId( $userId, $checkCache = true )
    {
        return $this->avatarDao->findByUserId($userId, $checkCache);
    }

    public function findAvatarByIdList( $idList )
    {
        return $this->avatarDao->findByIdList($idList);
    }

    /**
     * Find avatar object by userId list
     *
     * @param int $userId
     * @return BOL_Avatar
     */
    public function findByUserIdList( $userIdList )
    {
        return $this->avatarDao->getAvatarsList($userIdList);
    }

    public function findAvatarById( $id )
    {
        return $this->avatarDao->findById($id);
    }

    /**
     * Updates avatar object
     *
     * @param BOL_Avatar $avatar
     * @return int
     */
    public function updateAvatar( BOL_Avatar $avatar )
    {
        $this->clearCahche($avatar->userId);
        $this->avatarDao->save($avatar);

        return $avatar->id;
    }

    public function clearCahche( $userId )
    {
        $this->avatarDao->clearCahche($userId);
    }

    /**
     * Removes avatar image file
     *
     * @param string $path
     */
    public function removeAvatarImage( $path )
    {
        $storage = OW::getStorage();

        if ( $storage->fileExists($path) )
        {
            $storage->removeFile($path);
        }
    }

    /**
     * Removes user avatar
     *
     * @param int $userId
     * @return boolean
     */
    public function deleteUserAvatar( $userId )
    {
        if ( !$userId )
        {
            return false;
        }

        if ( !$this->userHasAvatar($userId) )
        {
            return true;
        }

        $avatar = $this->findByUserId($userId);

        $event = new OW_Event('base.before_user_avatar_delete', array('avatarId' => $avatar->id ));
        OW::getEventManager()->trigger($event);

        if ( $avatar )
        {
            return $this->deleteAvatar($avatar);
        }

        return false;
    }

    private function deleteAvatar( BOL_Avatar $avatar )
    {
        if ( empty($avatar) )
        {
            return false;
        }

        $this->avatarDao->deleteById($avatar->id);

        // avatar image
        $avatarPath = $this->getAvatarPath($avatar->userId, 1, $avatar->hash);
        $this->removeAvatarImage($avatarPath);

        // avatar big image
        $bigAvatarPath = $this->getAvatarPath($avatar->userId, 2, $avatar->hash);
        $this->removeAvatarImage($bigAvatarPath);

        // avatar original image
        $origAvatarPath = $this->getAvatarPath($avatar->userId, 3, $avatar->hash);
        $this->removeAvatarImage($origAvatarPath);

        return true;
    }

    public function deleteAvatarById( $id )
    {
        if ( !$id )
        {
            return false;
        }

        $avatar = $this->avatarDao->findById($id);

        if ( $avatar )
        {
            return $this->deleteAvatar($avatar);
        }

        return false;
    }

    /**
     * Crops user avatar using coordinates
     *
     * @param int $userId
     * @param $path
     * @param array $coords
     * @param int $viewSize
     * @return bool
     */
    public function cropAvatar( $userId, $path, $coords, $viewSize, array $editionalParams = array() )
    {
        $this->deleteUserAvatar($userId);

        $avatar = new BOL_Avatar();
        $avatar->userId = $userId;
        $avatar->hash = time();

        $this->updateAvatar($avatar);

        $params = array(
            'avatarId' => $avatar->id,
            'userId' => $userId,
            'trackAction' => isset($editionalParams['trackAction'])  ? $editionalParams['trackAction'] : true
        );

        $event = new OW_Event('base.after_avatar_update', array_merge($editionalParams, $params));
        OW::getEventManager()->trigger($event);

        // destination path
        $avatarPath = $this->getAvatarPath($userId, 1, $avatar->hash);
        $avatarBigPath = $this->getAvatarPath($userId, 2, $avatar->hash);
        $avatarOriginalPath = $this->getAvatarPath($userId, 3, $avatar->hash);

        // pluginfiles tmp path
        $avatarPFPath = $this->getAvatarPluginFilesPath($userId, 1, $avatar->hash);
        $avatarPFBigPath = $this->getAvatarPluginFilesPath($userId, 2, $avatar->hash);
        $avatarPFOriginalPath = $this->getAvatarPluginFilesPath($userId, 3, $avatar->hash);

        if ( !is_writable(dirname($avatarPFPath)) )
        {
            $this->deleteUserAvatar($userId);

            return false;
        }

        $storage = OW::getStorage();

        if ( !empty($editionalParams['isLocalFile']) )
        {
            $toFilePath = $path;
        }
        else
        {
            $toFilePath = OW::getPluginManager()->getPlugin('base')->getPluginFilesDir() . uniqid(md5( rand(0,9999999999) )).UTIL_File::getExtension($path);

            $storage->copyFileToLocalFS($path, $toFilePath);
        }

        $result = true;
        try
        {
            $image = new UTIL_Image($toFilePath);

            $width = $image->getWidth();
            $k = $width / $viewSize;

            $config = OW::getConfig();
            $avatarSize = (int) $config->getValue('base', 'avatar_size');
            $bigAvatarSize = (int) $config->getValue('base', 'avatar_big_size');

            $image->copyImage($avatarPFOriginalPath)
                ->cropImage($coords['x'] * $k, $coords['y'] * $k, $coords['w'] * $k, $coords['h'] * $k)
                ->resizeImage($bigAvatarSize, $bigAvatarSize, true)
                ->saveImage($avatarPFBigPath)
                ->resizeImage($avatarSize, $avatarSize, true)
                ->saveImage($avatarPFPath);

            $storage->copyFile($avatarPFOriginalPath, $avatarOriginalPath);
            $storage->copyFile($avatarPFBigPath, $avatarBigPath);
            $storage->copyFile($avatarPFPath, $avatarPath);
        }
        catch (Exception $ex)
        {
            $result = false;
        }

        @unlink($avatarPFPath);
        @unlink($avatarPFBigPath);
        @unlink($avatarPFOriginalPath);
        @unlink($toFilePath);

        return $result;
    }

    public function cropTempAvatar( $key, $coords, $viewSize )
    {
        $originalPath = $this->getTempAvatarPath($key, 3);
        $bigAvatarPath = $this->getTempAvatarPath($key, 2);
        $avatarPath = $this->getTempAvatarPath($key, 1);

        $image = new UTIL_Image($originalPath);

        $width = $image->getWidth();

        $k = $width / $viewSize;

        $config = OW::getConfig();
        $avatarSize = (int) $config->getValue('base', 'avatar_size');
        $bigAvatarSize = (int) $config->getValue('base', 'avatar_big_size');

        $image->cropImage($coords['x'] * $k, $coords['y'] * $k, $coords['w'] * $k, $coords['h'] * $k)
            ->resizeImage($bigAvatarSize, $bigAvatarSize, true)
            ->saveImage($bigAvatarPath)
            ->resizeImage($avatarSize, $avatarSize, true)
            ->saveImage($avatarPath);

        return true;
    }

    public function setUserAvatar( $userId, $uploadedFileName, array $editionalParams = array() )
    {
        $avatar = $this->findByUserId($userId);

        if ( !$avatar )
        {
            $avatar = new BOL_Avatar();
            $avatar->userId = $userId;
        }
        else
        {
            $oldHash = $avatar->hash;
        }

        $avatar->hash = time();

        // destination path
        $avatarPath = $this->getAvatarPath($userId, 1, $avatar->hash);
        $avatarBigPath = $this->getAvatarPath($userId, 2, $avatar->hash);
        $avatarOriginalPath = $this->getAvatarPath($userId, 3, $avatar->hash);

        // pluginfiles tmp path
        $avatarPFPath = $this->getAvatarPluginFilesPath($userId, 1, $avatar->hash);
        $avatarPFBigPath = $this->getAvatarPluginFilesPath($userId, 2, $avatar->hash);
        $avatarPFOriginalPath = $this->getAvatarPluginFilesPath($userId, 3, $avatar->hash);

        if ( !is_writable(dirname($avatarPFPath)) )
        {
            return false;
        }

        try
        {
            $image = new UTIL_Image($uploadedFileName);

            $config = OW::getConfig();

            $configAvatarSize = $config->getValue('base', 'avatar_size');
            $configBigAvatarSize = $config->getValue('base', 'avatar_big_size');

            $image->copyImage($avatarPFOriginalPath)
                ->resizeImage($configBigAvatarSize, $configBigAvatarSize, true)
                ->saveImage($avatarPFBigPath)
                ->resizeImage($configAvatarSize, $configAvatarSize, true)
                ->saveImage($avatarPFPath);

            $this->updateAvatar($avatar);

            $params = array(
                'avatarId' => $avatar->id,
                'userId' => $userId,
                'trackAction' => isset($editionalParams['trackAction'])  ? $editionalParams['trackAction'] : true
            );

            $event = new OW_Event('base.after_avatar_update', array_merge( $editionalParams, $params) );
            OW::getEventManager()->trigger($event);

            // remove old images
            if ( isset($oldHash) )
            {
                $oldAvatarPath = $this->getAvatarPath($userId, 1, $oldHash);
                $oldAvatarBigPath = $this->getAvatarPath($userId, 2, $oldHash);
                $oldAvatarOriginalPath = $this->getAvatarPath($userId, 3, $oldHash);

                $this->removeAvatarImage($oldAvatarPath);
                $this->removeAvatarImage($oldAvatarBigPath);
                $this->removeAvatarImage($oldAvatarOriginalPath);
            }

            $storage = OW::getStorage();

            $storage->copyFile($avatarPFOriginalPath, $avatarOriginalPath);
            $storage->copyFile($avatarPFBigPath, $avatarBigPath);
            $storage->copyFile($avatarPFPath, $avatarPath);

            @unlink($avatarPFPath);
            @unlink($avatarPFBigPath);
            @unlink($avatarPFOriginalPath);

            return true;
        }
        catch ( Exception $e )
        {
            return false;
        }
    }

    public function uploadUserTempAvatar( $key, $uploadedFileName )
    {
        $path = $this->getTempAvatarPath($key, 3);

        if ( !is_writable(dirname($path)) )
        {
            @unlink($uploadedFileName);

            return false;
        }

        if ( move_uploaded_file($uploadedFileName, $path) )
        {
            @unlink($uploadedFileName);

            return true;
        }

        @unlink($uploadedFileName);

        return false;
    }

    public function deleteUserTempAvatar( $key, $size = null )
    {
        if ( !$key )
        {
            return false;
        }

        if ( $size === null )
        {
            @unlink($this->getTempAvatarPath($key, 1));
            @unlink($this->getTempAvatarPath($key, 2));
            @unlink($this->getTempAvatarPath($key, 3));

            return true;
        }

        $path = $this->getTempAvatarPath($key, $size);

        if ( file_exists($path) )
        {
            @unlink($path);
        }

        return true;
    }

    public function deleteTempAvatars( )
    {
        $path = OW::getPluginManager()->getPlugin('base')->getUserFilesDir() . 'avatars' . DS . 'tmp' . DS;

        if ( $handle = opendir($path) )
        {
            while ( false !== ($file = readdir($handle)) )
            {
                if ( !is_file($path.$file) )
                {
                    continue;
                }

                if ( time() - filemtime($path.$file) >= 60*60*24 )
                {
                    if ( !preg_match('/\.jpg$/i', $file) )
                    {
                        continue;
                    }

                    @unlink($path.$file);
                }
            }
        }
    }

    /**
     * Give avatar original new name after hash is changed
     *
     * @param int $userId
     * @param int $oldHash
     * @param int $newHash
     */
    public function renameAvatarOriginal( $userId, $oldHash, $newHash )
    {
        $originalPath = $this->getAvatarPath($userId, 3, $oldHash);
        $newPath = $this->getAvatarPath($userId, 3, $newHash);

        OW::getStorage()->renameFile($originalPath, $newPath);
    }

    /**
     * Get url to access avatar image
     *
     * @param int $userId
     * @param int $size
     * @param null $hash
     * @param bool $checkCache
     * @return string
     */
    public function getAvatarUrl( $userId, $size = 1, $hash = null, $checkCache = true, $checkModerationStatus = true )
    {
        $event = new OW_Event("base.avatars.get_list", array(
            "userIds" => array($userId),
            "size" => $size,
            "checkModerationStatus" => $checkModerationStatus
        ));

        $eventAvatars = OW::getEventManager()->trigger($event)->getData();

        if ( isset($eventAvatars[$userId]) )
        {
            return $eventAvatars[$userId];
        }

        $avatar = $this->avatarDao->findByUserId($userId, false);

        if ( $avatar )
        {
            return $this->getAvatarUrlByAvatarDto($avatar, $size, $hash, $checkModerationStatus);
        }

        return null;
    }

    /**
     * Returns default avatar URL
     *
     * @param int $size
     * @return string
     */
    public function getDefaultAvatarUrl( $size = 1 )
    {
        $custom = self::getCustomDefaultAvatarUrl($size);

        if ( $custom != null )
        {
            return $custom;
        }

        // remove dirty check isMobile
        switch ( $size )
        {
            case 1:
                return OW::getThemeManager()->getSelectedTheme()->getStaticImagesUrl(OW::getApplication()->isMobile()) . 'no-avatar.png';

            case 2:
                return OW::getThemeManager()->getSelectedTheme()->getStaticImagesUrl(OW::getApplication()->isMobile()) . 'no-avatar-big.png';
        }

        return null;
    }

    private function getCustomDefaultAvatarUrl( $size = 1 )
    {
        if ( !in_array($size, array(1, 2)) )
        {
            return null;
        }

        $conf = json_decode(OW::getConfig()->getValue('base', 'default_avatar'), true);

        if ( isset($conf[$size]) )
        {
            $path = OW::getPluginManager()->getPlugin('base')->getUserFilesDir() . 'avatars' . DS . $conf[$size];

            return OW::getStorage()->getFileUrl($path);
        }

        return null;
    }

    public function setCustomDefaultAvatar( $size, $file )
    {
        $conf = json_decode(OW::getConfig()->getValue('base', 'default_avatar'), true);

        $dir = OW::getPluginManager()->getPlugin('base')->getUserFilesDir() . 'avatars' . DS;

        $ext = UTIL_File::getExtension($file['name']);
        $prefix = 'default_' . ($size == 1 ? self::AVATAR_PREFIX : self::AVATAR_BIG_PREFIX);

        $fileName = $prefix . uniqid() . '.' . $ext;

        if ( is_uploaded_file($file['tmp_name']) )
        {
            $storage = OW::getStorage();

            if ( $storage->copyFile($file['tmp_name'], $dir . $fileName) )
            {
                if ( isset($conf[$size]) )
                {
                    $storage->removeFile($dir . $conf[$size]);
                }

                $conf[$size] = $fileName;
                OW::getConfig()->saveConfig('base', 'default_avatar', json_encode($conf));

                return true;
            }
        }

        return false;
    }

    public function deleteCustomDefaultAvatar( $size )
    {
        $conf = json_decode(OW::getConfig()->getValue('base', 'default_avatar'), true);

        if ( !isset($conf[$size]) )
        {
            return false;
        }

        $dir = OW::getPluginManager()->getPlugin('base')->getUserFilesDir() . 'avatars' . DS;

        $storage = OW::getStorage();
        $storage->removeFile($dir . $conf[$size]);

        unset($conf[$size]);
        OW::getConfig()->saveConfig('base', 'default_avatar', json_encode($conf));

        return true;
    }

    /**
     * Returns list of users' avatars
     *
     * @param array $userIds
     * @param int $size
     * @return array
     */
    public function getAvatarsUrlList( array $userIds, $size = 1 )
    {
        if ( empty($userIds) || !is_array($userIds) )
        {
            return array();
        }

        $event = new OW_Event("base.avatars.get_list", array(
            "userIds" => $userIds,
            "size" => $size,
            "checkModerationStatus" => true
        ));

        $eventAvatars = OW::getEventManager()->trigger($event)->getData();

        if ( !empty($eventAvatars) )
        {
            return $eventAvatars;
        }

        $urlsList = array_fill(0, count($userIds), $this->getDefaultAvatarUrl($size));
        $urlsList = array_combine($userIds, $urlsList);

        $avatars = $this->avatarDao->getAvatarsList($userIds);

        foreach ( $avatars as $avatar )
        {
            $urlsList[$avatar->userId] =  $this->getAvatarUrlByAvatarDto($avatar, $size);
        }

        return $urlsList;
    }

    /**
     * Returns avatar file name for given avatar dto and size
     *
     * @param BOL_Avatar $avatar
     * @param int $size
     * @param string|null $hash
     * @param bool|true $checkModerationStatus
     *
     * @return null|string
     */
    public function getAvatarUrlByAvatarDto( $avatar, $size = 1, $hash = null, $checkModerationStatus = true )
    {
        $dir = OW::getPluginManager()->getPlugin('base')->getUserFilesDir() . 'avatars' . DS;

        if ( $checkModerationStatus && $avatar->getStatus() != BOL_ContentService::STATUS_ACTIVE )
        {
            return $this->getDefaultAvatarUrl($size);
        }

        $hash = isset($hash) ? $hash : $avatar->getHash();
        $avatarFile = $this->getAvatarFileName($avatar->userId, $hash, $size);

        if ( empty($avatarFile) )
        {
            return null;
        }

        return OW::getStorage()->getFileUrl($dir . $avatarFile);
    }


    /**
     * Composes avatar file name
     *
     * @param int $userId
     * @param int $size
     * @param null $hash
     * @return null|string
     */
    public function getAvatarFileName( $userId, $hash, $size = 1 )
    {
        switch ( $size )
        {
            case 1:
                return self::AVATAR_PREFIX . $userId . '_' . $hash . '.jpg';

            case 2:
                return self::AVATAR_BIG_PREFIX . $userId . '_' . $hash . '.jpg';

            case 3:
                return self::AVATAR_ORIGINAL_PREFIX . $userId . '_' . $hash . '.jpg';
        }

        return null;
    }

    /**
     * Get avatar path in filesystem
     *
     * @param int $userId
     * @param int $size
     * @param int $hash
     * @return string
     */
    public function getAvatarPath( $userId, $size = 1, $hash = null )
    {
        $avatar = $this->avatarDao->findByUserId($userId);

        $dir = $this->getAvatarsDir();

        if ( $avatar )
        {
            $hash = isset($hash) ? $hash : $avatar->getHash();
        }

        $fileName = $this->getAvatarFileName($userId, $hash, $size);

        return $fileName ? $dir . $fileName : null;
    }

    public function getAvatarPluginFilesPath( $userId, $size = 1, $hash = null )
    {
        $avatar = $this->avatarDao->findByUserId($userId);

        $dir = $this->getAvatarsPluginFilesDir();

        if ( $avatar )
        {
            $hash = isset($hash) ? $hash : $avatar->getHash();
        }

        $fileName = $this->getAvatarFileName($userId, $hash, $size);

        return $fileName ? $dir . $fileName : null;
    }

    public function getTempAvatarPath( $key, $size = 1 )
    {
        $dir = $this->getAvatarsDir() . 'tmp' . DS;

        switch ( $size )
        {
            case 1:
                return $dir . self::AVATAR_PREFIX . $key . '.jpg';

            case 2:
                return $dir . self::AVATAR_BIG_PREFIX . $key . '.jpg';

            case 3:
                return $dir . self::AVATAR_ORIGINAL_PREFIX . $key . '.jpg';
        }

        return null;
    }

    public function getTempAvatarUrl( $key, $size = 1 )
    {
        $url = OW::getPluginManager()->getPlugin('base')->getUserFilesUrl() . 'avatars/tmp/';

        switch ( $size )
        {
            case 1:
                return $url . self::AVATAR_PREFIX . $key . '.jpg';

            case 2:
                return $url . self::AVATAR_BIG_PREFIX . $key . '.jpg';

            case 3:
                return $url . self::AVATAR_ORIGINAL_PREFIX . $key . '.jpg';
        }

        return null;
    }

    public function getAvatarsDir()
    {
        return OW::getPluginManager()->getPlugin('base')->getUserFilesDir() . 'avatars' . DS;
    }

    public function getAvatarsPluginFilesDir()
    {
        return OW::getPluginManager()->getPlugin('base')->getPluginFilesDir() . 'avatars' . DS;
    }

    /**
     * Checks if user has avatar
     *
     * @param int $userId
     * @return boolean
     */
    public function userHasAvatar( $userId )
    {
        $avatar = $this->avatarDao->findByUserId($userId);

        return $avatar != null;
    }

    public function trackAvatarChangeActivity( $userId, $avatarId )
    {
        // Newsfeed
        $event = new OW_Event('feed.action', array(
            'pluginKey' => 'base',
            'entityType' => 'avatar-change',
            'entityId' => $avatarId,
            'userId' => $userId,
            'replace' => true
        ), array(
            'string' => OW::getLanguage()->text('base', 'avatar_feed_string'),
            /* 'content' => '<img src="' . $this->getAvatarUrl($userId) . '" />', */
            'view' => array(
                'iconClass' => 'ow_ic_picture'
            )
        ));
        OW::getEventManager()->trigger($event);
    }

    public function getDataForUserAvatars( $userIdList, $src = true, $url = true, $dispName = true, $role = true )
    {
        if ( !count($userIdList) )
        {
            return null;
        }

        $data = array();

        if ( $src )
        {
            $srcArr = $this->getAvatarsUrlList($userIdList);
        }

        $userService = BOL_UserService::getInstance();

        if ( $url )
        {
            $usernameList = BOL_UserService::getInstance()->getUserNamesForList($userIdList);
            $urlArr = $userService->getUserUrlsListForUsernames($usernameList);

            if ( $urlArr )
            {
                foreach ( $urlArr as $userId => $userUrl )
                {
                    $data[$userId]['urlInfo'] = array(
                        'routeName' => 'base_user_profile',
                        'vars' => array('username' => $usernameList[$userId])
                    );
                }
            }
        }

        if ( $dispName )
        {
            $dnArr = BOL_UserService::getInstance()->getDisplayNamesForList($userIdList);
        }

        if ( $role )
        {
            $roleArr = BOL_AuthorizationService::getInstance()->getRoleListOfUsers($userIdList);
        }

        foreach ( $userIdList as $userId )
        {
            $data[$userId]["userId"] = $userId;

            if ( $src )
            {
                $data[$userId]['src'] = !empty($srcArr[$userId]) ? $srcArr[$userId] : '_AVATAR_SRC_';
            }
            if ( $url )
            {
                $data[$userId]['url'] = !empty($urlArr[$userId]) ? $urlArr[$userId] : '#_USER_URL_';
            }
            if ( $dispName )
            {
                $data[$userId]['title'] = !empty($dnArr[$userId]) ? $dnArr[$userId] : null;
            }
            if ( $role )
            {
                $data[$userId]['label'] = !empty($roleArr[$userId]) ? $roleArr[$userId]['label'] : null;
                $data[$userId]['labelColor'] = !empty($roleArr[$userId]) ? $roleArr[$userId]['custom'] : null;
            }
        }

        return $data;
    }

    public function collectAvatarChangeSections()
    {
        $event = new BASE_CLASS_EventCollector(
            'base.avatar_change_collect_sections',
            array('limit' => self::AVATAR_CHANGE_GALLERY_LIMIT)
        );

        OW::getEventManager()->trigger($event);

        $data = $event->getData();

        return $data;
    }

    public function getAvatarChangeSection( $entityType, $entityId, $offset )
    {
        $params = array('entityType' => $entityType, 'entityId' => $entityId, 'offset' => $offset, 'limit' => self::AVATAR_CHANGE_GALLERY_LIMIT);
        $event = new BASE_CLASS_EventCollector('base.avatar_change_get_section', $params);

        OW::getEventManager()->trigger($event);

        $data = $event->getData();

        if ( !empty($data[0]) && count($data[0]) )
        {
            foreach ( $data[0]['list'] as &$image )
            {
                $image['entityType'] = $entityType;
                $image['entityId'] = $entityId;
            }

            return $data[0];
        }

        return $data;
    }

    public function getAvatarChangeGalleryItem( $entityType, $entityId, $itemId )
    {
        if ( !$entityType || !$itemId )
        {
            return null;
        }

        $params = array('entityType' => $entityType, 'entityId' => $entityId, 'id' => $itemId);
        $event = new OW_Event('base.avatar_change_get_item', $params);

        OW::getEventManager()->trigger($event);

        $data = $event->getData();

        return $data;
    }

    public function getAvatarChangeSessionKey()
    {
        $key = OW::getSession()->get(self::AVATAR_CHANGE_SESSION_KEY);

        return $key;
    }

    public function setAvatarChangeSessionKey()
    {
        $key = OW::getSession()->get(self::AVATAR_CHANGE_SESSION_KEY);

        if ( !strlen($key) )
        {
            $key = uniqid();
            OW::getSession()->set(self::AVATAR_CHANGE_SESSION_KEY, $key);
        }
    }

    public function createAvatar( $userId, $isModerable = true, $trackAction = true)
    {
        $key = $this->getAvatarChangeSessionKey();
        $path = $this->getTempAvatarPath($key, 2);

        if ( !file_exists($path) )
        {
            return false;
        }

        if ( !UTIL_File::validateImage($path) )
        {
            return false;
        }

        $event = new OW_Event('base.before_avatar_change', array(
            'userId' => $userId,
            'avatarId' => null,
            'upload' => true,
            'crop' => false,
            'isModerable' => $isModerable
        ));
        OW::getEventManager()->trigger($event);

        $avatarSet = $this->setUserAvatar($userId, $path, array('isModerable' => $isModerable, 'trackAction' => $trackAction ));

        if ( $avatarSet )
        {
            $avatar = $this->findByUserId($userId);

            if ( $avatar )
            {
                $event = new OW_Event('base.after_avatar_change', array(
                    'userId' => $userId,
                    'avatarId' => $avatar->id,
                    'upload' => true,
                    'crop' => false
                ));
                OW::getEventManager()->trigger($event);
            }

            $this->deleteUserTempAvatar($key);
        }

        return $avatarSet;
    }
}