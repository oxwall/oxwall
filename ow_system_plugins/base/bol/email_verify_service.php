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
 * Email Verify Service Class
 *
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_EmailVerifyService
{
    const TYPE_USER_EMAIL = 'user';
    const TYPE_SITE_EMAIL = 'site';


    /**
     * @var BOL_QuestionDao
     */
    private $emailVerifiedDao;

    /**
     * Constructor.
     *
     */
    private function __construct()
    {
        $this->emailVerifiedDao = BOL_EmailVerifyDao::getInstance();
    }
    /**
     * Singleton instance.
     *
     * @var BOL_EmailVerifyService
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_EmailVerifyService
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
            self::$classInstance = new self();

        return self::$classInstance;
    }

    /**
     * @param BOL_EmailVerified $object
     */
    public function saveOrUpdate( BOL_EmailVerified $object )
    {
        $this->emailVerifiedDao->save($object);
    }

    /**
     * @param string $email
     * @param string $type
     * @return BOL_EmailVerified
     */
    public function findByEmail( $email, $type )
    {
        return $this->emailVerifiedDao->findByEmail($email, $type);
    }

    /**
     * @param string $email
     * @param int $userId
     * @param string $type
     * @return BOL_EmailVerified
     */
    public function findByEmailAndUserId( $email, $userId, $type )
    {
        return $this->emailVerifiedDao->findByEmailAndUserId($email, $userId, $type);
    }

    /**
     * @param string $hash
     * @return BOL_EmailVerified
     */
    public function findByHash( $hash )
    {
        return $this->emailVerifiedDao->findByHash($hash);
    }

    /**
     * @return string
     */
    public function generateHash()
    {
        return md5(uniqid());
    }

    /**
     * @param array $objects
     */
    public function batchReplace( $objects )
    {
        $this->emailVerifiedDao->batchReplace($objects);
    }

    /**
     * @param int $id
     */
    public function deleteById( $id )
    {
        $this->emailVerifiedDao->deleteById($id);
    }

    /**
     * @param int $userId
     */
    public function deleteByUserId( $userId )
    {
        $this->emailVerifiedDao->deleteByUserId($userId);
    }

    /**
     * @param int $stamp
     */
    public function deleteByCreatedStamp( $stamp )
    {
        $this->emailVerifiedDao->deleteByCreatedStamp($stamp);
    }

    public function sendVerificationMail( $type, $params )
    {
        $subject = $params['subject'];
        $template_html = $params['body_html'];
        $template_text = $params['body_text'];

        switch ( $type )
        {
            case self::TYPE_USER_EMAIL:
                $user = $params['user'];
                $email = $user->email;
                $userId = $user->id;

                break;

            case self::TYPE_SITE_EMAIL:
                $email = OW::getConfig()->getValue('base', 'unverify_site_email');
                $userId = 0;

                break;

            default :
                OW::getFeedback()->error($language->text('base', 'email_verify_verify_mail_was_not_sent'));
                return;
        }

        $emailVerifiedData = BOL_EmailVerifyService::getInstance()->findByEmailAndUserId($email, $userId, $type);

        if ( $emailVerifiedData !== null )
        {
            $timeLimit = 60 * 60 * 24 * 3; // 3 days

            if ( time() - (int) $emailVerifiedData->createStamp >= $timeLimit )
            {
                $emailVerifiedData = null;
            }
        }

        if ( $emailVerifiedData === null )
        {
            $emailVerifiedData = new BOL_EmailVerify();
            $emailVerifiedData->userId = $userId;
            $emailVerifiedData->email = trim($email);
            $emailVerifiedData->hash = BOL_EmailVerifyService::getInstance()->generateHash();
            $emailVerifiedData->createStamp = time();
            $emailVerifiedData->type = $type;

            BOL_EmailVerifyService::getInstance()->batchReplace(array($emailVerifiedData));
        }

        $vars = array(
            'code' => $emailVerifiedData->hash,
            'url' => OW::getRouter()->urlForRoute('base_email_verify_code_check', array('code' => $emailVerifiedData->hash)),
            'verification_page_url' => OW::getRouter()->urlForRoute('base_email_verify_code_form')
        );

        $language = OW::getLanguage();

        $subject = UTIL_String::replaceVars($subject, $vars);
        $template_html = UTIL_String::replaceVars($template_html, $vars);
        $template_text = UTIL_String::replaceVars($template_text, $vars);

        $mail = OW::getMailer()->createMail();
        $mail->addRecipientEmail($emailVerifiedData->email);
        $mail->setSubject($subject);
        $mail->setHtmlContent($template_html);
        $mail->setTextContent($template_text);

        OW::getMailer()->send($mail);

        if ( !isset($params['feedback']) || $params['feedback'] !== false )
        {
            OW::getFeedback()->info($language->text('base', 'email_verify_verify_mail_was_sent'));
        }
    }

    public function sendUserVerificationMail( BOL_User $user, $feedback = true )
    {
        $vars = array(
            'username' => BOL_UserService::getInstance()->getDisplayName($user->id),
        );

        $language = OW::getLanguage();

        $subject = $language->text('base', 'email_verify_subject', $vars);
        $template_html = $language->text('base', 'email_verify_template_html', $vars);
        $template_text = $language->text('base', 'email_verify_template_text', $vars);

        $params = array(
            'user' => $user,
            'subject' => $subject,
            'body_html' => $template_html,
            'body_text' => $template_text
        );

        $this->sendVerificationMail(self::TYPE_USER_EMAIL, $params);
    }

    public function sendSiteVerificationMail( $feedback = true )
    {
        $language = OW::getLanguage();

        $subject = $language->text('base', 'site_email_verify_subject');
        $template_html = $language->text('base', 'site_email_verify_template_html');
        $template_text = $language->text('base', 'site_email_verify_template_text');

        $params = array(
            'subject' => $subject,
            'body_html' => $template_html,
            'body_text' => $template_text
        );

        $this->sendVerificationMail(self::TYPE_SITE_EMAIL, $params);
    }

    /**
     * @param string $code
     */
    public function verifyEmail( $code )
    {
        $language = OW::getLanguage();

        /* @var BOL_EmailVerified */
        $emailVerifyData = $this->findByHash($code);
        if ( $emailVerifyData !== null )
        {
            switch ( $emailVerifyData->type )
            {
                case self::TYPE_USER_EMAIL:

                    $user = BOL_UserService::getInstance()->findUserById($emailVerifyData->userId);
                    if ( $user !== null )
                    {
                        if ( OW::getUser()->isAuthenticated() )
                        {
                            if ( OW::getUser()->getId() !== $user->getId() )
                            {
                                OW::getUser()->logout();
                            }
                        }

                        OW::getUser()->login($user->getId());

                        $this->deleteById($emailVerifyData->id);

                        $user->emailVerify = true;
                        BOL_UserService::getInstance()->saveOrUpdate($user);

                        OW::getFeedback()->info($language->text('base', 'email_verify_email_verify_success'));
                        
                        OW::getApplication()->redirect(OW::getRouter()->urlForRoute('base_default_index'));
                    }
                    break;

                case self::TYPE_SITE_EMAIL:

                    OW::getConfig()->saveConfig('base', 'site_email', $emailVerifyData->email);
                    BOL_LanguageService::getInstance()->generateCacheForAllActiveLanguages();
                    OW::getFeedback()->info($language->text('base', 'email_verify_email_verify_success'));
                    OW::getApplication()->redirect(OW::getRouter()->urlForRoute('admin_settings_main'));

                    break;
            }
        }
    }
}
?>
