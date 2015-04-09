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
class BASE_Cron extends OW_Cron
{
    const EMAIL_VARIFY_CODE_REMOVE_TIMEOUT = 432000; // 5 days
    const BILLING_SALES_EXPIRE_JOB_RUN_INTERVAL = 30;

    // minutes


    public function __construct()
    {
        parent::__construct();

        $this->addJob('dbCacheProcess', 1);
        $this->addJob('mailQueueProcess', 5);

        $this->addJob('deleteExpiredOnlineUserProcess', 1);

        //$this->addJob('expireUnverifiedSalesProcess', self::BILLING_SALES_EXPIRE_JOB_RUN_INTERVAL);

        $this->addJob('deleteExpiredOnlineUserProcess', 1);
        $this->addJob('checkPluginUpdates', 60 * 24);
        $this->addJob('deleteExpiredPasswordResetCodes', 10);
        $this->addJob('resetCronFlag', 1);
        $this->addJob('rmTempAttachments', 60 * 24);
        $this->addJob('rmTempAvatars', 60 * 24);
        $this->addJob('deleteExpiredCache', 60 * 24);
        $this->addJob('dropLogFile', 60 * 24);
        $this->addJob('clearMySqlSearchIndex', 60 * 24);

        $this->addJob('checkRealCron');
    }

    public function run()
    {
        //clean email varify code table
        BOL_EmailVerifyService::getInstance()->deleteByCreatedStamp(time() - self::EMAIL_VARIFY_CODE_REMOVE_TIMEOUT);
        BOL_UserService::getInstance()->cronSendWellcomeLetter();
    }

    public function dbCacheProcess()
    {
        // Delete expired db cache entry
        BOL_DbCacheService::getInstance()->deleteExpiredList();
    }

    public function mailQueueProcess()
    {
        // Send mails from mail queue
        BOL_MailService::getInstance()->processQueue();
    }

    public function deleteExpiredOnlineUserProcess()
    {
        BOL_UserService::getInstance()->deleteExpiredOnlineUsers();
    }

    public function expireUnverifiedSalesProcess()
    {
        BOL_BillingService::getInstance()->deleteExpiredSales();
    }

    public function expireSearchResultList()
    {
        BOL_SearchService::getInstance()->deleteExpireSearchResult();
    }

    public function clearMySqlSearchIndex()
    {
        $mysqlSearchStorage = new BASE_CLASS_MysqlSearchStorage();
        $mysqlSearchStorage->realDeleteEntities();
    }

    public function checkPluginUpdates()
    {
        BOL_PluginService::getInstance()->checkUpdates();
    }

    public function deleteExpiredPasswordResetCodes()
    {
        BOL_UserService::getInstance()->deleteExpiredResetPasswordCodes();
    }

    public function resetCronFlag()
    {
        if ( OW::getConfig()->configExists('base', 'cron_is_active') && (int) OW::getConfig()->getValue('base', 'cron_is_active') === 0 )
        {
            OW::getConfig()->saveConfig('base', 'cron_is_active', 1);
        }
    }

    public function rmTempAttachments()
    {
        BOL_AttachmentService::getInstance()->deleteExpiredTempImages();
    }

    public function rmTempAvatars()
    {
        BOL_AvatarService::getInstance()->deleteTempAvatars();
    }

    public function deleteExpiredCache()
    {
        OW::getCacheManager()->clean(array(), OW_CacheManager::CLEAN_OLD);
    }

    public function dropLogFile()
    {
        $logFilePath = OW_DIR_LOG . 'error.log';

        if ( file_exists($logFilePath) )
        {
            $logFileSize = filesize($logFilePath);

            if ( $logFileSize !== false && $logFileSize / 1024 / 1024 >= (int) OW::getConfig()->getValue('base', 'log_file_max_size_mb') )
            {
                unlink($logFilePath);
            }
        }
    }

    public function checkRealCron()
    {
        if ( !isset($_GET['ow-light-cron']) )
        {
            if ( OW::getConfig()->configExists('base', 'cron_is_configured') )
            {
                OW::getConfig()->saveConfig('base', 'cron_is_configured', 1);
            }
            else
            {
                OW::getConfig()->addConfig('base', 'cron_is_configured', 1);
            }
        }
    }
}
