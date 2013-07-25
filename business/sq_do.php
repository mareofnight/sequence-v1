<?php

require_once(realpath(__dir__).'/../data/sq_get.php');
require_once(realpath(__dir__).'/../data/sq_set.php');
require_once(realpath(__dir__).'/../data/sq_rss.php');
require_once(realpath(__dir__).'/../business/sq_utils.php');

class sq_do {


/**
 * Get the most recent news items from the RSS feed (indicated in the settings)
 * @param $num number of news items to get
 * @return two dimensional array of news items, or false if news items are not available
 **/
public function get_news($num = 10) {
    $path = self::get_setting('news_rss_url');
    $feed = new sq_rss($path);
    $newsInputs = $feed->get_news($num);
    if ($newsInputs != false) {
        $news = array();
        foreach ($newsInputs as $newsInput) {
            foreach ($newsInput as $key=>$attr) {
                if (strcmp($key, 'title')==0 || strcmp($key, 'desc')==0) {
                    $html = true;
                }else { $html = false; }
                $newsInput[$key] = sq_utils::sanitize_out($attr, $html);
            }
            $news[] = $newsInput;
        }
        return $news;
    }
    else {
        return false;
    }
}

/**
 * get the blog that the news comes from
 * @return array with the blog title as the first element and the blog URL as the second, or false if either is unavailable
 **/
public function get_news_source() {
    $path = self::get_setting('news_rss_url');
    $feed = new sq_rss($path);
    $title = sq_utils::sanitize_out($feed->get_news_title());
    $url = sq_utils::sanitize_out($feed->get_news_location());
    if ($title == false || $url == false) {
        return false;
    }
    return array('title'=>$title, 'url'=>$url);
}



/**
 * check whether a given basic page ID exists
 * @param $id of the basic page
 * @return true if the basic page exists, false if it does not
 **/
public static function basicpage_exists($id) {
    $results = sq_get::basicpage_exists($id);
    if ($results[0][0]['exists'] == 1) {
        return true;
    }
    else { return false; }
}

/**
 * check whether a given basic page slug exists
 * @param $slug slug of the basic page
 * @return true if the basic page exists, false if it does not
 **/
public static function basicpage_exists_slug($slug) {
    $results = sq_get::basicpage_exists_slug($slug);
    if ($results[0][0]['exists'] == 1) {
        return true;
    }
    else { return false; }
}


/**
 * get all data for a basic page, using its id
 * @param $id id of the basic page
 * @param $published false to include all pages, true to include only published pages
 * @return array of all the basic page's attributes, with attribute names as keys
 **/
public static function get_basicpage($id, $published = true) {
    
    // still need to deal with slugs!!!
    
    if ($published == false && self::check_permission('view_drafts') == false) {// don't let unauthorized users see unpublished content
        $published == true;
    }
    $results = sq_get::basicpage($id, $published);
    $result = $results[0][0];
    if (ctype_digit($result['publish'])) {
        $result['publish'] = sq_utils::from_unixtime($result['publish']);
    }
    return $result;
}

/**
 * get all data for a basic page, using its slug
 * @param $slug slug of the basic page
 * @param $published false to include all pages, true to include only published pages
 * @return array of all the basic page's attributes, with attribute names as keys
 **/
public static function get_basicpage_by_slug($slug, $published = true) {
    
    // still need to deal with slugs!!!
    
    if ($published == false && self::check_permission('view_drafts') == false) {// don't let unauthorized users see unpublished content
        $published == true;
    }
    $results = sq_get::basicpage_by_slug($slug, $published);
    $result = $results[0][0];
    if (ctype_digit($result['publish'])) {
        $result['publish'] = sq_utils::from_unixtime($result['publish']);
    }
    return $result;
}

/**
 * update all basic page's attributes
 * @param $id the id of the basic page
 * @param $values the new values of the basic page's attributes
 * @return an error message if the basic page fails to save, otherwise true
 **/
static function edit_basicpage($id, $values) {
    
    // convert timezone
    if (!ctype_digit($values['publish'])) {
        $values['publish'] = sq_utils::to_unixtime($values['publish']);
    }
    
    // deal with null dates
    if (!sq_utils::exists($values['publish']) || strlen($values['publish']) == 0 ||
        strcmp($values['publish'], '0') == 0 || strcmp($values['publish'], '0000-00-00 00:00') == 0) {
        $values['publish'] = 0;
    }
    
    if (self::check_permission('edit_basicpage')) {
        if (strcmp($values['oldslug'], $values['slug']) != 0) {
            sq_set::insert_slug($values['slug']);
            $success = sq_set::basicpage($id, $values);
            sq_set::delete_slug($values['oldslug']);
        }
        else {
            $success = sq_set::basicpage($id, $values);
        }
    }
    if ($success[0] == 0) {
        return true;
    }
    else {// return error if not successful
        return 'Basic page failed to save - please try again';
    }
}


/**
 * get the ids, titles, pagenums (and in a later version thumbnails and iscover) of all basic pages
 * @param $published false to include all pages, true to include only published pages
 * @return two dimensional array of all the basic pages' titles and slugs, with their ids as keys
 **/
public static function get_basicpages($published = true) {
    if ($published == false && self::check_permission('view_drafts') == false) {// don't let unauthorized users see unpublished content
        $published == true;
    }
    $results = sq_get::basicpages($published);
    $result = array();
    
    // build the array with correct keys
    foreach ($results[0] as $record) {
        $result[$record['id']] = $record;
    }
    
    return $result;
}

/**
 * Create a new, blank basic page
 * @return slug of the basic page
 **/
static function new_basicpage($title) {
    if (!self::check_permission('edit_basicpage')) { return false; }
    $title = trim(substr($title, 0, 255));
    $short = substr($title, 0, 21);
    if (strrpos($short, ' ') > 4 && strlen($short) > 4) {
        $short = substr($short, 0, strrpos($short, ' '));
    }
    $short = trim(substr($short, 0, 20));
    $slug = sq_utils::sanitize_slug($short);
    
    // make sure the slug isn't taken, change it if it is
    if (self::check_slug($slug) != false) {
        $counter = 0;
        $slug = substr($slug, 0, 17);
        do {
            if ($counter < 1000) {
                $newslug = $slug.strval($counter);
                $counter = $counter + 1;
            }
            else {
                $newslug = rand(0, 99999999999999999999);
            }
        } while (self::check_slug($newslug) != false);
        $slug = sq_utils::sanitize_slug($newslug);
    }
    $id = sq_set::new_basicpage($slug, $title, $short, sq_utils::sanitize_basic($_SESSION['userid']));
    if (isset($id) && ctype_digit($id) && $id > 0) {
        return $id;
    }
    else {
        return false;
    }
}


public static function get_basicpage_slug_by_id($id) {
    $results = sq_get::basicpage_slug_by_id($id);
    $result = $results[0][0]['slug'];
    return $result;
}

public static function get_storypage_slug_pagenum_by_id($id) {
    $results = sq_get::storypage_slug_pagenum_by_id($id);
    $result = $results[0][0];
    return $result;
}



/**
 * Get the story page updates (based on settings and the most recent or latest-in-sequence story pages)
 * @return two dimensional array of the story title, story page title, slug and page number of each of the most recent story pages
 **/
public static function get_update() {
    $results = sq_get::update();
    return $results[0];
}


/**
 * Get the storyid, slug and title of the story that a story page belongs to
 * @param $id story page ID
 * @return storyid, slug and title of the story that a story page belongs to
 **/
static function get_story_by_pageid($id) {
    $results = sq_get::story_by_pageid($id);
    $result = $results[0][0];
    return $result;
}


public static function content_published($id) {
    $result = sq_get::content_published($id);
    if ($result[0][0]['published'] == 0) {
        return false;
    }
    else {
        return true;
    }
}


/**
 * check whether a given storypage ID exists
 * @param $id id of the story page
 * @return true if the story page exists, false if it does not
 **/
public static function storypage_exists($id) {
    $results = sq_get::storypage_exists($id);
    if ($results[0][0]['exists'] == 1) {
        return true;
    }
    else { return false; }
}

/**
 * get all data for a story page
 * @param $id id of the story page
 * @param $published false to include all pages, true to include only published pages
 * @return array of all the story page's attributes, with attribute names as keys
 **/
public static function get_storypage($id, $published = true) {
    if ($published == false && self::check_permission('view_drafts') == false) {// don't let unauthorized users see unpublished content
        $published == true;
    }
    $results = sq_get::storypage($id, $published);
    $result = $results[0][0];
    if (ctype_digit($result['publish'])) {
        $result['publish'] = sq_utils::from_unixtime($result['publish']);
    }
    return $result;
}

/**
 * get all data for a story page, based on its story's slug and its page number
 * @param $slug slug of the story the story page belongs to
 * @param $pagenum the page number of the story page
 * @param $published false to include all pages, true to include only published pages
 * @return array of all the story page's attributes, with attribute names as keys
 **/
public static function get_storypage_by_pagenum($slug, $pagenum, $published = true) {
    if ($published == false && self::check_permission('view_drafts') == false) {// don't let unauthorized users see unpublished content
        $published == true;
    }
    $results = sq_get::storypage_by_pagenum($slug, $pagenum, $published);
    $result = $results[0][0];
    return $result;
}

/**
 * get the page numbers of the the first, previous, next and last pages, as needed (based on story settings)
 * @param $slug slug of the story the story page belongs to
 * @param $pagenum the page number of the story page
 * @return associative array of page numbers
 **/
public static function get_storypage_links($slug, $pagenum) {
    $results = sq_get::storypage_links($slug, $pagenum);
    return $results;
}


/**
 * get the ids, titles, pagenums (and in a later version thumbnails and iscover) of all story pages of a story, in order of page number
 * @param $slug the slug of the story
 * @param $published false to include all pages, true to include only published pages
 * @return two dimensional array of all the story's page's titles, pagenumbers (relative numbering since cover), thumbnails, and iscover, with their ids as keys
 **/
public static function get_storypages($slug, $published = true) {
    if ($published == false && self::check_permission('view_drafts') == false) {// don't let unauthorized users see unpublished content
        $published == true;
    }
    $results = sq_get::storypages($slug, $published);
    $result = array();
    
    // build the array with correct keys
    foreach ($results[0] as $record) {
        $result[$record['id']] = $record;
    }
    
    return $result;
}


/**
 * update all story page's attributes
 * @param $id the id of the story page
 * @param $values the new values of the story page's attributes
 * @return an error message if the story page fails to save, otherwise true
 **/
static function edit_storypage($id, $values) {
    
    // convert timezone
    if (!ctype_digit($values['publish'])) {
        $values['publish'] = sq_utils::to_unixtime($values['publish']);
    }
    
    // deal with null dates
    if (!sq_utils::exists($values['publish']) || strlen($values['publish']) == 0 ||
        strcmp($values['publish'], '0') == 0 || strcmp($values['publish'], '0000-00-00 00:00') == 0) {
        $values['publish'] = 0;
    }
    
    if (self::check_permission('edit_storypage')) {
        $success = sq_set::storypage($id, $values);
    }
    if ($success[0] == 0) {
        return true;
    }
    else {// return error if not successful
        return 'Story page failed to save - please try again';
    }
}


/**
 * Create a new Story Page (and associated Content record) and return its id
 * @param $slug the slug of the story to add the storypage to
 * @return $id the ID of the new story page
 **/
static function new_storypage($slug) {
    if (self::check_permission('edit_storypage')) {
        $userid = sq_utils::sanitize_basic($_SESSION['userid']);
        $results = sq_set::insert_storypage($slug, $userid);
    }
    if (isset($results[0][0]['id'])) {
        $id = $results[0][0]['id'];
        return $id;
    }
    else {
        return false;
    }
}


/**
 * get the number of pages in a story, given its slug
 * @param $slug the slug of the story
 * @param $published false to include all pages, true to include only published pages
 * @return the number of pages in the story
 **/
public static function num_storypages($slug, $published = false) {
    if ($published == false && self::check_permission('view_drafts') == false) {// don't let unauthorized users see unpublished content
        $published == true;
    }
    $results = sq_get::num_storypages($slug, $published);
    return $results[0][0]['num'];
}


/**
 * get the title of a story based on its slug
 * @param $slug the slug of the story
 * @return the story title, or false if the story's title could not be found
 **/
static function story_title($slug) {
    $results = sq_get::story_title($slug);
    if (isset($results[0][0]['title'])) {
        $title = $results[0][0]['title'];
        $title = sq_utils::sanitize_out($title);
        return $title;
    }
    else {
        return false;
    }
}

/**
 * update all story attributes, and the slug if necessary
 * @param $slug the story's slug before any editing
 * @param $values the new values of the story's attributes
 * @return nothing if save successful, otherwise an error message
 **/
static function edit_story($slug, $values) {
    if (self::check_permission('edit_story')) {
        if (strcmp($slug, $values['slug']) != 0) {
            $redirect = true;
            sq_set::insert_slug($values['slug']);
            sq_set::story($slug, $values);
            sq_set::delete_slug($slug);
        }
        else {
            $redirect = false;
            sq_set::story($slug, $values);
        }
        
        // check whether all fields on the form match the database
        $current = self::get_story($values['slug']);
        $success = true;
        foreach ($current as $key=>$value) {
            if ($success && strcmp($value, $values[$key]) != 0) {
                $success == false;
            }
        }
    }
    else {
        return 'You do not have permission to edit stories';
    }
    
    if ($success) {// refresh the page if successful
        if ($redirect) {
            header('Location: '.sq_utils::get_uri().'/admin/story/'.$values['slug'].'/edit/true');
        }
    }
    else {
        return 'Story failed to save - please try again';
    }
}

/**
 * Create a new, blank story
 * @param $title title of the story
 * @return slug of the story
 **/
static function new_story($title) {
    if (!self::check_permission('edit_storypage')) {
        return false;
    }
    
    // set title, short title, slug
    $title = trim(substr($title, 0, 255));
    $short = substr($title, 0, 21);
    if (strrpos($short, ' ') > 4 && strlen($short) > 4) {
        $short = substr($short, 0, strrpos($short, ' '));
    }
    $short = trim(substr($short, 0, 20));
    $slug = sq_utils::sanitize_slug($short);
    
    // make sure the slug isn't taken, change it if it is
    if (self::check_slug($slug) != false) {
        $counter = 0;
        $slug = substr($slug, 0, 17);
        do {
            if ($counter < 1000) {
                $newslug = $slug.strval($counter);
                $counter = $counter + 1;
            }
            else {
                $newslug = rand(0, 99999999999999999999);
            }
        } while (self::check_slug($newslug) != false);
        $slug = sq_utils::sanitize_slug($newslug);
    }
    
    $results = sq_set::new_story($slug, $title, $short);
    if (isset($results[0])) {
        return $slug;
    }
    else {
        return false;
    }
}

/**
 * Check whether a slug exists / is taken
 * @param $slug the slug to check for
 * @return false if the slug does not already exist in the database, or the slug's string if the slug does exist
 **/
static function check_slug($slug) {
    $results = sq_get::check_slug($slug);
    if (isset($results[0]) && count($results[0]) > 0) { return $results[0][0]; }
    else { return false; }
}

/**
 * get the titles and slugs of all stories as an array
 * @return array of all stories with slugs as keys and titles as values
 **/
static function get_stories() {
    $results = sq_get::stories();
    $result = $results[0];
    $stories = array();
    foreach ($result as $story) {
        $stories[$story['slug']] = $story['title'];
    }
    return $stories;
}

/**
 * get all data about a story, given its slug
 * @param $slug the slug of the story to get
 * @return array of all data about the story
 **/
static function get_story($slug) {
    $results = sq_get::story($slug);
    $result = $results[0][0];
    return $result;
}

/**
 * get the website settings as an array
 * @return array of all site settings
 **/
static function get_settings() {
    $results = sq_get::settings();
    $result = array();
    foreach ($results[0] as $record) {
        /*if (!isset($record['value']) || $record['value'] == null || $record['value'] == false) {
            $result[$record['name']] = false;
        }
        else if (strcmp($record['value'], 'true') == 0) {
            $result[$record['name']] = true;
        }
        else {*/ $result[$record['name']] = $record['value']; //}
    }
    return $result;
}
/**
 * get a single website setting
 * @param $name the name of the setting
 * @return the value of the setting (null if setting is not found)
 **/
static function get_setting($name) {
    $results = sq_get::setting($name);
    $value = $results[0][0]['value'];
    
    /*if (!isset($value) || $value == null || $value == false) {
        $value = false;
    }
    else if (strcmp($value, '1') == 0) {
        $value = true;
    }*/
    return $value;
}

/**
 * Delete a basic page, given its id
 * @param $id id of the basic page to delete
 * @return true if deleted, otherwise false
 **/
static function delete_basicpage($id) {
    if (self::check_permission('edit_basicpage')) {
        $results = sq_set::delete_basicpage($id);
        $result = $results[0];
        if ($result == true || $result == 1) {
            return true;
        }
        else {
            return false;
        }
    }
    else {
        return false;
    }
}

/**
 * Delete a story page, given its id
 * @param $id id of the story page to delete
 * @return true if deleted, otherwise false
 **/
static function delete_storypage($id) {
    if (self::check_permission('edit_storypage')) {
        $results = sq_set::delete_storypage($id);
        $result = $results[0];
        if ($result == true || $result == 1) {
            return true;
        }
        else {
            return false;
        }
    }
    else {
        return false;
    }
}

/**
 * Delete a story (only if it has no pages - this does not cascade!), given its slug
 * @param $slug slug of the story to delete
 * @return true if deleted, otherwise false
 **/
static function delete_story($slug) {
    if (!self::check_permission('edit_story')) {
        return 'You do not have permission to edit and delete stories';
    }
    else if (self::num_storypages($slug) > 0) {
        return 'This story still has story pages - you must delete all of a story'."'".'s story pages before deleting the story';
    }
    else {
        $results = sq_set::delete_story($slug);
        $result = $results[0];
        if ($result == true || $result == 1) {
            return true;
        }
        else {
            return false;
        }
    }
}
 


/**
 * save the website settings
 * @param $values an array with setting names as keys and setting values as values
 **/
static function edit_settings($values) {
    if (self::check_permission('edit_settings')) {
        // build a two dimensional array of all the settings
        //      setting names as keys of the first level
        //      numeric keys for the second level, with value first and name second
        $settings = array();
        foreach ($values as $name=>$value) {
            $settings[] = array($value, $name);
        }
        sq_set::settings($settings);
    }
}

/**
 * get user info for all users
 * @return user info for all users
 **/
static function get_all_users() {
    if (self::check_permission('manage_users')) {
        $results = sq_get::all_users();
        return $results[0];
    }
    else {
        return false;
    }
}


/**
 * get user info for a user by their userid
 * @param $userid the user's userid
 * @return user's own userid, username, email, role and URL, or false if the user does not exist
 **/
static function get_user($userid) {
    $results = sq_get::user($userid);
    $result = $results[0][0];
    if (count($result) == 0 || !isset ($result)) {
        return false;
    }
    else {
        return $result;
    }
}

static function new_user($username) {
    $result = sq_set::insert_user($username);
    $result = $result[0];
    if ($result == 1) {
        return self::check_username($username);
    }
    else {
        return false;
    }
}


/**
 * check if a user with a given username exists
 * @param $username username to check
 * @return userid if a user with the username exists, false if not
 **/
static function check_username($username) {
    $results = sq_get::check_username($username);
    $result = $results[0][0]['userid'];
    if (count($result) == 0 || !isset ($result)) {
        return $result;
    }
    else {
        return $result;
    }
}


/**
 * get user info for the currently logged in user
 * @return user's own userid, username, email, role and URL
 **/
static function get_self() {
    return self::get_user($_SESSION['userid']);
}

/**
 * save account info (not including role) for the current user
 * @param $values new account info (must also include the user's old/current password)
 * @return true if success, false if failure
 **/
static function set_self($values) {
    if (!self::check_permission('edit_own_account')) { return false; }
    
    $userid = $_SESSION['userid'];
    if (self::check_password($values['current_password'], $userid) == false) {
        return false;
    }
    $results = sq_set::user($userid, $values);
    if ($results[0] == 1) {
        return true;
    }
    else {
        return false;
    }
}

/**
 * save account info (not including role) for a user, given their userid
 * @param $userid userid of the user whose account info to save
 * @param $values new account info (must also include the user's old/current password)
 * @return true if success, false if failure
 **/
static function set_user($userid, $values) {
    if (!self::check_permission('manage_users')) { return false; }
    
    // don't let users change their own role
    if ($userid == $_SESSION['userid']) {
        unset($values['role']);
    }
    
    $results = sq_set::user($userid, $values);
    if ($results[0] == 1) {
        return true;
    }
    else {
        return false;
    }
}

/**
 * save a user's password
 * @param $password the user's new password
 * @param $userid the user's userid (default to the current user if password is not set)
 * @return true if save succeeded, otherwise false
 **/
static function set_password($password, $userid = null) {
    if (!isset($userid)) {
        $userid = $_SESSION['userid'];
    }
    if ((intval($userid) == intval($_SESSION['userid']) && self::check_permission('edit_own_account'))
            || self::check_permission('manage_users')) {
        $results = sq_set::password($userid, $password);
        if ($results[0] == 1) {
            return true;
        }
    }
    return false;
}


/**
 * check whether the user with a given userid has a given permission
 * @param $permission the name of the permission
 * @param $userid the userid of the user
 * @return true if the user has the permission, otherwise false
 **/
static function check_permission($permission, $userid=null) {
    if (!isset($userid)) {
        $userid = $_SESSION['userid'];
    }
    $results = sq_get::permission($permission, $userid);
    if (isset($results[0]) && count($results[0]) > 0) { return true; }
    else { return false; }
}

/**
 * check a user password against the database, given the userid
 * @param $userid userid of the user to check the password of
 * @param $password the password to check
 * @return true if the password is true, otherwise false
 **/
static function check_password($password, $userid =  null) {
    if (!isset($userid)) {
        $userid = $_SESSION['userid'];
    }
    $results = sq_get::password($userid);
    $dbpassword = $results[0][0]['password'];
    if (strcmp(sq_utils::hash($password), $dbpassword) == 0) {
        return true;
    }
    else {
        return false;
    }
}

static function login($username, $password) {
    $results = sq_get::login($username);
    $result = $results[0][0];
    $errors = array();
    if (count($result) == 0) {
        $errors['username'] = 'Incorrect username - this user does not exist';
    }
    else {
        if (strcmp(sq_utils::hash($password), $result['password']) != 0) {
            $errors['password'] = 'Incorrect password';
        }
        else {
            // login the user
            $_SESSION['userid'] = $result['userid'];
            $_SESSION['username'] = $username;
            
        }
    }
    if (count($errors) > 0) {
        return $errors;
    }
    else {
        header('Location: '.sq_utils::get_uri().'/admin');// redirect to the admin homepage
    }
}

/**
 * Log the user out
 **/
static function logout() {
    session_unset();
    session_destroy();
    session_start();
    header('Location: '.sq_utils::get_uri().'/login');// redirect to the login page
}

/**
 * Send account information for a lost email account
 * @param $email email address to send account info to
 * @return nothing if successful, error message if failed to send
 **/
static function email_lost_account($email) {
    $subject = 'Account retrieval';
    $message = '';
    
    $message .= 'You are recieving this message because asked to retrieve a lost username or password, and provided your email address.';
    $message .= "\n".'These are your Sequence user accounts:';
    
    $users = sq_get::users_by_email($email);
    $users = $users[0];
    if (count($users) == 0) { return 'There are no users with this email address.'; }
    foreach ($users as $user) {
        $password = sq_utils::gen_password();
        sq_set::password($user['userid'], sq_utils::hash($password));
        $message .= "\n\n".'Username: '.$user['username'];
        $message .= "\n".'Password: '.$password;
    }
    
    $message .= "\n\n".'To log in, go to '.sq_utils::get_uri().'/login and type in your username and new password.';
    $message .= ' You can reset your password after logging in, by clicking on your username and editing your account information.';
    $message .= ' You will need your new, generated password to do this.';
    
    // send email
    $success = sq_utils::send_mail($email, $subject, $message);
    if ($succcess == false) {
        return 'Message may have failed to send';
    }
    else {
        return null;
    }
    
}

}
?>