<?php

class BASE_CTRL_MediaPanel extends OW_ActionController
{
    /**
     * @var BASE_CMP_Menu
     */
    private $menu;
    private $id;

    public function __construct()
    {
        if ( !OW::getUser()->isAuthenticated() )
        {
            $this->setVisible(false);
            return;
        }

        if ( !OW::getRequest()->isAjax() ) 
        {
            OW::getDocument()->getMasterPage()->setTemplate(OW::getThemeManager()->getMasterPageTemplate(OW_MasterPage::TEMPLATE_BLANK));
            OW::getDocument()->addStyleDeclaration(".ow_footer{display:none;}");
        }
    }

    public function ajaxUpload( $params )
    {
        $pluginKey = $params['pluginKey'];
        $result = array();

        if (OW::getRequest()->isPost())
        {
            if ( !empty($_POST['command']) && $_POST['command'] == 'image-upload' )
            {
                $imageId = UploadImageForm::addFile($pluginKey);

                if ( is_numeric($imageId) )
                {
                    $img = BOL_MediaPanelService::getInstance()->findImage($imageId);
                    $url = OW::getStorage()->getFileUrl(OW::getPluginManager()->
                            getPlugin('base')->getUserFilesDir() . $img->id . '-' . $img->getData()->name);

                    $result = array(
                        'file_url' => OW::getStorage()->
                                getFileUrl(OW::getPluginManager()->getPlugin('base')->getUserFilesDir() . $img->id . '-' . $img->getData()->name),
                    );
                }
                else {
                    $result = array(
                        'error_message' => $imageId,
                    );
                }
            }
        }

        die(json_encode($result));
    }

    public function index( $params )
    {
        $pluginKey = $params['pluginKey'];

        $this->initMenu($params);
        $this->addComponent('menu', $this->menu);
        $this->menu->getElement('upload')->setActive(true);

        $form = new UploadImageForm();

        if ( !empty($_POST['command']) && $_POST['command'] == 'image-upload' )
        {
            UploadImageForm::process($pluginKey, $params);
        }

        $this->assign('maxSize', OW::getConfig()->getValue('base', 'tf_max_pic_size'));
        
        $this->addForm($form);
    }

    public function gallery( $params )
    {

        if ( OW::getRequest()->isPost() )
        {
            $userId = OW::getUser()->getId();
            if ( empty($userId) )
            {
                throw new Exception('Guests can\'t view this page');
            }

            $imgId = intval($_POST['img-id']);

            if ( $imgId <= 0 )
            {
                throw new Redirect404Exception();
            }

            BOL_MediaPanelService::getInstance()->deleteById($imgId);

            OW::getFeedback()->info(OW::getLanguage()->text('base', 'media_panel_file_deleted'));
            $this->redirect();
        }

        $pluginKey = $params['pluginKey'];
        $this->initMenu($params);
        $this->addComponent('menu', $this->menu);
        $this->menu->getElement('gallery');

        $service = BOL_MediaPanelService::getInstance();

        $list = $service->findGalleryImages($pluginKey, OW::getUser()->getId(), 0, 500);
        $list = array_reverse($list);
        $images = array();

        foreach ( $list as $img )
        {
            $images[] = array(
                'dto' => $img,
                'data' => $img->getData(),
                'url' => OW::getStorage()->getFileUrl(OW::getPluginManager()->getPlugin('base')->getUserFilesDir() . $img->id . '-' . $img->getData()->name),
                'sel' => !empty($params['pid']) && $img->getId() == $params['pid'],
            );
        }

        $this->assign('images', $images);
        $this->assign('id', $params['id']);
    }

    public function fromUrl( $params )
    {
        $this->initMenu($params);
        $this->addComponent('menu', $this->menu);
        $this->assign('elid', $params['id']);
    }

    private function initMenu( $params )
    {
        $language = OW::getLanguage();
        $router = OW::getRouter();

        $this->menu = new BASE_CMP_ContentMenu();

        $item = new BASE_MenuItem();
        $item->setLabel($language->text('base', 'upload'));
        $item->setOrder(0);
        $item->setKey('upload');
        $item->setUrl($router->urlFor('BASE_CTRL_MediaPanel', 'index', $params));
        $this->menu->addElement($item);

        $item = new BASE_MenuItem();
        $item->setLabel($language->text('base', 'tf_img_from_url'));
        $item->setOrder(1);
        $item->setKey('url');
        $item->setUrl($router->urlFor('BASE_CTRL_MediaPanel', 'fromUrl', $params));
        $this->menu->addElement($item);

        $count = BOL_MediaPanelService::getInstance()->countGalleryImages($params['pluginKey'], OW::getUser()->getId());

        $item = new BASE_MenuItem();
        $item->setLabel($language->text('base', 'tf_img_gal') . ($count == 0 ? '' : " ({$count})" ));
        $item->setOrder(1);
        $item->setKey('gallery');
        $item->setUrl($router->urlFor('BASE_CTRL_MediaPanel', 'gallery', $params));
        $this->menu->addElement($item);
    }
}

class UploadImageForm extends Form
{

    public function __construct()
    {
        parent::__construct('image-upload');

        $this->setEnctype('multipart/form-data');

        $hidden = new HiddenField('command');

        $hidden->setValue('image-upload');

        $this->addElement($hidden);

        $hiddenMaxSize = new HiddenField('MAX_FILE_SIZE');

        $hiddenMaxSize->setValue(intval(OW::getConfig()->getValue('base', 'tf_max_pic_size')) * 1000000);

        $fileInput = new FileField('file');

        $fileInput->setLabel(OW::getLanguage()->text('base', 'tf_img_choose_file'))->setRequired(true);

        $this->addElement($fileInput);

        $submit = new Submit('submit');

        $submit->setValue(OW::getLanguage()->text('base', 'upload'));

        $this->addElement($submit);

        return $this;
    }

    /**
     * Add file
     * 
     * @param string $plugin
     * @return integer|string
     */
    public static function addFile( $plugin )
    {
        $uploaddir = OW::getPluginManager()->getPlugin('base')->getUserFilesDir();
        $name = $_FILES['file']['name'];

        if ( !UTIL_File::validateImage($name) )
        {
            return OW::getLanguage()->text('base', 'invalid_file_type_acceptable_file_types_jpg_png_gif');
        }

        $tmpname = $_FILES['file']['tmp_name'];

        if ( (int) $_FILES['file']['size'] > (float) OW::getConfig()->getValue('base', 'tf_max_pic_size') * 1024 * 1024 )
        {
            return OW::getLanguage()->text('base', 'upload_file_max_upload_filesize_error');
        }

        $image = new UTIL_Image($tmpname);
        $height = $image->getHeight();
        $width = $image->getWidth();

        $id = BOL_MediaPanelService::getInstance()->add($plugin, 'image', OW::getUser()->getId(), array('name' => $name, 'height' => $height, 'width' => $width));
        OW::getStorage()->copyFile($tmpname, $uploaddir . $id . '-' . $name);
        @unlink($tmpname);

        return $id;
    }

    public static function process( $plugin, $params )
    {
        $imageId = self::addFile($plugin);

        if (!is_numeric($imageId)) {
            OW::getFeedback()->error($imageId);
            OW::getApplication()->redirect();
        }

        $params['pid'] = $imageId;
        OW::getApplication()->redirect(OW::getRouter()->urlFor('BASE_CTRL_MediaPanel', 'gallery', $params) . '#bottom');
    }
}
