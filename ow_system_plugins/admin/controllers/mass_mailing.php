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
 * Mass Mailing
 *
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow_system_plugins.admin.controllers
 * @since 1.0
 */
class ADMIN_CTRL_MassMailing extends ADMIN_CTRL_Abstract
{
    const EMAIL_FORMAT_TEXT = 'txt';

    const EMAIL_FORMAT_HTML = 'html';

    const MAILS_ARRAY_MAX_RECORDS = 50;

    private $userService;
    private $ajaxResponderUrl;

    public function __construct()
    {
        $this->userService = BOL_UserService::getInstance();
        $this->ajaxResponderUrl = OW::getRouter()->urlFor("ADMIN_CTRL_MassMailing", "ajaxResponder");

        parent::__construct();
    }

    public function index( $params = array() )
    {
        $userService = BOL_UserService::getInstance();

        $language = OW::getLanguage();

        $this->setPageHeading($language->text('admin', 'massmailing'));
        $this->setPageHeadingIconClass('ow_ic_script');

        $massMailingForm = new Form('massMailingForm');
        $massMailingForm->setId('massMailingForm');
        
        $rolesList = BOL_AuthorizationService::getInstance()->getRoleList();
        
        $userRoles = new CheckboxGroup('userRoles');
        $userRoles->setLabel($language->text('admin', 'massmailing_user_roles_label'));
        
        foreach( $rolesList as $role )
        {
            if( $role->name != 'guest' )
            {
                $userRoles->addOption($role->name, $language->text('base', 'authorization_role_' . $role->name ));
            }
        }

        $massMailingForm->addElement($userRoles);
        
        $emailFormat = new Selectbox('emailFormat');
        $emailFormat->setLabel($language->text('admin', 'massmailing_email_format_label'));
        $emailFormat->setOptions(
            array(
                self::EMAIL_FORMAT_TEXT => $language->text('admin', 'massmailing_email_format_text'),
                self::EMAIL_FORMAT_HTML => $language->text('admin', 'massmailing_email_format_html')
        ));

        $emailFormat->setValue(self::EMAIL_FORMAT_TEXT);
        $emailFormat->setHasInvitation(false);

        if ( !empty($_POST['emailFormat']) )
        {
            $emailFormat->setValue($_POST['emailFormat']);
        }
        
        $massMailingForm->addElement($emailFormat);

        $subject = new TextField('subject');
        $subject->addAttribute('class', 'ow_text');
        $subject->addAttribute('style', 'width: auto;');
        $subject->setRequired();
        $subject->setLabel($language->text('admin', 'massmailing_subject_label'));

        if ( !empty($_POST['subject']) )
        {
            $subject->setValue($_POST['subject']);
        }

        $massMailingForm->addElement($subject);

        $body = new Textarea('body');

        if ( $emailFormat->getValue() == self::EMAIL_FORMAT_HTML )
        {
            $body = new WysiwygTextarea('body');
            $body->forceAddButtons(array( BOL_TextFormatService::WS_BTN_IMAGE, BOL_TextFormatService::WS_BTN_HTML ));
        }
        
        $body->addAttribute('class', 'ow_text');
        $body->addAttribute('style', 'width: auto;');
        $body->setRequired();
        $body->setLabel($language->text('admin', 'massmailing_body_label'));

        if ( !empty($_POST['body']) )
        {
            $body->setValue($_POST['body']);
        }

        $massMailingForm->addElement($body);

        $submit = new Submit('startMailing');
        $submit->addAttribute('class', 'ow_button');
        $submit->setValue($language->text('admin', 'massmailing_start_mailing_button'));

        $massMailingForm->addElement($submit);

        $this->addForm($massMailingForm);

        $ignoreUnsubscribe = false;
        $isActive = true;

        if ( defined( 'OW_PLUGIN_XP' ) )
        {
            $massMailingTimestamp = OW::getConfig()->getValue( 'admin', 'mass_mailing_timestamp' );

            $timeout = ($massMailingTimestamp + 60 * 60 * 24) - time();

            if ( $timeout  > 0 )
            {
                $isActive = false;
                $this->assign('expireText',  $language->text('admin', 'massmailing_expire_text', array( 'hours' => (int) ceil( $timeout / ( 60 * 60 ) ) ) ) );
            }
        }
        
        $this->assign('isActive', $isActive);

        $total = $userService->findMassMailingUserCount($ignoreUnsubscribe);

        if ( OW::getRequest()->isPost() && $isActive && isset($_POST['startMailing']) )
        {
            if ( $massMailingForm->isValid($_POST) )
            {
                $data = $massMailingForm->getValues();

                $start = 0;
                $count = self::MAILS_ARRAY_MAX_RECORDS;
                $mailCount = 0;

                $total = $userService->findMassMailingUserCount($ignoreUnsubscribe, $data['userRoles']);

                while ( $start < $total )
                {
                    $result = $this->userService->findMassMailingUsers($start, $count, $ignoreUnsubscribe, $data['userRoles']);
                    
                    $mails = array();
                    $userIdList = array();

                    foreach ( $result as $user )
                    {
                        $userIdList[] = $user->id;
                    }

                    $displayNameList = $this->userService->getDisplayNamesForList($userIdList);
                    $event = new BASE_CLASS_EventCollector('base.add_global_lang_keys');
                    OW::getEventManager()->trigger($event);
                    $vars = call_user_func_array('array_merge', $event->getData());

                    $hasUnsubscribeUrl = preg_match('/{\$(unsubscribe_url)}/i', $data['body']);
                    
                    foreach ( $result as $key => $user )
                    {
                        $vars['user_email'] = $user->email;

                        $mail = OW::getMailer()->createMail();
                        $mail->addRecipientEmail($user->email);

                        $vars['user_name'] = $displayNameList[$user->id];

                        $code = md5($user->username . $user->password);
                        
                        $vars['unsubscribe_url'] = OW::getRouter()->urlForRoute('base_massmailing_unsubscribe', array('id' => $user->id, 'code' => $code));

                        $event = new BASE_CLASS_PropertyEvent("base.massmail_on_before_fetch_user_mail", $vars, array("userId" => $user->id));
                        OW::getEventManager()->trigger($event);
                        $vars = $event->getProperties();
                        
                        $subjectText = UTIL_String::replaceVars($data['subject'], $vars);
                        $mail->setSubject($subjectText);
                        
                        if ( $data['emailFormat'] === self::EMAIL_FORMAT_HTML )
                        {
                            $htmlContent = UTIL_String::replaceVars($data['body'], $vars);
                            
                            if( !$hasUnsubscribeUrl )
                            {
                                $htmlContent .= $language->text('admin', 'massmailing_unsubscribe_link_html', array('link' => $vars['unsubscribe_url']));
                            }
                            
                            $mail->setHtmlContent($htmlContent);

                            $textContent = preg_replace("/\<br\s*[\/]?\s*\>/", "\n", $htmlContent);
                            $textContent = strip_tags($textContent);
                            $mail->setTextContent($textContent);
                        }
                        else
                        {
                            $textContent = UTIL_String::replaceVars($data['body'], $vars);
                            
                            if( !$hasUnsubscribeUrl )
                            {
                                $textContent .= "\n\n" . $language->text('admin', 'massmailing_unsubscribe_link_text', array('link' => $vars['unsubscribe_url']));
                            }
                            
                            $mail->setTextContent($textContent);
                        }

                        $mails[] = $mail;
                        $mailCount++;
                    }

                    $start += $count;
                    //printVar($mails);
                    OW::getMailer()->addListToQueue($mails);
                }

                OW::getFeedback()->info($language->text('admin', 'massmailing_send_mails_message', array('count' => $mailCount)));

                if ( defined( 'OW_PLUGIN_XP' ) )
                {
                    OW::getConfig()->saveConfig( 'admin', 'mass_mailing_timestamp', time() );
                }

                $this->redirect();
            }
        }

 
        $this->assign('userCount', $total);

        $language->addKeyForJs('admin', 'questions_empty_lang_value');
        $language->addKeyForJs('admin', 'massmailing_total_members');

        $script = ' window.massMailing = new MassMailing(\'' . $this->ajaxResponderUrl . '\'); ';

        OW::getDocument()->addOnloadScript($script);

        $jsDir = OW::getPluginManager()->getPlugin("admin")->getStaticJsUrl();

        OW::getDocument()->addScript($jsDir . "mass_mailing.js");
    }

    public function ajaxResponder()
    {
        if ( empty($_POST["command"]) || !OW::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }

        $command = (string) $_POST["command"];

        switch ( $command )
        {
            case 'countMassMailingUsers':

                $params = json_decode($_POST['values'], true);
                
                $ignoreUnsubscribe = false;
                $roles = array();

                if ( isset($params['ignoreUnsubscribe']) )
                {
                    $ignoreUnsubscribe = true;
                }

                if ( isset($params['roles']) && is_array($params['roles']) )
                {
                    $roles = $params['roles'];
                }

                $result = $this->userService->findMassMailingUserCount($ignoreUnsubscribe, $roles);

                echo json_encode(array('result' => (int) $result));

                break;

            default:
        }
        exit;
    }
}
