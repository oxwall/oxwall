<?php

require_once("iterators.php");
require_once("RSSItem.php");

class RssParcer
{
    /**
     *
     * @param string $path
     * @param int $limit
     * @return BaseIterator
     * @throws Exception
     */
    public static function getIterator( $path, $limit = null )
    {
        $dom = new DOMDocument();

        if (!@$dom->load($path))
        {
            throw new Exception("Unable to read RSS file.");
        }

        $iteratorClass = 'RSSIterator';
        $items = null;

        switch ( true )
        {
            case $dom->getElementsByTagName('feed')->item(0) !== null:
                $iteratorClass = 'AtomIterator';
                $items = $dom->getElementsByTagName("entry");

                break;

            default :
                $iteratorClass = 'RSSIterator';
                $items = $dom->getElementsByTagName("item");
        }

        return new $iteratorClass($items, $limit);
    }
}