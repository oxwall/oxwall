<?php
/**
 * Created by PhpStorm.
 * User: jk
 * Date: 2/10/16
 * Time: 12:47 PM
 */

trait BASE_CLASS_UploadTmpAvatarTrait {

    public function uploadTmpAvatar($file)
    {
        if ( isset($file) )
        {
            $lang = OW::getLanguage();

            if ( !UTIL_File::validateImage($file['name']) )
            {
                return array('result' => false, 'error' => $lang->text('base', 'not_valid_image'));
            }

            $message = BOL_FileService::getInstance()->getUploadErrorMessage($_FILES['file']['error']);

            if ( !empty($message) )
            {
                return array('result' => false, 'error' => $message);
            }

            $filesize = OW::getConfig()->getValue('base', 'avatar_max_upload_size');

            if ( $filesize*1024*1024 < $_FILES['file']['size'] )
            {
                $message = OW::getLanguage()->text('base', 'upload_file_max_upload_filesize_error');
                return array('result' => false, 'error' => $message);
            }

            $avatarService = BOL_AvatarService::getInstance();

            $key = $avatarService->getAvatarChangeSessionKey();
            $uploaded = $avatarService->uploadUserTempAvatar($key, $file['tmp_name']);

            if ( !$uploaded )
            {
                return array('result' => false, 'error' => $lang->text('base', 'upload_avatar_faild'));
            }

            $url = $avatarService->getTempAvatarUrl($key, 3);

            return array('result' => true, 'url' => $url);
        }

        return array('result' => false);
    }
}