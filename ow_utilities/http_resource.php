<?php

require_once  OW_DIR_LIB . 'oembed' . DS. 'oembed.php';

class UTIL_HttpResource
{

    /**
     *
     * @param string $url
     * @return false|string
     */
    public static function getContents( $url, $timeout = 20 )
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_HEADER, "User-Agent: Oxwall Content Fetcher\r\n");
        curl_setopt($ch, CURLOPT_MAXREDIRS, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);

        return $output;
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
