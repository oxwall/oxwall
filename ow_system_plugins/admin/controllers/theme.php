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
 * Theme manage admin controller class.
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_system_plugins.admin.controllers
 * @since 1.0
 */
class ADMIN_CTRL_Theme extends ADMIN_CTRL_Abstract
{
    /**
     * @var BOL_ThemeService
     *
     */
    private $themeService;
    /**
     * @var BASE_CMP_ContentMenu
     */
    private $menu;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->themeService = BOL_ThemeService::getInstance();
        $this->setDefaultAction('settings');
    }

    public function init()
    {
        $router = OW_Router::getInstance();

        $pageActions = array(array('name' => 'settings', 'iconClass' => 'ow_ic_gear_wheel'), array('name' => 'css', 'iconClass' => 'ow_ic_files'), array('name' => 'graphics', 'iconClass' => 'ow_ic_picture'));

        $menuItems = array();

        foreach ( $pageActions as $key => $item )
        {
            $menuItem = new BASE_MenuItem();
            $menuItem->setKey($item['name'])->setLabel(OW::getLanguage()->text('admin', 'sidebar_menu_item_' . $item['name']))->setOrder($key)->setUrl($router->urlForRoute('admin_theme_' . $item['name']));
            $menuItem->setIconClass($item['iconClass']);
            $menuItems[] = $menuItem;
        }

        $this->menu = new BASE_CMP_ContentMenu($menuItems);

        $this->addComponent('contentMenu', $this->menu);

        OW::getNavigation()->activateMenuItem(OW_Navigation::ADMIN_APPEARANCE, 'admin', 'sidebar_menu_item_theme_edit');
        $this->setPageHeading(OW::getLanguage()->text('admin', 'themes_settings_page_title'));
    }

    public function settings()
    {
        $dto = $this->themeService->findThemeByName(OW::getConfig()->getValue('base', 'selectedTheme'));

        if ( $dto === null )
        {
            throw new LogicException("Can't find theme `" . OW::getConfig()->getValue('base', 'selectedTheme') . "`");
        }

        $assignArray = (array) json_decode($dto->getDescription());

        $assignArray['iconUrl'] = $this->themeService->getStaticUrl($dto->getName()) . BOL_ThemeService::ICON_FILE;
        $assignArray['name'] = $dto->getName();
        $assignArray['title'] = $dto->getTitle();
        $this->assign('themeInfo', $assignArray);
        $this->assign('resetUrl', OW::getRouter()->urlFor(__CLASS__, 'reset'));

        $controls = $this->themeService->findThemeControls($dto->getId());

        if ( empty($controls) )
        {
            $this->assign('noControls', true);
        }
        else
        {
            $form = new ThemeEditForm($controls);

            $this->assign('inputArray', $form->getFormElements());

            $this->addForm($form);

            if ( OW::getRequest()->isPost() )
            {
                if ( $form->isValid($_POST) )
                {
                    $this->themeService->saveThemeControls($dto->getId(), $form->getValues());
                    $this->themeService->updateCustomCssFile($dto->getId());
                    $this->redirect();
                }
            }
        }

        $this->menu->getElement('settings')->setActive(true);
    }

    public function css()
    {
        if ( OW::getRequest()->isAjax() )
        {
            $css = isset($_POST['css']) ? trim($_POST['css']) : '';

            $dto = $this->themeService->findThemeByName(OW::getConfig()->getValue('base', 'selectedTheme'));
            $dto->setCustomCss($css);
            $this->themeService->saveTheme($dto);
            $this->themeService->updateCustomCssFile($dto->getId());

            echo json_encode(array('message' => OW::getLanguage()->text('admin', 'css_edit_success_message')));
        }

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('admin')->getStaticJsUrl() . 'prettify.js');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('admin')->getStaticJsUrl() . 'lang-css.js');
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('admin')->getStaticCssUrl() . 'prettify.css');
        OW::getDocument()->addOnloadScript("prettyPrint();");

        $fileString = file_get_contents(OW::getThemeManager()->getSelectedTheme()->getRootDir() . BOL_ThemeService::CSS_FILE_NAME);

        $this->assign('code', '<pre class="prettyprint lang-css">' . $fileString . '</pre>');

        $this->addForm(new AddCssForm());
    }

    public function graphics()
    {
        $images = $this->themeService->findAllCssImages();
        $assignArray = array();

        /* @var $value BOL_ThemeImage */
        foreach ( $images as $value )
        {
            $assignArray[] = array(
                'url' => OW::getStorage()->getFileUrl($this->themeService->getUserfileImagesDir() . $value->getFilename()),
                'delUrl' => OW::getRouter()->urlFor(__CLASS__, 'deleteImage', array('image-id' => $value->getId())),
                'cssUrl' => $this->themeService->getUserfileImagesUrl() . $value->getFilename()
            );
        }

        $this->assign('images', $assignArray);

        $form = new UploadGraphicsForm();
        $form->setEnctype(FORM::ENCTYPE_MULTYPART_FORMDATA);
        $this->addForm($form);

        $this->assign('confirmMessage', OW::getLanguage()->text('admin', 'theme_graphics_image_delete_confirm_message'));

        if ( OW::getRequest()->isPost() )
        {
            try
            {
                $this->themeService->addImage($_FILES['file']);
            }
            catch ( Exception $e )
            {
                OW::getFeedback()->error(OW::getLanguage()->text('admin', 'theme_graphics_upload_form_fail_message'));
                $this->redirect();
            }

            OW::getFeedback()->info(OW::getLanguage()->text('admin', 'theme_graphics_upload_form_success_message'));
            $this->redirect();
        }
    }

    public function resetGraphics()
    {
        $this->themeService->resetImageControl(OW::getThemeManager()->getSelectedTheme()->getDto()->getId(), trim($_GET['name']));
        $this->redirectToAction('settings');
    }

    public function reset()
    {
        $dto = $this->themeService->findThemeByName(OW::getConfig()->getValue('base', 'selectedTheme'));
        $this->themeService->resetTheme($dto->getId());
        $this->redirectToAction('settings');
    }

    public function deleteImage( $params )
    {
        $this->themeService->deleteImage((int) $params['image-id']);
        OW::getFeedback()->info(OW::getLanguage()->text('admin', 'theme_graphics_delete_success_message'));
        $this->redirectToAction('graphics');
    }
}

class UploadGraphicsForm extends Form
{

    public function __construct()
    {
        parent::__construct('upload_graphics');

        $this->addElement(new FileField('file'));

        $submit = new Submit('submit');
        $submit->setValue(OW::getLanguage()->text('admin', 'theme_graphics_upload_form_submit_label'));
        $this->addElement($submit);
    }
}

class AddCssForm extends Form
{

    public function __construct()
    {
        parent::__construct('add-css');

        $text = new Textarea('css');
        $dto = BOL_ThemeService::getInstance()->findThemeByName(OW::getConfig()->getValue('base', 'selectedTheme'));
        $text->setValue($dto->getCustomCss());
        $this->addElement($text);

        $submit = new Submit('submit');
        $submit->setValue(OW::getLanguage()->text('admin', 'theme_css_edit_submit_label'));
        $this->addElement($submit);

        $this->setAjax(true);
        $this->setAjaxResetOnSuccess(false);
        $this->bindJsFunction(Form::BIND_SUCCESS, 'function(data){OW.info(data.message)}');
    }
}

class ThemeEditForm extends Form
{
    private $formElements = array();

    public function __construct( $controls )
    {
        $this->setEnctype(Form::ENCTYPE_MULTYPART_FORMDATA);

        parent::__construct('theme-edit');

        $typeArray = array(
            'text' => 'TextField',
            'color' => 'ColorField',
            'font' => 'FontFamilyField',
            'image' => 'ImageField'        
        );

        $inputArray = array();
        
        foreach ( $controls as $value )
        {
            $refField = new ReflectionClass($typeArray[$value['type']]);
            $field = $refField->newInstance($value['key']);

            if ( $this->getElement($field->getName()) !== null )
            {
                continue;
            }

            $field->setValue($value['value'] !== null ? trim($value['value']) : trim($value['defaultValue']));
            $this->addElement($field);

            if ( !array_key_exists(trim($value['section']), $this->formElements) )
            {
                $this->formElements[trim($value['section'])] = array();
            }

            $this->formElements[trim($value['section'])][] = array('name' => $value['key'], 'title' => $value['label'], 'desc' => $value['description']);
        }

        ksort($this->formElements);

        $submit = new Submit('submit');
        $submit->setValue(OW::getLanguage()->text('admin', 'theme_settings_form_submit_label'));

        $this->addElement($submit);
    }

    public function getFormElements()
    {
        return $this->formElements;
    }
}

class FontFamilyField extends Selectbox
{

    public function __construct( $name )
    {
        parent::__construct($name);

        $this->setOptions(
            array(
                'default' => 'Default',
                'Arial, Helvetica, sans-serif' => 'Arial, Helvetica, sans-serif',
                'Times New Roman, Times, serif' => 'Times New Roman, Times, serif',
                'Courier New, Courier, monospace' => 'Courier New, Courier, monospace',
                'Georgia, Times New Roman, Times, serif' => 'Georgia, Times New Roman, Times, serif',
                'Verdana, Arial, Helvetica, sans-serif' => 'Verdana, Arial, Helvetica, sans-serif',
                'Geneva, Arial, Helvetica, sans-serif' => 'Geneva, Arial, Helvetica, sans-serif'
            )
        );

        $this->setHasInvitation(false);
    }
}

class ImageField extends FormElement
{
    private $mobile;

    public function __construct( $name, $mobile = false )
    {
        parent::__construct($name);
        $this->mobile = (bool)$mobile;
    }

    public function getValue()
    {
        return isset($_FILES[$this->getName()]) ? $_FILES[$this->getName()] : null;
    }

    /**
     * @see FormElement::renderInput()
     *
     * @param array $params
     * @return string
     */
    public function renderInput( $params = null )
    {
        parent::renderInput($params);

        $output = '';

        if ( $this->value !== null && ( trim($this->value) !== 'none' ) )
        {
            if ( !strstr($this->value, 'http') )
            {
                $resultString = substr($this->value, (strpos($this->value, 'images/') + 7));
                $this->value = 'url(' . OW::getThemeManager()->getSelectedTheme()->getStaticImagesUrl($this->mobile) . substr($resultString, 0, strpos($resultString, ')')) . ')';
            }

            $randId = 'if' . rand(10, 10000000);

            $script = "$('#" . $randId . "').click(function(){
                new OW_FloatBox({\$title:'" . OW::getLanguage()->text('admin', 'themes_settings_graphics_preview_cap_label') . "', \$contents:$('#image_view_" . $this->getName() . "'), width:'550px'});
            });";

            OW::getDocument()->addOnloadScript($script);
            
            $output .= '<div class="clearfix"><a id="' . $randId . '" href="javascript://" class="theme_control theme_control_image" style="background-image:' . $this->value . ';"></a>
                <div style="float:left;padding:10px 0 0 10px;"><a href="javascript://" onclick="window.location=\'' . OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlFor('ADMIN_CTRL_Theme', 'resetGraphics'), array('name' => $this->getName())) . '\'">' . OW::getLanguage()->text('admin', 'themes_settings_reset_label') . '</a></div></div>
                <div style="display:none;"><div class="preview_graphics" id="image_view_' . $this->getName() . '" style="background-image:' . $this->value . '"></div></div>';
        }

        $output .= '<input type="file" name="' . $this->getName() . '" />';

        return $output;
    }
}