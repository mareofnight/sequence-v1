<?php

class sq_rss {


private $doc;// the DOM document, or false if document could not be loaded

/**
 * constructor (create $this->doc, as a DOM document if path is valid, or as false if invalid)
 * @param $path URL of the RSS feed
 **/
function __construct($path) {
    $this->doc = new DOMDocument();
    $level = error_reporting(0);// suppress errors
    $success = $this->doc->load($path);
    error_reporting($level);// stop suppressing errors
    if (!$success) {
        $this->doc = false;
    }
}

/**
 * return news items as an array
 * @param $num number of news items to get
 * @return two dimensional array of news items, or false if doc is invalid
 **/
function get_news($num) {
    if (!$this->doc) {
        return false;
    }
    $itemElements = $this->doc->getElementsByTagName('item');
    $items = array();
    $counter = 0;
    foreach ($itemElements as $itemEl) {
        $title = $itemEl->getElementsByTagName('title')->item(0)->nodeValue;
        $desc = $itemEl->getElementsByTagName('description')->item(0)->nodeValue;
        $link = $itemEl->getElementsByTagName('link')->item(0)->nodeValue;
        $guid = $itemEl->getElementsByTagName('guid')->item(0)->nodeValue;
        $date = $itemEl->getElementsByTagName('pubDate')->item(0)->nodeValue;
        $items[] = array('title'=>$title, 'desc'=>$desc, 'link'=>$link, 'guid'=>$guid, 'date'=>$date);
    }
    return $items;
}

/**
 * returns the URL of the blog the RSS feed comes from
 * @return blog URL, or false if doc is invalid
 **/
function get_news_location() {
    if ($this->doc) {
        $link = $this->doc->getElementsByTagName('link')->item(0)->nodeValue;
        return $link;
    }
    else {
        return false;
    }
}

/**
 * returns the title of the blog the RSS feed comes from
 * @return the blog title, or false if doc is invalid
 **/
function get_news_title() {
    if ($this->doc) {
        $title = $this->doc->getElementsByTagName('title')->item(0)->nodeValue;
        return $title;
    }
    else {
        return false;
    }
}

}

?>