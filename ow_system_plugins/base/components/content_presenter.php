<?php

class BASE_CMP_ContentPresenter extends OW_Component
{
    private static $presenters = array();

    protected $content;
    
    public function __construct( $content )
    {
        parent::__construct();
        
        $this->content = empty($content) ? array() : $content;
    }
    
    private function detectFormat( $content )
    {
        $formats = array(
            "video" => array("html"),
            "image_content" => array("image", "title", "description"),
            "image" => array("image"),
            "content" => array("title", "description"),
            "text" => array("text")
        );
        
        foreach ( $formats as $format => $fields )
        {
            if ( $this->testArray($content, $fields) )
            {
                return $format;
            }
        }
        
        return "empty";
    }
        
    public function getPresenters()
    {
        if ( !empty(self::$presenters) )
        {
            return self::$presenters;
        }
        
        $presenters = array(
            "video"         => "BASE_ContentPresenterVideo",
            "image"         => "BASE_ContentPresenterImage",
            "image_content" => "BASE_ContentPresenterImageContent",
            "content"       => "BASE_ContentPresenterContent",
            "text"          => "BASE_ContentPresenterText"
        );
        
        $event = new BASE_CLASS_EventCollector("content.collect_presenters");
        OW::getEventManager()->trigger($event);
        
        foreach ( $event->getData() as $format )
        {
            $presenters[$format["name"]] = $format["class"];
        }
        
        self::$presenters = $presenters;
        
        return self::$presenters;
    }
    
    public function render() 
    {
        $presenters = $this->getPresenters();
        $typeInfo = $this->content["typeInfo"];
        
        if ( !empty($this->content["displayFormat"]) )
        {
            $typeInfo["displayFormat"] = $this->content["displayFormat"];
        }
        
        $typeInfo["displayFormat"] = empty($typeInfo["displayFormat"]) 
                ? $this->detectFormat($this->content)
                : $typeInfo["displayFormat"];
        
        $presenterClass = empty($presenters[$typeInfo["displayFormat"]])
                ? "BASE_ContentPresenter"
                : $presenters[$typeInfo["displayFormat"]];
        
        $contentDefaults = BOL_ContentService::getInstance()->_contentDataDefaults();
        $tplContent = array_merge($contentDefaults, $this->content);
        
        $presenter = new $presenterClass($tplContent, $typeInfo["displayFormat"]);
        
        return $presenter->render();
    }
    
    private function testArray( $array, $fields )
    {
        foreach ( $array as &$value )
        {
            $value = is_array($value) ? array_filter($value) : $value;
            $value = is_string($value) ? trim($value) : $value;
        }
        
        return !array_diff($fields, array_keys(array_filter($array)));
    }
}



// Presenters

class BASE_ContentPresenter extends OW_Component
{
    protected $content = array();
    protected $displayFormat;
    protected $uniqId;

    public function __construct( $content, $displayFormat ) 
    {
        parent::__construct();
        
        $this->setTemplate(OW::getPluginManager()->getPlugin("base")
                ->getCmpViewDir() . "content_presenter.html");
        
        $this->content = $content;
        $this->displayFormat = $displayFormat;
        
        $this->assign("displayFormat", $displayFormat);
        $this->uniqId = uniqid("cp-");
    }
    
    protected function processImage()
    {
        $imageSet = & $this->content["image"];
        if ( is_string($imageSet) )
        {
            $imageSet = array(
                "fullsize" => $imageSet
            );
        }
        
        // Processing image data to ensure all image types are defined
        $contentDefaults = BOL_ContentService::getInstance()->_contentDataDefaults();
        $imageTypes = array_keys($contentDefaults["image"]);
        $imageTypesCount = count($imageTypes);
        $filledType = null;

        for ( $typeIndex = 0; $typeIndex < $imageTypesCount * 2; $typeIndex++ ) 
        {
            $imageType = $imageTypes[$typeIndex >= $imageTypesCount 
                    ? $imageTypesCount - ($typeIndex - $imageTypesCount) - 1 
                    : $typeIndex];

            $filledType = empty($imageSet[$imageType]) ? $filledType : $imageType;

            if ( $filledType && empty($imageSet[$imageType]) )
            {
                $imageSet[$imageType] = $imageSet[$filledType];
            }
        }
    }
    
    public function onBeforeRender() 
    {
        parent::onBeforeRender();

        $event = new OW_Event("content.before_presenter_render", array(
            "name" => $this->displayFormat,
            "content" => $this->content,
            "uniqId" => $this->uniqId
        ), $this->content);
        
        OW::getEventManager()->trigger($event);
        
        $this->content = $event->getData();
        
        $this->prepare();
        $this->assign("data", $this->content);
        $this->assign("uniqId", $this->uniqId);
    }
    
    protected function prepare() {}
}

class BASE_ContentPresenterText extends BASE_ContentPresenter
{
    protected function prepare() 
    {
        parent::prepare();
        
        $this->content["text"] = empty($this->content["text"]) 
                ? null
                : strip_tags($this->content["text"]);
    }
}

class BASE_ContentPresenterContent extends BASE_ContentPresenterText
{
    protected function prepare() 
    {
        parent::prepare();
        
        $this->content["description"] = empty($this->content["description"]) 
                ? null
                : strip_tags($this->content["description"]);
    }
}

class BASE_ContentPresenterImageContent extends BASE_ContentPresenterContent
{
    protected function prepare() 
    {
        parent::prepare();
        
        $this->processImage();
    }
}

class BASE_ContentPresenterVideo extends BASE_ContentPresenterImageContent
{
    protected function prepare() 
    {
        parent::prepare();
        
        if ( $this->content['html'] )
        {
            $js = UTIL_JsGenerator::newInstance();
        
            $code = BOL_TextFormatService::getInstance()->addVideoCodeParam($this->content['html'], "autoplay", 1);
            $code = BOL_TextFormatService::getInstance()->addVideoCodeParam($code, "play", 1);

            $js->addScript('$("[data-action=play]", "#" + {$uniqId}).click(function(e) { '
                    . 'e.preventDefault(); e.stopPropagation();'
                    . '$(".ow_newsfeed_oembed_atch", "#" + {$uniqId}).addClass("ow_video_playing"); '
                    . '$(".ow_newsfeed_item_picture", "#" + {$uniqId}).html({$embed});'
                    . 'return false; });', array(
                "uniqId" => $this->uniqId,
                "embed" => $code
            ));

            OW::getDocument()->addOnloadScript($js);
        }
    }
}

class BASE_ContentPresenterImage extends BASE_ContentPresenterText
{
    protected function prepare() 
    {
        parent::prepare();
        
        $this->processImage();
    }
}