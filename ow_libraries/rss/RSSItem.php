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
 * @link			http://www.transio.org/framework/data/rss/RSSItem.php
 * @package			org.transio.framework
 * @subpackage		org.transio.framework.data.rss
 * @since			Transio Framework (tm) Media Library v 0.0.1
 * @version			0.0.1
 * @modifiedby		Steven Moseley
 * @lastmodified		2009/02/23
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */



/**
  * RSSItem
  * This class is used to model an RSS Item
  * 
  * Example:
  * 
  * $item = $rssFeed->current();
  * print($item->title);
  * print($item->description);
  * print($item->link);
  *  
  */
class RSSItem {
    
	public $title;
	public $description;
	public $link;
	public $date;
	
	public function __construct($title, $description, $link, $date) {
		$this->title = $title;
		$this->description = $description;
		$this->link = $link;
		$this->date = $date;
	}
}
?>