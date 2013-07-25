<?php
require_once(realpath(__dir__).'/sq_admin_layout_writer.php');
require_once(realpath(__dir__).'/sq_admin_form_writer.php');
require_once(realpath(__dir__).'/../business/sq_do.php');

class sq_admin_writer {

static function index() {
    $uri = sq_utils::get_uri();
    $out = '';
    $out .= sq_admin_layout_writer::above('Dashboard');
    
    // links to all the stories
    $stories = sq_do::get_stories();
    $out .= "\n".'<h3>Stories</h3>';
    $out .= "\n".'<div class="dashboard">';
    foreach ($stories as $slug=>$title) {
        $out .= "\n\t".'<h4><a href="'.$uri.'/admin/story/'.$slug.'">'.sq_utils::sanitize_out($title).'</a></h4>';
    }
    $out .= "\n\t".'<h4><a href="'.$uri.'/admin/story/new" class="special">Create Story</a></h4>';
    $out .= "\n".'</div>';
    
    // links for other things
    $out .= "\n".'<h3>Site</h3>';
    $out .= "\n".'<div class="dashboard">';
    $out .= "\n\t".'<h4><a href="'.$uri.'/admin/settings'.'">Settings</a></h4>';
    $out .= "\n".'</div>';
    
    $out .= sq_admin_layout_writer::below();
    return $out;
}

/**
 * Write HTML for a form to make a new basic page
 * @return HTML for a form to make a new basic page
 **/
static function new_basicpage() {
    $out = '';
    $out .= sq_admin_layout_writer::above('New Basic Page');
    $out .= '<h2>New Basic Page</h2>'."\n\n";
    $out .= sq_admin_form_writer::new_basicpage();
    $out .= sq_admin_layout_writer::below();
    return $out;
}

/**
 * Generate HTML for a page to edit a basic page
 * @param $id content id of the basic page to edit
 * @return HTML for the web page
 **/
static function edit_basicpage($id) {
    $out = '';
    $out .= sq_admin_layout_writer::above('Edit Basic Page');
    $out .= '<h2>Edit Basic Page</h2>'."\n\n";
    $out .= "\n".'<a href="'.sq_utils::get_uri().'/'.sq_do::get_basicpage_slug_by_id($id).'" target="blank" class="previewlink">View Basic Page</a>';
    $out .= sq_admin_form_writer::edit_basicpage($id);
    $out .= sq_admin_layout_writer::below();
    return $out;
}

/**
 * Generate HTML for a page that lists all the basic pages in the basic
 * @return HTML for the page listing all the basic pages
 **/
static function list_basicpages() {
    // beginning stuff
    $out = '';
    $out .= sq_admin_layout_writer::above('Basic Pages');
    $out .= '<h2>Edit Basic Pages</h2>'."\n\n";
    
    // list of basic pages
    $pages = sq_do::get_basicpages(false);
    $url = sq_utils::get_uri();
    $out .= "\n\t".'<a href="'.sq_utils::get_uri().'/admin/basicpage/new" class="special">New Page</a>';
    $out .= "\n\t".'<div class="pagelist">';
    foreach ($pages as $id=>$page) {
        $out .= "\n\t\t".'<div><a href="'.$url.'/admin/basicpage/'.$id.'">'
            .'<span class="title">'.sq_utils::sanitize_out($page['title']).'</span></a></div>';
    }
    $out .= "\n\t".'</div>';
    
    // ending stuff
    $out .= sq_admin_layout_writer::below();
    return $out;
}

/**
 * Generate HTML for a page to edit a story page
 * @param $id content id of the story page to edit
 * @return HTML for the web page
 **/
static function edit_storypage($id) {
    $out = '';
    $story = sq_do::get_story_by_pageid($id);
    $out .= sq_admin_layout_writer::above('Edit Story Page: '.$story['title']);
    $out .= '<h2>'.'Edit Story Page: '.$story['title'].'</h2>'."\n\n";
    $linkinfo = sq_do::get_storypage_slug_pagenum_by_id($id);
    $out .= "\n".'<a href="'.sq_utils::get_uri().'/'.$linkinfo['slug'].'/'.$linkinfo['pagenum'].'" target="blank" class="previewlink">View Story Page</a>';
    $out .= sq_admin_form_writer::edit_storypage($id);
    $out .= sq_admin_layout_writer::below();
    return $out;
}

/**
 * Generate HTML for a page that lists all the story pages in the story
 * @param $slug the slug of the story
 * @return HTML for the page listing all the story pages
 **/
static function list_storypages($slug) {
    // beginning stuff
    $title = sq_do::story_title($slug);
    $out = '';
    $out .= sq_admin_layout_writer::above('Story Dashboard');
    $out .= '<h2>Edit Story Pages: '.sq_utils::sanitize_out($title).'</h2>'."\n\n";
    $out .= "\n".'<a href="'.sq_utils::get_uri().'/'.$slug.'" target="blank" class="previewlink">View Story</a>';
    
    // list of story pages
    $pages = sq_do::get_storypages($slug, false);
    $url = sq_utils::get_uri();
    $out .= "\n\t".'<div class="pagelist">';
    if (count($pages) > 0) {
        foreach ($pages as $id=>$page) {
            $out .= "\n\t\t".'<div><a href="'.$url.'/admin/storypage/'.$id.'">'
                .'<span class="pagenum">'.sq_utils::sanitize_out($page['pagenum']).'</span>'
                .'<span class="title">'.sq_utils::sanitize_out($page['title']).'</span></a></div>';
        }
    }
    else {
        $out .= "\n\t\t".'<h3>There are no pages in this story yet.</h3>';
    }
    $out .= "\n\t".'</div>';
    
    // ending stuff
    $out .= sq_admin_layout_writer::below();
    return $out;
}

/**
 * Write HTML for a form to make a new story
 * @return HTML for a form to make a new story
 **/
static function new_story() {
    $out = '';
    $out .= sq_admin_layout_writer::above('New Story');
    $out .= '<h2>New Story</h2>'."\n\n";
    $out .= sq_admin_form_writer::new_story();
    $out .= sq_admin_layout_writer::below();
    return $out;
}

/**
 * Write HTML for a form to change the story's settings
 * @param $slug the slug of the story
 * @return HTML for the story form
 **/
static function story($slug) {
    $title = sq_do::story_title($slug);
    $out = '';
    $out .= sq_admin_layout_writer::above('Story Dashboard');
    $out .= '<h2>Story: '.sq_utils::sanitize_out($title).'</h2>'."\n\n";
    $out .= "\n".'<a href="'.sq_utils::get_uri().'/'.$slug.'" target="blank" class="previewlink">View Story</a>';
    // lots of links
    $out .= '<div class="dashboard">';
    $out .= "\n\t".'<a href="'.sq_utils::get_uri().'/admin/story/'.$slug.'/new" class="special">New Page</a>';
    $out .= "\n\t".'<a href="'.sq_utils::get_uri().'/admin/story/'.$slug.'/edit">Edit Story</a>';
    //$out .= "\n\t".'<a href="'.sq_utils::get_uri().'/admin/story/'.$slug.'/orderpages">Order Pages</a>';
    $out .= "\n\t".'<a href="'.sq_utils::get_uri().'/admin/story/'.$slug.'/storypage">Edit Pages</a>';
    $out .= "\n".'</div>';
    $out .= sq_admin_layout_writer::below();
    return $out;
}

/**
 * Generate HTML for a page to change a story and its settings (not its story pages)
 * @param $slug the slug of the story
 * @param $success whether the story has been saved successfully (null if not saved, 'true' if success)
 * @return HTML for a page to edit a story and its settings
 **/
static function edit_story($slug, $success = null) {
    $out = '';
    $out .= sq_admin_layout_writer::above('Edit Story');
    $out .= '<h2>Edit Story</h2>'."\n\n";
    $out .= "\n".'<a href="'.sq_utils::get_uri().'/'.$slug.'" target="blank" class="previewlink">View Story</a>';
    $newstory = sq_admin_form_writer::edit_story($slug, $success);
    $out .= $newstory;
    $out .= sq_admin_layout_writer::below();
    return $out;
}

/**
 * Generate HTML for a page to edit site settings
 * @return HTML for the web page
 **/
static function settings() {
    $out = '';
    $out .= sq_admin_layout_writer::above('Settings');
    $out .= '<h2>Site Settings</h2>'."\n\n";
    $out .= "\n".'<a href="'.sq_utils::get_uri().'" target="blank" class="previewlink">View Public Site</a>';
    $out .= sq_admin_form_writer::settings();
    $out .= sq_admin_layout_writer::below();
    return $out;
}

/**
 * Generate HTML for a page to add a new user
 * @return HTML for the web page
 **/
static function new_user() {
    $out = '';
    $out .= sq_admin_layout_writer::above('New User');
    $out .= '<h2>New User</h2>'."\n\n";
    $out .= sq_admin_form_writer::new_user();
    $out .= sq_admin_layout_writer::below();
    return $out;
}

/**
 * Generate HTML for a page to edit a user
 * @param $userid userid of the user
 * @return HTML for the web page
 **/
static function user($userid) {
    $out = '';
    $out .= sq_admin_layout_writer::above('Modify User');
    $out .= '<h2>Modify User</h2>'."\n\n";
    $out .= sq_admin_form_writer::user($userid);
    $out .= sq_admin_layout_writer::below();
    return $out;
}

/**
 * Generate HTML for a page that lists all the users
 * @return HTML for the page listing all the users
 **/
static function list_users() {
    // beginning stuff
    $out = '';
    $out .= sq_admin_layout_writer::above('Users');
    $out .= '<h2>Manage Users</h2>'."\n\n";
    
    // list of users
    if (!sq_do::check_permission('manage_users')) { $out .= sq_admin_form_writer::no_permission(); }
    $users = sq_do::get_all_users();
    $url = sq_utils::get_uri();
    $out .= "\n\t".'<div class="pagelist">';
    $out .= "\n\t\t".'<div><a href="'.$url.'/admin/users/new/" class="special">New User</a></div>';
    foreach ($users as $user) {
        $out .= "\n\t\t".'<div><a href="'.$url.'/admin/users/'.sq_utils::sanitize_out($user['userid']).'">'.
            sq_utils::sanitize_out($user['userid']).': '.sq_utils::sanitize_out($user['username']).'</a></div>';
    }
    $out .= "\n\t".'</div>';
    
    // ending stuff
    $out .= sq_admin_layout_writer::below();
    return $out;
}

/**
 * Generate HTML for a page to edit the user's own account
 * @return HTML for modify own account page
 **/
static function myaccount() {
    $out = '';
    $out .= sq_admin_layout_writer::above('My Account');
    $out .= '<h2>My Account</h2>'."\n\n";
    $out .= sq_admin_form_writer::myaccount();
    $out .= sq_admin_layout_writer::below();
    return $out;
}

/**
 * Write all HTML for the login page
 * @return all HTML for the login page
 **/
static function login() {
    if (isset($_SESSION['userid'])) {
        header('Location: '.sq_utils::get_uri().'/admin');
    }
    $out = '';
    $out .= sq_admin_layout_writer::above('Login', false);
    $out .= '<h2>Login</h2>'."\n\n";
    $out .= sq_admin_form_writer::login()."\n\n";
    $out .= sq_admin_layout_writer::below();
    return $out;
}

static function lost_account() {
    $out = '';
    $out .= sq_admin_layout_writer::above('Retrieve Account', false);
    $out .= '<h2>Retrieve Account</h2>'."\n\n";
    $out .= '<p>Reset your password(s) and retrieve your username(s) by email.</p>'."\n\n";
    $out .= sq_admin_form_writer::lost_account()."\n\n";
    $out .= sq_admin_layout_writer::below();
    return $out;
}

/**
 * Log the user out
 **/
static function logout() {
    sq_do::logout();
}

static function no_permission() {
    $out = '';
    $out .= sq_admin_layout_writer::above('no permission', false);
    $out .= '<h2>You do not have permission to view this page.</h2>'."\n\n";
    $out .= sq_admin_layout_writer::below();
    return $out;
}

static function err404() {
    $out = '';
    $out .= sq_admin_layout_writer::above('404 page not found');
    $out .= '<h2>Page not found.</h2>'."\n\n";
    $out .= sq_admin_layout_writer::below();
    return $out;
}

}
?>