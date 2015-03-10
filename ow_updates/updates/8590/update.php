<?php

UPDATE_LanguageService::getInstance()->deleteLangKey('admin', 'input_settings_allow_photo_upload_label');
UPDATE_ConfigService::getInstance()->deleteConfig('base', 'tf_allow_pic_upload');