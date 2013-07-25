<?php
require_once(realpath(__dir__).'/../business/sq_utils.php');
require_once(realpath(__dir__).'/../business/sq_do.php');

class sq_admin_layout_writer {

/**
 * Write all HTML that goes before the main content of an admin area page
 * @param $title title of the current page
 * @param $nav whether to include navigation links (default true)
 * @return all HTML before an admin area page's main content
 **/
static function above($title, $nav = true) {
    $out = '';
    $out .= self::head($title);
    $out .= self::header();
	if ($nav) {
		$out .= self::nav();
	}
    $out .= self::start();
    return $out;
}

/**
 * Write all HTML that goes after the main content of an admin area page
 * @return all HTML after an admin page's main content
 **/
static function below() {
    $out = '';
    $out .= self::end();
    $out .= self::footer();
    $out .= self::foot();
    return $out;
}

/**
 * Write the HTML doctype and head of an admin area page
 * @param $title title of the current page
 * @return HTML doctype and head for an admin area page
 **/
static function head($title) {
    $uri = sq_utils::get_uri();
return '<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<title>Sequence - '.$title.'</title>
	<link href="http://fonts.googleapis.com/css?family=Droid+Serif:400,700,400italic,700italic" rel="stylesheet" type="text/css">
    <link type="text/css" rel="stylesheet" href="'.$uri.'/admin/reset.css" />
    <link type="text/css" rel="stylesheet" href="'.$uri.'/admin/layout.css" />
	<link type="text/css" rel="stylesheet" href="'.$uri.'/admin/style.css" />
	<script src="'.$uri.'/ckeditor/ckeditor.js"></script>
</head>
<body>
<div class="everything">'."\n\n";
}

/**
 * Write the HTML for the header of an admin area page
 * @return HTML for the header of an admin area page
 **/
static function header() {
    return '<header class="header" >
    <h1><a href="'.sq_utils::get_uri().'/admin"><span class="logo-image">&nbsp;<span class="logo-letter">S</span></span><span class="logo-text">equence</span></a></h1>
</header>'."\n\n";
}

/**
 * Write the HTML for the navigation links of an admin area page
 * @return HTML for the navigation links of an admin area page
 **/
static function nav() {
    $uri = sq_utils::get_uri();
    $out = '';
    $out .='<nav class="login">
    <ul>';
	if (array_key_exists('username', $_SESSION) && isset($_SESSION['username'])) {$out .= "\n\t\t".'<li><a href="'.$uri.'" target="_blank">View Public Site</a></li>';
		$out .= "\n\t\t".'<li><a href="'.$uri.'/admin/myaccount'.'">'.sq_utils::sanitize_out($_SESSION['username']).'</a></li>';
		$out .= "\n\t\t".'<li><a href="'.$uri.'/admin/logout'.'" class="special">logout</a></li>';
		
	}
	else {
		$out .= "\n\t\t".'<li><a href="'.$uri.'/admin/login'.'">login</a></li>';
	}
	$out .= "\n\t".'</ul>
</nav>';
    $out .= "\n\n";

    $out .= '<nav class="navbar">
    <ul>
        <li><a href="'.$uri.'/admin/settings'.'">Settings</a></li>
        <li><a href="'.$uri.'/admin/users'.'">Users</a></li>
        <li><a href="'.$uri.'/admin/basicpage'.'">Basic Pages</a></li>
    </ul>
    <ul>'."\n";
    // get story titles and slugs
    $stories = sq_do::get_stories();
    foreach ($stories as $slug=>$title) {
        $out .= "\t".'<li><a href="'.$uri.'/admin/story/'.$slug.'">'.$title.'</a></li>'."\n";
    }
    $out .= "\t".'<li><a href="'.$uri.'/admin/story/new" class="special">Create Story</a></li>
    </ul>
</nav>'."\n\n";
    return $out;
}

/**
 * Write the HTML for the content start tag of an admin area page
 * @return HTML for the content start tag of an admin area page
 **/
static function start() {
    return "\n".'<div class="content" >'."\n\n";
}

/**
 * Write the HTML for the content end tag of an admin area page
 * @return HTML for the content end tag of an admin area page
 **/
static function end() {
    return "\n".'</div>'."\n\n";
}

/**
 * Write the HTML for the footer of an admin area page
 * @return HTML for the footer of an admin area page
 **/
static function footer() {
    return ''."\n\n";
}

/**
 * Write the HTML for the last closing tags of an admin area page
 * @return HTML for the last closing tags of an admin area page
 **/
static function foot() {
    return '</div>'."\n".'</body>'."\n".'</html>';
}


}

?>