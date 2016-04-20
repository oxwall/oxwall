<?php

require_once dirname(__FILE__) . DS . 'providers.php';

class OEmbed
{
    private static $providers = array();

    /**
     *
     * @var OEmbedProvider
     */
    private static $defaultProvider;

    public static function addProvider( OEmbedProvider $provider )
    {
        self::$providers[] = $provider;
    }

    public static function parse( $url )
    {
        $oembed = null;

        foreach ( self::$providers as $p )
        {
            if ( $p->check($url) )
            {
                $oembed = $p->parse($url);
                break;
            }
        }

        if ( empty($oembed) )
        {
            self::$defaultProvider = empty(self::$defaultProvider)
                    ? new OEmbedDefaultProvider()
                    : self::$defaultProvider;

            $oembed = self::$defaultProvider->parse($url);
        }

        if ( !empty($oembed) )
        {
            $oembed['href'] = $url;
        }

        return $oembed;
    }
}

abstract class OEmbedProvider
{
    public abstract function check( $url );
    public abstract function parse( $url );
}

class OEmbedApiProvider extends OEmbedProvider
{
    private $api;
    private $scheme = array();

    public function __construct( $api, $scheme )
    {
        $this->api = $api;
        $this->scheme = (array) $scheme;
    }

    private function request( $requestUrl )
    {
        $response = UTIL_HttpResource::getContents($requestUrl);

        if ( empty($response) )
        {
            return null;
        }

        return json_decode($response, true);
    }

    private function getRequestUrl( $url )
    {
        return str_replace(':url', urlencode($url), $this->api);
    }

    public function check( $url )
    {
        foreach ( $this->scheme as $s )
        {
            if ( preg_match($s, $url) )
            {
                return true;
            }
        }

        return false;
    }

    public function parse( $url )
    {
        return $this->request($this->getRequestUrl($url));
    }
}


class OEmbedDefaultProvider extends OEmbedProvider
{
    public function __construct()
    {

    }

    public function check( $url )
    {
        return true;
    }

    private function getType( $url )
    {
        $urlInfo = parse_url($url);

        if ( empty($urlInfo['path']) )
        {
            return 'link';
        }

        $foo = explode('.', $urlInfo['path']);
        $ext = end($foo);

        switch ( trim($ext) )
        {
           case 'gif':
           case 'jpeg':
           case 'jpg':
           case 'png':
                return 'image';

            default :
                return 'link';
        }
    }

    private function parsePage( $url )
    {
        $content = @UTIL_HttpResource::getContents($url);

        $matches = array();
        preg_match('/<\s*meta\s*[^\>]*?http-equiv=[\'"]content-type[\'"][^\>]*?\s*>/i',$content,$matches);
        $meta = empty($matches[0]) ? null : $matches[0];

        preg_match('/content=[\'"][^\'"]*?charset=([\w-]+)(:[^\w-][^\'"])*?[\'"]/i',$meta,$matches);
        $encoding = empty($matches[1]) ? 'UTF-8' : $matches[1];

        preg_match('/<\s*title\s*>([\s\S]*?)<\s*\/\s*title\s*>/i',$content,$matches);
        $title = empty($matches[1]) ? null : mb_convert_encoding($matches[1], 'UTF-8', $encoding);

        $matches = array();
        $meta = "";
        preg_match('/<\s*meta\s*[^\>]*?name=[\'"]description[\'"][^\>]*?\s*>/i',$content,$matches);
        $meta = empty($matches[0]) ? null : $matches[0];

        $matches = array();
        preg_match('/content=[\'"](.*?)[\'"]/i',$meta,$matches);
        $description = empty($matches[1]) ? null : mb_convert_encoding($matches[1], 'UTF-8', $encoding);

        $matches = array();
        preg_match_all('/<\s*img\s*.*?src=[\'"](.+?)[\'"].*?>/i',$content, $matches);

        $images = array();

        foreach ( $matches[1] as $img )
        {
            $urlInfo = parse_url($url);
            $imgInfo = parse_url($img);

            if ( empty($imgInfo['host']) )
            {
                $imgDir = dirname($imgInfo['path']);

                $urlScheme = empty($urlInfo['scheme']) ? '' : $urlInfo['scheme'] . '://';
                $urlAddr = $urlScheme . $urlInfo['host'];

                if ( strpos($imgDir, '/') === 0 )
                {
                    $img = $urlAddr . $imgInfo['path'];
                }
                elseif ( !empty($urlInfo['path']) )
                {
                    $pp = pathinfo($urlInfo['path']);
                    $urlPath = $pp['dirname'] . ( empty($pp['extension']) ? $pp['basename'] . '/' : '' );
                    $img = $urlAddr . $urlPath . $imgInfo['path'];
                }
                else
                {
                    $img = $urlAddr . '/' . $imgInfo['path'];
                }
            }

            $images[] = $img;
        }

        $firstImg = reset($images);
        $firstImg = $firstImg ? $firstImg : null;

        return array(
            'type' => 'link',
            'description' => UTIL_HtmlTag::escapeHtml($description),
            'title' => UTIL_HtmlTag::escapeHtml($title),
            'thumbnail_url' => $firstImg,
            'allImages' => $images
        );
    }

    public function parse( $url )
    {
        $sType = $this->getType($url);

        if ( $sType == 'image' )
        {
            return array(
                'url' => $url,
                'href' => $url,
                'type' => 'photo'
            );
        }

        return $this->parsePage($url);
    }
}