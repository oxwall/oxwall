<?php
/**
 * Transio.org - Transio Framework (tm) for PHP 5 and MySQL 5
 *
 * RSS Library for reading and creating RSS Feeds
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @copyright		Copyright 2008, Transio.org
 * @link			http://www.transio.org/framework/data/rss/RSSIterator.php
 * @package			org.transio.framework
 * @subpackage		org.transio.framework.data.rss
 * @since			Transio Framework (tm) Media Library v 0.0.1
 * @version			0.0.1
 * @modifiedby		Steven Moseley
 * @lastmodified		2009/02/23
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */

require_once("RSSItem.php");

abstract class BaseIterator implements Iterator
{
    protected $items;
    protected $currentItem = 0;
    protected $limit;

    public function __construct($items, $limit=null) {

            $this->items = $items;
            $this->limit = $limit;
    }

    public function rewind() {
            $this->currentItem = 0;
    }

    public function valid() {
            return $this->currentItem < $this->items->length &&
                    (is_null($this->limit) || $this->limit == 0 || $this->currentItem < $this->limit);
    }

    public function key() {
            return $this->currentItem;
    }

    public function next() {
            $this->currentItem++;
    }

    public function seek($itemNumber) {
            $this->currentItem = $itemNumber;
    }
}

/**
  * RSSIterator
  * This class is used to load and iterate an RSS Feed
  *
  */

class RSSIterator extends BaseIterator
{
	public function current()
        {
            $title = '';
            $description = '';
            $link = '';
            $date = '';

            $itemNode = $this->items->item($this->currentItem);

            $titleNode = $itemNode->getElementsByTagName("title")->item(0);
            if ( !empty($titleNode) )
            {
                $title = $titleNode->nodeValue;
            }

            $dateNode = $itemNode->getElementsByTagName("pubDate")->item(0);
            if ( !empty($dateNode) )
            {
                $date = $dateNode->nodeValue;
            }

            $linkNode = $itemNode->getElementsByTagName("link")->item(0);
            if ( !empty($linkNode) )
            {
                $link = $linkNode->nodeValue;
            }

            $descriptionNode = $itemNode->getElementsByTagName("description")->item(0);

            if ( !empty($descriptionNode) )
            {
                $description = $descriptionNode->nodeValue;
            }

            if ( empty($description) )
            {
                $descriptionNode = $itemNode->getElementsByTagName("content")->item(0);
                if ( !empty($descriptionNode) )
                {
                    $description = $descriptionNode->nodeValue;
                }
            }

            return new RSSItem($title, $description, $link, $date);
	}
}

class AtomIterator extends BaseIterator
{
    public function current()
    {
        $title = '';
        $description = '';
        $link = '';
        $date = '';

        $itemNode = $this->items->item($this->currentItem);

        $titleNode = $itemNode->getElementsByTagName("title")->item(0);
        if ( !empty($titleNode) )
        {
            $title = $titleNode->nodeValue;
        }

        $dateNode = $itemNode->getElementsByTagName("updated")->item(0);
        if ( !empty($dateNode) )
        {
            $date = $dateNode->nodeValue;
        }

        $linkNode = $itemNode->getElementsByTagName("link")->item(0);
        $linkAttr = $linkNode->attributes->getNamedItem("href");
        if ( !empty($linkAttr) )
        {
            $link = $linkAttr->nodeValue;
        }

        $descriptionNode = $itemNode->getElementsByTagName("summary")->item(0);
        if ( !empty($descriptionNode) )
        {
            $description = $descriptionNode->nodeValue;
        }

        return new RSSItem($title, $description, $link, $date);
    }
}