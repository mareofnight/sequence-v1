<?php

require_once(realpath(__dir__).'/../business/sq_utils.php');
require_once(realpath(__dir__).'/../business/sq_do.php');

class sq_public_layout_writer {

/**
 * Write all HTML that goes before the main content of an public area page
 * @param $title title of the current page
 * @return all HTML before an public area page's main content
 **/
static function above($title) {
    $out = '';
    $out .= self::head($title);
    $out .= self::header();
    $out .= self::nav();
    $out .= self::start();
    return $out;
}

/**
 * Write all HTML that goes after the main content of an public area page
 * @return all HTML after an public page's main content
 **/
static function below() {
    $out = '';
    $out .= self::end();
    $out .= self::footer();
    $out .= self::foot();
    return $out;
}

/**
 * Write the HTML doctype and head of an public area page
 * @param $title title of the current page
 * @return HTML doctype and head for an public area page
 **/
static function head($title) {
    $uri = sq_utils::get_uri();
    $site_title = sq_do::get_setting('site_title');
return '<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<title>'.$site_title.' | '.$title.'</title>
	<link type="text/css" rel="stylesheet" href="'.$uri.'/presentation/reset.css" />
    <link type="text/css" rel="stylesheet" href="'.$uri.'/presentation/layout.css" />
	<link type="text/css" rel="stylesheet" href="'.$uri.'/presentation/style.css" />
</head>
<body>

<div class="everything">'."\n\n\n";
}

/**
 * Write the HTML for the header of an public area page
 * @return HTML for the header of an public area page
 **/
static function header() {
    $site_title = sq_do::get_setting('site_title');
    return '<header class="header" >
    <h1><a href="'.sq_utils::get_uri().'">'.sq_utils::sanitize_out($site_title).'</a></h1>
</header>'."\n";
}

/**
 * Write the HTML for the navigation links of an public area page
 * @return HTML for the navigation links of an public area page
 **/
static function nav() {
    $uri = sq_utils::get_uri();
    $out = '';
    
    $out .= "\n".'<nav class="navbar">';
    $out .= "\n\t".'<ul>';
    
    // write the links
    $out .= "\n\t\t".'<li><a href="'.$uri.'">Home</a></li>';
    if (strlen(sq_do::get_setting('news_rss_url')) > 0) {
    	$out .= "\n\t\t".'<li><a href="'.$uri.'/news">News</a></li>';
    }
    $stories = sq_do::get_stories();
    foreach ($stories as $slug=>$title) {
        $out .= "\n\t\t".'<li><a href="'.$uri.'/'.$slug.'">'.$title.'</a></li>';
    }
	$basicpages = sq_do::get_basicpages();
	foreach ($basicpages as $page) {
		$out .= "\n\t\t".'<li><a href="'.$uri.'/'.$page['slug'].'">'.$page['short_title'].'</a></li>';
	}
    
    $out .= "\n\t".'</ul>';
    $out .= "\n".'</nav>'."\n\n";
    return $out;
}

/**
 * Write the HTML for the content start tag of an public area page
 * @return HTML for the content start tag of an public area page
 **/
static function start() {
    return '<div class="content" >'."\n\n";
}

/**
 * Write the HTML for the content end tag of an public area page
 * @return HTML for the content end tag of an public area page
 **/
static function end() {
    return '</div>'."\n\n";
}

/**
 * Write the HTML for the footer of an public area page
 * @return HTML for the footer of an public area page
 **/
static function footer() {
    return '<footer class="footer">'/*.Footer text goes here*/.'</footer>'."\n\n";
}

/**
 * Write the HTML for the last closing tags of an public area page
 * @return HTML for the last closing tags of an public area page
 **/
static function foot() {
    return '</div>'."\n".'</body>'."\n".'</html>';
}


}

?>