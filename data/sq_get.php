<?php

require_once(realpath(__dir__).'/../data/db.php');

class sq_get {

/**
 * get userids and usernames for all a user's accounts, given their email address
 * @param $email user's email address
 * @return the user's userids and usernames
 **/
static function users_by_email($email) {
    $query = 'SELECT userid, username FROM User WHERE email = ?';
    $params = array(array($email));
    $db = new DB();
    $result = $db->run($query, $params);
    $db->close();
    return $result;
}

/**
 * get password and userid of a user, given their usernam
 * @param $username the user's username
 * @return the user's userid and (hashed) password
 **/
static function login($username) {
    $query = 'SELECT userid, password FROM User WHERE username = ?';
    $params = array(array($username));
    $db = new DB();
    $result = $db->run($query, $params);
    $db->close();
    return $result;
}

/**
 * check if a user with a given username exists
 * @param $username username to check
 * @return array(array('exists'=>1)) if exists, otherwise array(array('exists'=>0))
 **/
static function check_username($username) {
    $query = 'SELECT userid FROM User WHERE username = ?';
    $params = array(array($username));
    $db = new DB();
    $result = $db->run($query, $params);
    $db->close();
    return $result;
}

/**
 * get password of a user, given their userid
 * @param $userid the user's userid
 * @return the user's (hashed) password
 **/
static function password($userid) {
    $query = 'SELECT password FROM User WHERE userid = ?';
    $params = array(array($userid));
    $db = new DB();
    $result = $db->run($query, $params);
    $db->close();
    return $result;
}

/**
 * Check whether a given user has a given permission
 * @param $permission the name of the permission
 * @param $userid the userid of the user
 * @return 1 if the user has the permission, otherwise 0
 **/
static function permission($permission, $userid) {
    $query = 'SELECT Permission.permissionid FROM User RIGHT JOIN Role ON Role.roleid = User.roleid RIGHT JOIN Grant_ ON Role.roleid = Grant_.roleid RIGHT JOIN Permission ON Grant_.permissionid = Permission.permissionid WHERE permission = ? AND userid = ?';
    $params = array(array($permission, $userid));
    $db = new DB();
    $result = $db->run($query, $params);
    $db->close();
    return $result;
}



// get password of a user, given their userid
// SELECT password FROM User WHERE userid = ?;

/**
 * get userid, username, email, role and URL of all users
 **/
// SELECT userid, username, email, role, URL FROM User JOIN Role ON Role.roleid = User.roleid;
//      ^ this query isn't done yet - need to test with a larger number of users.
static function all_users() {
    $query = 'SELECT userid, username, email, role, url FROM User JOIN Role ON User.roleid = Role.roleid';
    $params = array(array());
    $db = new DB();
    $result = $db->run($query, $params);
    $db->close();
    return $result;
}

// do I need one to check if a specific user has a specific permission?


/**
 * get userid, username, email, role and URL of one user, given their userid
 * @param $userid userid of the user to retrieve
 * @return user info, wrapped inside two other arrays
 **/
static function user($userid) {
    $query = 'SELECT userid, username, email, role, url FROM User JOIN Role ON User.roleid = Role.roleid WHERE userid = ?';
    $params = array(array($userid));
    $db = new DB();
    $result = $db->run($query, $params);
    $db->close();
    return $result;
}


/**
 * get all settings
 * @return two dimensonal array of the names and values of all settings
 **/
static function settings() {
    $query = 'SELECT name, value FROM Setting';
    $db = new DB();
    $params = array(array());
    $result = $db->run($query, $params);
    $db->close();
    return $result;
}

/**
 * get a single setting
 * @param $name the name of the setting
 * @return two dimensional array with the value of the seting inside
 **/
static function setting($name) {
    $query = 'SELECT value FROM Setting WHERE name = ?';
    $db = new DB();
    $params = array(array($name));
    $result = $db->run($query, $params);
    $db->close();
    return $result;
}


/**
 * get all slugs with the same name as the input slug (should be a two dimensional array with zero or one slug names inside)
 * @return two dimensional array, containing the slug name on the inner level if the slug name exists
 **/
static function check_slug($slug) {
    $query = 'SELECT slug FROM Slug where slug = ?';
    $db = new DB();
    $params = array(array($slug));
    $result = $db->run($query, $params);
    $db->close();
    return $result;
}

/**
 * get all data for a story (by ID or slug)
 **/


/**
 * get the title of a story based on its slug
 * @param $slug the slug of the story
 * @return a two dimensional array with the story title inside
 **/
static function story_title($slug) {
    $query = 'SELECT title FROM Story WHERE slug = ?';
    $db = new DB();
    $params = array(array($slug));
    $result = $db->run($query, $params);
    $db->close();
    return $result;
}


/**
 * get the short titles and slugs of all stories
 * @return array of all stories with slugs as keys and short titles as values
 **/
static function stories() {
    $query = 'SELECT short_title AS "title", slug FROM Story ORDER BY storyid';
    $db = new DB();
    $params = array(array());
    $result = $db->run($query, $params);
    $db->close();
    return $result;
}

/**
 * get all data about a story, given its slug
 * @param $slug the slug of the story to get
 * @return two dimentional array, with all data about the story in the inner level of the array
 **/
static function story($slug) {
    $query = 'SELECT storyid, slug, short_title, title, synopsis, show_synopsis, show_cover_thumb, show_page_thumb, show_page_title, '
        .'show_page_num, show_prevnext, show_firstlast, show_updates FROM Story WHERE slug = ?';
    $db = new DB();
    $params = array(array($slug));
    $result = $db->run($query, $params);
    $db->close();
    return $result;
}


/**
 * Get the storyid, slug and title of the story that a story page belongs to
 * @param $id story page ID
 * @return storyid, slug and title of the story that a story page belongs to
 **/
static function story_by_pageid($id) {
    $query = 'SELECT storyid, slug, title FROM Story_Page JOIN Story ON Story_Page.story = Story.storyid WHERE id = ?';
    $params = array(array($id));
    $db = new DB();
    $result = $db->run($query, $params);
    $db->close();
    return $result;
}


/**
 * get the number of pages in a story, given its slug
 * @param $slug the slug of the story
 * @param $published false to include all pages, true to include only published pages
 * @return the number of pages in the story
 **/
public static function num_storypages($slug) {
    $query = 'SELECT COUNT(id) AS "num" FROM Story_Page JOIN Story ON Story_Page.story = Story.storyid WHERE Story.slug = ?';
    if ($published) {
        $query .= ' AND publish <= NOW() AND publish != 0';
    }
    $query .= ' ORDER BY pagenum';
    $params = array(array($slug));
    $db = new DB();
    $result = $db->run($query, $params);
    $db->close();
    return $result;
}

public static function content_published($id) {
    $query = 'SELECT COUNT(id) AS "published" FROM Content WHERE id = ? AND publish <= NOW() AND publish != 0';
    $params = array(array($id));
    $db = new DB();
    $result = $db->run($query, $params);
    $db->close();
    return $result;
}


public static function basicpage_slug_by_id($id) {
    $query = 'SELECT slug FROM Content JOIN Basic_Page ON Basic_Page.id = Content.id WHERE Content.id = ?';
    $params = array(array($id));
    $db = new DB();
    $result = $db->run($query, $params);
    $db->close();
    return $result;
}

public static function storypage_slug_pagenum_by_id($id) {
    $query = 'SELECT slug, pagenum FROM Story_Page JOIN Content ON Story_Page.id = Content.id JOIN Story on Story_Page.story = Story.storyid WHERE Content.id = ?';
    $params = array(array($id));
    $db = new DB();
    $result = $db->run($query, $params);
    $db->close();
    return $result;
}


/**
 * get the ids, titles, pagenums (and in a later version thumbnails and iscover) of all story pages of a story, in order of page number
 * @param $slug the slug of the story
 * @param $published false to include all pages, true to include only published pages
 * @return array of all the story's page's ids, titles, pagenumbers (and in a later version thumbnails and iscover)
 **/
public static function storypages($slug, $published) {
    $query = 'SELECT Content.id AS "id", Content.title AS "title", pagenum, iscover, thumbnail '.
                'FROM Story_Page JOIN Content ON Story_Page.id = Content.id '.
                'JOIN Story ON Story_Page.story = Story.storyid '.
                'WHERE Story.slug = ?';
    if ($published) {
        $query .= ' AND publish <= NOW() AND publish != 0';
    }
    $query .= ' ORDER BY pagenum';
    $params = array(array($slug));
    $db = new DB();
    $result = $db->run($query, $params);
    $db->close();
    return $result;
}



/**
 * check whether a given storypage ID exists
 * @param $id id of the story page
 * @return two dimensional array containing 1 if the story page exists, 0 if it does not
 **/
public static function storypage_exists($id) {
    $query = 'SELECT COUNT(id) AS "exists" FROM Story_Page WHERE id = ?';
    $params = array(array($id));
    $db = new DB();
    $result = $db->run($query, $params);
    $db->close();
    return $result;
}



/**
 * get all data for a story page
 * @param $id id of the story page
 * @param $published false to include all pages, true to include only published pages
 * @return two dimensional array, with story page data in the inner array
 **/
public static function storypage($id, $published) {
    $query = 'SELECT Content.id AS "id", title, body, thumbnail, pagenum, iscover, author_comment, UNIX_TIMESTAMP(publish) AS "publish" FROM Story_Page JOIN Content ON Story_Page.id = Content.id WHERE Content.id = ?';
    if ($published) {
        $query .= ' AND publish <= NOW() AND publish != 0';
    }
    $params = array(array($id));
    $db = new DB();
    $db->run('SET time_zone = "+0:00"', array(array()));// set timzeone (for timestamp processing)
    $result = $db->run($query, $params);
    $db->close();
    return $result;
}

/**
 * get all data for a story page (using slug and page number instead of id)
 * @param $slug slug of the story the story page belongs to
 * @param $pagenum the page number of the story page
 * @param $published false to include all pages, true to include only published pages
 * @return two dimensional array, with story page data in the inner array
 **/
public static function storypage_by_pagenum($slug, $pagenum, $published) {
    $query = 'SELECT Content.id AS "id", Content.title AS "title", body, pagenum, author_comment, thumbnail, iscover, publish '.
                'FROM Story_Page JOIN Content ON Story_Page.id = Content.id JOIN Story ON Story_Page.story = Story.storyid '.
                'WHERE slug = ? AND pagenum = ?';
    if ($published) {
        $query .= ' AND publish <= NOW() AND publish != 0';
    }
    $params = array(array($slug, $pagenum));
    $db = new DB();
    $result = $db->run($query, $params);
    $db->close();
    return $result;
}

/**
 * get the page numbers of the pages that come before and after this one
 * @param $slug slug of the story the story page belongs to
 * @param $pagenum the page number of the story page
 * @return array of the page numbers of the previous, next, first and last story pages (based on the settings)
 **/
public static function storypage_links($slug, $pagenum) {
    $query_prev = 'SELECT MAX(pagenum) AS "prev" FROM Story_Page JOIN Story ON storyid=story JOIN Content ON Story_Page.id = Content.id WHERE pagenum < ? AND slug = ? AND show_prevnext = true AND publish <= NOW() AND publish != 0';
    $query_next = 'SELECT MIN(pagenum) AS "next" FROM Story_Page JOIN Story ON storyid=story JOIN Content ON Story_Page.id = Content.id WHERE pagenum > ? AND slug = ?  AND show_prevnext = true AND publish <= NOW() AND publish != 0';
    $query_first = 'SELECT MIN(pagenum) AS "first" FROM Story_Page JOIN Story ON storyid=story JOIN Content ON Story_Page.id = Content.id WHERE slug = ?  AND show_firstlast = true AND publish <= NOW() AND publish != 0';
    $query_last = 'SELECT MAX(pagenum) AS "last" FROM Story_Page JOIN Story ON storyid=story JOIN Content ON Story_Page.id = Content.id WHERE slug = ?  AND show_firstlast = true AND publish <= NOW() AND publish != 0';
    $params1 = array(array($slug));
    $params2 = array(array($pagenum, $slug));
    
    $db = new DB();
    $prev = $db->run($query_prev, $params2);
    $next = $db->run($query_next, $params2);
    $first = $db->run($query_first, $params1);
    $last = $db->run($query_last, $params1);
    $db->close();
    
    $result = array();
    $result['first'] = $first[0][0]['first'];
    $result['previous'] = $prev[0][0]['prev'];
    $result['next'] = $next[0][0]['next'];
    $result['last'] = $last[0][0]['last'];
    
    return $result;
}




/**
 * check whether a given basic page ID exists
 * @param $id id of the basic page
 * @return two dimensional array containing 1 if the basic page exists, 0 if it does not
 **/
public static function basicpage_exists($id) {
    $query = 'SELECT COUNT(id) AS "exists" FROM Basic_Page WHERE id = ?';
    $params = array(array($id));
    $db = new DB();
    $result = $db->run($query, $params);
    $db->close();
    return $result;
}

/**
 * check whether a given basic page slug exists
 * @param $slug slug of the basic page
 * @return two dimensional array containing 1 if the basic page exists, 0 if it does not
 **/
public static function basicpage_exists_slug($slug) {
    $query = 'SELECT COUNT(id) AS "exists" FROM Basic_Page WHERE slug = ?';
    $params = array(array($slug));
    $db = new DB();
    $result = $db->run($query, $params);
    $db->close();
    return $result;
}



/**
 * get all data for a basic page, using its id
 * @param $id id of the story page
 * @param $published false to include all pages, true to include only published pages
 * @return two dimensional array, with story page data in the inner array
 **/
public static function basicpage($id, $published) {
    $query = 'SELECT Content.id AS "id", title, short_title, slug, body, UNIX_TIMESTAMP(publish) AS "publish" '.
                'FROM Content JOIN Basic_Page ON Basic_Page.id = Content.id WHERE Content.id = ?';
    if ($published) {
        $query .= ' AND publish <= NOW() AND publish != 0';
    }
    $params = array(array($id));
    $db = new DB();
    $db->run('SET time_zone = "+0:00"', array(array()));// set timzeone (for timestamp processing)
    $result = $db->run($query, $params);
    $db->close();
    return $result;
}

/**
 * get all data for a basic page, using its slug
 * @param $slug slug of the basic page
 * @param $published false to include all pages, true to include only published pages
 * @return two dimensional array, with story page data in the inner array
 **/
public static function basicpage_by_slug($slug, $published) {
    $query = 'SELECT Content.id AS "id", title, short_title, slug, body, UNIX_TIMESTAMP(publish) AS "publish" '.
                'FROM Content JOIN Basic_Page ON Basic_Page.id = Content.id WHERE slug = ?';
    if ($published) {
        $query .= ' AND publish <= NOW() AND publish != 0';
    }
    $params = array(array($slug));
    $db = new DB();
    $db->run('SET time_zone = "+0:00"', array(array()));// set timzeone (for timestamp processing)
    $result = $db->run($query, $params);
    $db->close();
    return $result;
}


/**
 * get the ids, titles, pagenums (and in a later version thumbnails and iscover) of all basic pages
 * @param $published false to include all pages, true to include only published pages
 * @return array of all the basic pages' titles and slugs
 **/
public static function basicpages($published) {
    $query = 'SELECT Content.id AS "id", Content.title AS "title", short_title, slug '.
                'FROM Basic_Page JOIN Content ON Basic_Page.id = Content.id';
    if ($published) {
        $query .= ' WHERE publish <= NOW() AND publish != 0';
    }
    $query .= ' ORDER BY publish DESC';
    $params = array(array());
    $db = new DB();
    $result = $db->run($query, $params);
    $db->close();
    return $result;
}



/**
 * get data for the most recent story page of each story that has updates displayed on the homepage
 * @return two dimensional array of the data for the most recent story pages
 **/
public static function update() {
    $query = 'SELECT Content.id , Content.title, body, pagenum, thumbnail, Story.show_updates, Story.title AS "storytitle", slug
FROM (
    (Content JOIN Story_Page ON Content.id = Story_Page.id )
    JOIN Story on Story_Page.story = Story.storyid)
    JOIN (SELECT story , MAX(pagenum) AS "maxv" FROM Story_Page
    JOIN Content ON Content.id = Story_Page.id WHERE publish <= NOW() AND Publish != 0 GROUP BY story)Subq
ON Story.storyid = Subq.story AND Story_Page.pagenum = Subq.maxv
WHERE show_updates = true';
    $params = array(array());
    $db = new DB();
    $result = $db->run($query, $params);
    $db->close();
    return $result;
}



}

?>