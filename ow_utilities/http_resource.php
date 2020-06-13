<?php

require_once  OW_DIR_LIB . 'oembed' . DS. 'oembed.php';

/**
 * Class UTIL_HttpResource
 */
class UTIL_HttpResource
{

    /**
     *
     * @param string $url
     * @param int    $timeout
     * @return string|false
     */
    public static function getContents( $url, $timeout = 20 )
    {
        $context = stream_context_create( array(
            'http'=>array(
                'timeout' => $timeout,
                'header' => "User-Agent: Oxwall Content Fetcher\r\n"
            )
        ));

        return file_get_contents($url, false, $context);
    }

    /**
     *
     * @param string $url
     * @return array
     */
    public static function getOEmbed( $url )
    {
        return OEmbed::parse($url);
    }
}